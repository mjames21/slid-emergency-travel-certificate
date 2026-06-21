# Disaster Recovery Runbook

Purpose: define restore steps and operating targets for the SLID visa and border-management platform.

Recovery targets:
- RPO: define by SLID policy before launch.
- RTO: define by SLID policy before launch.
- Minimum recommendation: daily encrypted database backups, with more frequent backups during border operating hours.

Backup coverage:
- PostgreSQL database.
- Uploaded passport biodata and MRZ images.
- Generated permit, receipt, and invoice PDFs.
- Application `.env` secrets stored in a secure password vault.
- Deployment release artifact and dependency lock files.

Restore drill:
1. Provision clean non-production host.
2. Restore latest database backup.
3. Restore private and public storage files.
4. Configure `.env` from vault.
5. Run migrations in safe mode.
6. Run `php artisan route:list`.
7. Run smoke tests for login, eVisa status, permit verification, HQ dashboard, and staff application search.
8. Validate one known permit PDF and QR verification.
9. Record restore duration and data timestamp.

Incident response:
- Declare incident owner.
- Freeze deployments.
- Preserve logs.
- Restore from known-good backup if data corruption is confirmed.
- Notify SLID leadership and legal/data-protection owner according to policy.

External gate:
- Production launch should not proceed until a restore drill has succeeded and been documented.
