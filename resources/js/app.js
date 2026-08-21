const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const showStatus = (element, message, tone = 'info') => {
    if (!element) {
        return;
    }

    element.textContent = message;
    element.classList.remove('hidden', 'text-gray-600', 'text-red-700', 'text-emerald-700');

    const toneClass = {
        error: 'text-red-700',
        success: 'text-emerald-700',
        info: 'text-gray-600',
    }[tone] ?? 'text-gray-600';

    element.classList.add(toneClass);
};

const base64UrlToBuffer = (value) => {
    const padded = `${value}${'='.repeat((4 - (value.length % 4)) % 4)}`;
    const base64 = padded.replace(/-/g, '+').replace(/_/g, '/');
    const binary = window.atob(base64);
    const bytes = new Uint8Array(binary.length);

    for (let index = 0; index < binary.length; index += 1) {
        bytes[index] = binary.charCodeAt(index);
    }

    return bytes.buffer;
};

const bufferToBase64Url = (buffer) => {
    const bytes = new Uint8Array(buffer);
    let binary = '';

    bytes.forEach((byte) => {
        binary += String.fromCharCode(byte);
    });

    return window.btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
};

const requestJson = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers ?? {}),
        },
        ...options,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const validationMessage = payload.errors
            ? Object.values(payload.errors).flat().join(' ')
            : null;

        throw new Error(validationMessage || payload.message || 'The passkey request could not be completed.');
    }

    return payload;
};

const prepareCreationOptions = (options) => ({
    ...options,
    challenge: base64UrlToBuffer(options.challenge),
    user: {
        ...options.user,
        id: base64UrlToBuffer(options.user.id),
    },
    excludeCredentials: (options.excludeCredentials ?? []).map((credential) => ({
        ...credential,
        id: base64UrlToBuffer(credential.id),
    })),
});

const prepareRequestOptions = (options) => ({
    ...options,
    challenge: base64UrlToBuffer(options.challenge),
    allowCredentials: (options.allowCredentials ?? []).map((credential) => ({
        ...credential,
        id: base64UrlToBuffer(credential.id),
    })),
});

const serializeAttestation = (credential) => ({
    id: credential.id,
    rawId: bufferToBase64Url(credential.rawId),
    type: credential.type,
    authenticatorAttachment: credential.authenticatorAttachment,
    response: {
        attestationObject: bufferToBase64Url(credential.response.attestationObject),
        clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
        transports: typeof credential.response.getTransports === 'function'
            ? credential.response.getTransports()
            : [],
    },
    clientExtensionResults: credential.getClientExtensionResults(),
});

const serializeAssertion = (credential) => ({
    id: credential.id,
    rawId: bufferToBase64Url(credential.rawId),
    type: credential.type,
    authenticatorAttachment: credential.authenticatorAttachment,
    response: {
        authenticatorData: bufferToBase64Url(credential.response.authenticatorData),
        clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
        signature: bufferToBase64Url(credential.response.signature),
        userHandle: credential.response.userHandle
            ? bufferToBase64Url(credential.response.userHandle)
            : null,
    },
    clientExtensionResults: credential.getClientExtensionResults(),
});

const passkeysAvailable = () => window.PublicKeyCredential && navigator.credentials;

const setupPasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const showIcon = button.querySelector('[data-password-icon="show"]');
        const hideIcon = button.querySelector('[data-password-icon="hide"]');

        if (!input || button.dataset.passwordToggleReady === 'true') {
            return;
        }

        button.dataset.passwordToggleReady = 'true';
        button.addEventListener('click', () => {
            const isVisible = input.type === 'text';
            const nextLabel = isVisible ? button.dataset.showLabel : button.dataset.hideLabel;

            input.type = isVisible ? 'password' : 'text';
            button.setAttribute('aria-label', nextLabel);
            button.setAttribute('aria-pressed', String(!isVisible));
            button.title = nextLabel;
            showIcon?.classList.toggle('hidden', !isVisible);
            hideIcon?.classList.toggle('hidden', isVisible);
            input.focus({ preventScroll: true });
        });
    });
};

const setupPasskeyRegistration = () => {
    document.querySelectorAll('[data-passkey-registration]').forEach((container) => {
        const button = container.querySelector('[data-passkey-register]');
        const nameInput = container.querySelector('[data-passkey-name]');
        const status = container.querySelector('[data-passkey-status]');

        if (!button || !nameInput) {
            return;
        }

        if (!passkeysAvailable()) {
            button.disabled = true;
            showStatus(status, 'This browser does not support passkeys.', 'error');
            return;
        }

        button.addEventListener('click', async () => {
            const name = nameInput.value.trim();

            if (!name) {
                showStatus(status, 'Enter a label for this passkey.', 'error');
                nameInput.focus();
                return;
            }

            button.disabled = true;
            showStatus(status, 'Waiting for your device passkey prompt...', 'info');

            try {
                const { options } = await requestJson(container.dataset.optionsUrl);
                const credential = await navigator.credentials.create({
                    publicKey: prepareCreationOptions(options),
                });

                await requestJson(container.dataset.storeUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        name,
                        credential: serializeAttestation(credential),
                    }),
                });

                showStatus(status, 'Passkey registered. Reloading...', 'success');
                window.setTimeout(() => window.location.reload(), 650);
            } catch (error) {
                showStatus(status, error.message, 'error');
            } finally {
                button.disabled = false;
            }
        });
    });
};

const setupPasskeyLogin = () => {
    document.querySelectorAll('[data-passkey-login]').forEach((container) => {
        const button = container.querySelector('[data-passkey-login-button]');
        const status = container.querySelector('[data-passkey-login-status]');

        if (!button) {
            return;
        }

        if (!passkeysAvailable()) {
            button.disabled = true;
            showStatus(status, 'This browser does not support passkeys.', 'error');
            return;
        }

        button.addEventListener('click', async () => {
            button.disabled = true;
            showStatus(status, 'Waiting for your passkey...', 'info');

            try {
                const { options } = await requestJson(container.dataset.optionsUrl);
                const credential = await navigator.credentials.get({
                    publicKey: prepareRequestOptions(options),
                });
                const remember = document.querySelector(container.dataset.rememberSelector)?.checked ?? false;
                const response = await requestJson(container.dataset.loginUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        credential: serializeAssertion(credential),
                        remember,
                    }),
                });

                window.location.assign(response.redirect ?? '/dashboard');
            } catch (error) {
                showStatus(status, error.message, 'error');
            } finally {
                button.disabled = false;
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    setupPasswordToggles();
    setupPasskeyRegistration();
    setupPasskeyLogin();

    document.querySelectorAll('[data-passkey-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm('Remove this passkey from the account?')) {
                event.preventDefault();
            }
        });
    });
});
