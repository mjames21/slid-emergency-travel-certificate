# OWASP Top 10 Hardening Checklist

Source: OWASP Top 10:2025, current released OWASP Top 10 as of 2026-06-21.

The OWASP Top 10 is an awareness and prioritization checklist, not a deployment certification. For Sierra Leone Immigration Department production use, this checklist must be paired with penetration testing, infrastructure review, backup/restore testing, monitoring, and legal/data-protection sign-off.

| OWASP 2025 item | Implemented application controls | Go-live evidence required |
| --- | --- | --- |
| A01 Broken Access Control | Staff routes require auth, approved staff email domain, active user, confirmed staff MFA, staff title, and airport-scoped access middleware. Payment-only users are restricted to payment screens. Receipt, invoice, permit, HQ, admin, and report routes are title-gated. | UAT role matrix signed off; negative access tests passing; production staff role review completed; `SECURITY_STAFF_MFA_REQUIRED=true`; `SECURITY_STAFF_EMAIL_DOMAINS` reviewed. |
| A02 Security Misconfiguration | Security headers, CSP, HSTS, private-cache controls, runtime disclosure header suppression, strict production cookie settings, disabled public registration, private local storage, feature flags for out-of-scope modules. | `APP_DEBUG=false`; `expose_php=Off`; HTTPS/TLS scan; no generic private storage route; config cache and queue workers deployed from reviewed env. |
| A03 Software Supply Chain Failures | Composer and npm lockfiles are used. `composer security:audit`, `composer security:test`, and `npm run audit:security` scripts exist for release checks. | Clean `composer audit --locked`; clean `npm audit --audit-level=high`; dependency update owner assigned. |
| A04 Cryptographic Failures | Laravel encryption/hashing, encrypted production sessions, secure/httpOnly cookies, HTTPS-only production cookies, HSTS support, tokenized public status links, private document storage. | Valid `APP_KEY`; TLS 1.2+ only; SMTP/TLS verified; secrets rotated out of repo and managed by deployment secret store. |
| A05 Injection | Eloquent parameter binding is used. Raw SQL search snippets use bound parameters. OCR/Tesseract now runs through array-based `proc_open` to bypass shell interpretation. Tesseract binary and language are validated. Public OCR uploads are restricted to raster passport image formats. | Static review of raw SQL and command execution; upload validation UAT; OCR binary path pinned in production. |
| A06 Insecure Design | Permit payment requires paid status or approved waiver before issuance. NRA receipt reuse is blocked. ETC payment is WanGov-only. Workflow transitions are role-gated. Traveler history and standards checks inform officer decisions. | Business process sign-off; abuse-case review for payment, issuance, revocation, extension, and reprint flows. |
| A07 Authentication Failures | Layered login throttles, rotating-username IP throttles, approved staff email domain enforcement on creation/profile/login/route access, mandatory confirmed staff MFA, Fortify two-factor setup, passkeys, two-factor/passkey throttles, recent password confirmation for sensitive admin routes, session-fingerprint hijack protection, shorter password reset and password confirmation windows. | Staff MFA enrollment completed before go-live; MFA reset/change procedure approved; brute-force monitoring alerts configured. |
| A08 Software or Data Integrity Failures | Webhooks require an explicit shared secret, optional IP allowlist, payload size limit, replay/idempotency handling, and stored payload SHA-256/source/request metadata. WanGov production endpoints must use HTTPS, and checkout redirects are restricted to configured hosts. PDF/QR verification uses server records. | WanGov production callback contract verified; `WANGOV_WEBHOOK_SECRET` set to a distinct high-entropy value; `WANGOV_CHECKOUT_ALLOWED_HOSTS` reviewed; reconciliation report compared to provider records. |
| A09 Security Logging and Alerting Failures | Audit log service exists; session integrity failures, webhook rejections, and public permit verification attempts are logged; HQ audit/reconciliation pages exist. | Central log shipping/SIEM configured; alert thresholds for brute force, webhook rejection, duplicate receipt, invalid permit verification bursts, and privilege changes. |
| A10 Mishandling of Exceptional Conditions | Production debug is disabled by env template. Sensitive pages emit no-store cache headers. Public token/code routes are rate-limited to reduce enumeration. | Staging smoke test with `APP_DEBUG=false`; custom error pages reviewed; incident response runbook approved. |

## Release Commands

Run before each production release:

```bash
composer security:test
composer security:owasp
composer security:audit
npm run build
```

## Zero Trust Access Posture

NIST SP 800-207 treats every staff request as untrusted until policy checks pass. For this application, the production staff boundary is enforced in the application layer:

- Public ETC application, payment callbacks, and permit verification remain internet-facing with throttles and token/code controls.
- Staff, HQ, admin, payment, permit, invoice, receipt, and document routes require authenticated users from approved staff email domains, active accounts, staff membership, confirmed MFA, title-based authorization, and airport/document scoping.
- Session integrity checks bind staff sessions to browser and network-prefix signals to reduce hijack risk.
- Production release checks fail when staff MFA is not enabled and confirmed, payment endpoints are not HTTPS, webhook secrets are weak or reused, or checkout redirect hosts are not constrained.

## Open Production Gates

- External penetration test and remediation retest.
- Server/WAF/firewall review, including `expose_php=Off` and hidden web server version banners.
- TLS and HSTS preload readiness review.
- Centralized logging and alert routing.
- Disaster recovery drill.
- SLID policy and data-protection sign-off.
