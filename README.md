# Sierra Leone Immigration Department Emergency Travel Certificate

This repository is the staff-operated Emergency Travel Certificate (ETC) platform for Sierra Leone Immigration Department.

ETC applications are entered in the office by an authorized issuer. Applicants do not apply publicly. The platform runs separately from landing permit / visa-on-arrival operations.

## Scope

In scope:

- Staff-only ETC intake for office applicants.
- Passport biodata upload and MRZ/OCR assistance.
- Manual correction when passport image quality is poor.
- Applicant, guardian, contact, destination, history, security declaration, and certification fields.
- Receipt-number payment recording, with optional WanGov/GovPay integration.
- Staff-only application status tracking.
- Approval and issuance of paid ETC applications by the ETC issuer.
- Digital certificates, MRZ, email notification, and QR links to public verification.
- System administrator provisioning of issuer and executive accounts.
- Installable PWA; no Tauri or kiosk mode.
- Security headers, rate limits, private uploads, audit records, and staff MFA for HQ access.

Out of scope:

- Airport landing permit / visa-on-arrival officer intake.
- Permit extension, border movement, and airport permit reports.
- Public applicant intake and native desktop or kiosk shells.

Those remain in `slid-visa-on-arrival`.

## Important URLs

| Area | URL |
| --- | --- |
| Office ETC application | `/emergency-travel-certificate/apply` |
| Staff ETC status | `/emergency-travel-certificate/status/{token}` |
| HQ ETC review | `/hq/emergency-travel-certificates` |
| Staff user management | `/admin/staff/users` |
| Staff sign in | `/login` |
| Certificate verification | `/verify/{code}` |
| Digital certificate | `/digital/etc/{code}` |

## Local Setup

Install dependencies:

```bash
composer install
npm install
```

Create and configure `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Run migrations and seed reference data:

```bash
php artisan migrate --force
php artisan db:seed
```

The staff account seeder creates or repairs the System Administrator, ETC Issuer, and Executive Observer accounts. In production, set `SEED_SYSTEM_ADMIN_PASSWORD`, `SEED_ETC_ISSUER_PASSWORD`, and `SEED_EXECUTIVE_PASSWORD` before the first seed. Remove or blank those password variables after bootstrap unless you intentionally want a later `db:seed --force` to rotate that account password.

Default seed emails are `admin@immigration.gov.sl`, `etc.issuer@immigration.gov.sl`, and `executive@immigration.gov.sl`. The local-development default password is `ChangeMe123!`; production must keep `SEED_ALLOW_DEFAULT_PASSWORDS=false` and use unique strong passwords. Existing passwords are preserved when their seed password variables are blank.

Build frontend assets:

```bash
npm run build
```

Start local development:

```bash
composer dev
```

## Production Notes

Use `.env.production.example` as the production checklist template.

Production must set:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://etc.slid.datahub.gov.sl` for the deployed ETC domain.
- `APP_KEY` to a generated production key.
- `WANGOV_ENABLED=false` for receipt-only operations. When enabling the integration, configure `WANGOV_SERVICE_KEY`, `WANGOV_BEARER_TOKEN`, `WANGOV_WEBHOOK_SECRET`, and the approved HTTPS provider URL and checkout hosts.
- `SECURITY_STAFF_MFA_REQUIRED=true`
- `SEED_SYSTEM_ADMIN_PASSWORD`, `SEED_ETC_ISSUER_PASSWORD`, and `SEED_EXECUTIVE_PASSWORD` for first-time staff account bootstrap.
- Secure encrypted session cookies with `SESSION_DOMAIN=null` for a host-only login cookie.
- `LOG_CHANNEL=stderr` so container logging does not depend on writable application files.
- SMTP credentials for the approved SLID sender.

Do not copy secrets from the permit operations project.

## Security Position

The public surface is limited to staff authentication, token-protected certificate verification/digital certificates, and authenticated provider webhooks. Intake, application status, receipt recording, and HQ review require authenticated, active staff with confirmed MFA and the appropriate role.

Before production:

```bash
php artisan test
APP_DEBUG=false SESSION_SECURE_COOKIE=true SESSION_ENCRYPT=true SESSION_SAME_SITE=strict SECURITY_STAFF_MFA_REQUIRED=true SECURITY_STAFF_MFA_REQUIRE_CONFIRMED=true composer security:owasp
npm run build
```

## Split Notes

This project still carries some shared Laravel models and document services from the original platform because ETC approval currently generates the certificate through the existing permit/document primitives. After UAT stabilizes, shared pieces can be copied into a smaller internal package or simplified inside this ETC project.
