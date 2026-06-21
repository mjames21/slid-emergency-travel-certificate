# Security And Penetration Test Readiness

Purpose: prepare the platform for an accredited third-party security assessment.

Pre-test controls:
- `APP_DEBUG=false` in production.
- Public registration disabled.
- Staff self-deletion disabled.
- Confirmed multi-factor authentication required for staff access in production.
- Role and staff-title access controls reviewed.
- WanGov webhook secret configured.
- WanGov webhook source IP allowlist configured when provider IPs are available.
- SMTP, database, app key, and payment secrets rotated before production.

Test scope:
- Authentication and session management.
- Staff role/title authorization bypass attempts.
- HQ-only pages and airport data isolation.
- Public Emergency Travel Certificate application, status, and payment routes.
- WanGov webhook replay, amount mismatch, source validation, and payload-size controls.
- Permit QR verification and enumeration resistance.
- PDF/document access control.
- File upload controls for passport biodata and MRZ images.
- Audit-log integrity and sensitive-data exposure.

Evidence to provide:
- Current route list.
- User role/title matrix.
- Test-suite output.
- Production `.env` checklist without secret values.
- Dependency lock files.
- Deployment topology and network allowlists.
- Staff MFA enrollment and reset procedure.

Exit criteria:
- Critical and high findings closed.
- Medium findings risk-accepted or remediated.
- Retest evidence filed.
- SLID security owner signs production launch approval.
