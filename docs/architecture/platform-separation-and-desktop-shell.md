# Platform Separation And Staff Desktop Shell

## Decision

Split the current combined platform into two operational products:

1. **SLID Permit Operations**
   - Officer-led landing permit / visa-on-arrival intake.
   - NRA receipt evidence or optional future WanGov/GovPay payment.
   - Permit issuance, receipt, invoice, verification, staff dashboard, audit, and reports.
   - Staff-only access with MFA, role controls, airport scoping, session integrity, and device controls.

2. **SLID Emergency Travel Certificate**
   - Public online ETC application.
   - Applicant passport upload, MRZ/manual correction, questionnaire, WanGov/GovPay payment, public status link, HQ review, and applicant email notification.
   - Separate public deployment, separate branding, separate security profile, and separate UAT pack.

The current repository should remain the permit operations system. ETC should be extracted into a new project after the shared database boundaries are confirmed.

## Why Split

- ETC is a public citizen-facing service.
- Permit operations is an internal officer workflow.
- The two systems have different payment rules, threat models, user types, uptime expectations, and deployment controls.
- Splitting reduces blast radius: a public ETC issue should not expose officer permit operations.
- Documentation, UAT, and management sign-off become clearer.

## PWA Or Desktop Shell Position

A Progressive Web App should be the first option for the staff permit system unless SLID needs a native desktop capability that the browser cannot provide.

Recommended PWA use:

- Officers install the permit operations portal from Chrome or Edge as a standalone app.
- The app launches without normal browser tabs and feels like a desktop application.
- Updates are deployed centrally from the server.
- No separate installer or app-store distribution is required for most desktops.
- Camera capture, file upload, secure sessions, MFA, and role controls continue to work through the browser security model.

PWA limitations:

- It is still an online web application.
- It cannot hide the server origin from a determined user.
- It cannot guarantee managed-device-only access by itself.
- Deep native controls, custom scanner drivers, certificate stores, kiosk lockdown, and signed auto-update are stronger in a native shell or managed-device stack.

Recommended path:

1. Build permit operations as an installable PWA first.
2. Add managed-device and Zero Trust access controls at the cloud/IAM layer.
3. Move to Electron only if SLID needs native scanner integration, stronger kiosk control, or signed desktop distribution.

## Electron Shell Position

An Electron desktop shell can be used for the staff permit system, but it must not be treated as security by itself.

Recommended use:

- Install a signed desktop application on officer laptops.
- The shell opens only the official permit operations portal.
- The shell pins the production host allowlist.
- The shell disables arbitrary navigation, popups, devtools in production, and unsafe Electron APIs.
- The shell supports camera/scanner workflows where useful.
- The portal still requires staff login, confirmed MFA, role authorization, and server-side checks.

Do not rely on a long UUID domain as the security control. A hidden URL is only obscurity. It can reduce casual discovery, but access must still be enforced by identity, MFA, device trust, and authorization.

## Recommended Staff Access Model

The permit operations portal can remain internet-reachable only if it is protected as a staff system:

- Staff MFA is mandatory before access.
- Admin pages require recent password confirmation.
- Staff sessions use anti-hijack session integrity checks.
- Users are scoped by staff title and airport.
- Devices are registered and can be revoked.
- Production should add a cloud access broker or Zero Trust access layer, device posture checks, WAF, centralized logging, and alerting.
- Optional stronger controls: client certificates, managed-device-only access, SSO, conditional access, and IP allowlists for airport networks where practical.

## Electron Security Baseline

When the desktop shell is built, use these defaults:

- `nodeIntegration: false`
- `contextIsolation: true`
- `sandbox: true`
- `webSecurity: true`
- `allowRunningInsecureContent: false`
- Block navigation to any URL outside the permit operations allowlist.
- Block `window.open` except explicitly approved destinations.
- Disable devtools in production builds.
- Use auto-update only from a signed/trusted update channel.
- Sign and notarize application builds where the operating system supports it.
- Store no production secrets in the desktop app package.

## Proposed Extraction Boundary

Move to the ETC project:

- `EvisaApplicationController`
- `App\Services\Evisa\*`
- `resources/views/evisa/*`
- Public ETC routes under `/emergency-travel-certificate/*`
- ETC HQ review screens and tests
- ETC-specific migrations and questionnaire fields after schema review
- WanGov/GovPay ETC payment flow
- ETC UAT and public applicant documentation

Keep in permit operations:

- Staff permit application intake
- NRA receipt payment capture
- Optional future WanGov/GovPay permit payment path
- Permit, receipt, invoice, verification, and PDF generation
- Staff/HQ/admin/audit/reporting modules for permits
- Passenger, MRZ, country, nationality, sex, purpose, airport, desk, staff title, and audit primitives needed by permit operations

Shared logic should be copied or moved into a small internal package only after the split stabilizes. Do not couple the two production deployments through a shared runtime unless SLID accepts the operational dependency.

## Migration Sequence

1. Freeze current permit and ETC behavior with tests.
2. Create a new ETC repository from the current Laravel baseline.
3. Move ETC routes, controllers, views, services, tests, and docs into the ETC repository.
4. Remove public ETC routes and menu items from the permit repository.
5. Rename remaining permit repository copy and docs to permit operations only.
6. Give each project separate `.env.production.example`, database name, app key, mail name, queue, logs, and deployment pipeline.
7. Run UAT separately:
   - Permit operations UAT for officers, payment officers, supervisors, HQ, and admins.
   - ETC public/HQ UAT for applicants, WanGov/GovPay, and HQ reviewers.
8. Build the Electron staff shell only for the permit operations portal.
9. Deploy ETC as public web.
10. Deploy permit operations behind staff-grade access controls.

## Immediate Code Actions

- Keep the current ETC feature flag enabled until the new ETC project exists.
- After the ETC project is created, set `FEATURE_EMERGENCY_TRAVEL_CERTIFICATE_ENABLED=false` in the permit operations production environment.
- Keep `SECURITY_STAFF_MFA_REQUIRED=true` for permit operations production.
- Update README and UAT documents after the extraction so they no longer describe a combined product.
