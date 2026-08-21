# Sierra Leone Immigration Department Emergency Travel Certificate

This repository is the public Emergency Travel Certificate (ETC) platform for Sierra Leone Immigration Department.

It was extracted from the combined permit/ETC platform so ETC can run as a separate public online service while landing permit / visa-on-arrival operations remain a staff-only system.

## Scope

In scope:

- Public no-account ETC application.
- Passport biodata upload and MRZ/OCR assistance.
- Manual correction when passport image quality is poor.
- Applicant, guardian, contact, destination, history, security declaration, and certification fields.
- WanGov/GovPay-only ETC payment.
- Public status tracking by secure token.
- HQ review of paid ETC applications.
- Email notification and certificate verification.
- Security headers, rate limits, private uploads, audit records, and staff MFA for HQ access.

Out of scope:

- Airport landing permit / visa-on-arrival officer intake.
- NRA receipt upload for permit payment.
- Permit extension, border movement, and airport permit reports.
- Native desktop/PWA shell for officer permit operations.

Those remain in `slid-visa-on-arrival`.

## Important URLs

| Area | URL |
| --- | --- |
| Public ETC application | `/emergency-travel-certificate/apply` |
| Public ETC status | `/emergency-travel-certificate/status/{token}` |
| Legacy public redirect | `/evisa/apply` |
| HQ ETC review | `/hq/emergency-travel-certificates` |
| Staff sign in | `/login` |
| Certificate verification | `/verify/{code}` |

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
- `WANGOV_ENABLED=true`
- `WANGOV_SERVICE_KEY`, `WANGOV_BEARER_TOKEN`, and `WANGOV_WEBHOOK_SECRET`.
- `SECURITY_STAFF_MFA_REQUIRED=true`
- `SEED_SYSTEM_ADMIN_PASSWORD`, `SEED_ETC_ISSUER_PASSWORD`, and `SEED_EXECUTIVE_PASSWORD` for first-time staff account bootstrap.
- Secure encrypted session cookies with `SESSION_DOMAIN=null` for a host-only login cookie.
- SMTP credentials for the approved SLID sender.

Do not copy secrets from the permit operations project.

## Security Position

ETC is public-facing, so it must remain internet accessible. The public surface is limited to application, status, payment, and verification routes. HQ review remains staff-only and requires authenticated, active staff with confirmed MFA.

Before production:

```bash
php artisan test
APP_DEBUG=false SESSION_SECURE_COOKIE=true SESSION_ENCRYPT=true SESSION_SAME_SITE=strict SECURITY_STAFF_MFA_REQUIRED=true SECURITY_STAFF_MFA_REQUIRE_CONFIRMED=true composer security:owasp
npm run build
```

## Split Notes

This project still carries some shared Laravel models and document services from the original platform because ETC approval currently generates the certificate through the existing permit/document primitives. After UAT stabilizes, shared pieces can be copied into a smaller internal package or simplified inside this ETC project.
