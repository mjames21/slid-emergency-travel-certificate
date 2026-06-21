# Data Protection Review

Purpose: prepare legal and policy review for traveler, passport, visa, payment, and border-movement data.

Data categories:
- Traveler identity and passport details.
- Passport biodata and MRZ images.
- Visa application details.
- Payment invoices, receipts, and webhook payloads.
- Permit PDFs and verification codes.
- Border movement and admissibility decisions.
- Watchlist and document-alert records.
- Staff account and audit-log activity.

Controls already represented in the platform:
- Authenticated staff access.
- Staff-title authorization.
- Airport-level access checks.
- Compliance auditor role.
- Audit logs.
- Public staff registration disabled.
- Staff self-deletion disabled for audit retention.
- Payment webhook idempotency and verification controls.

Policy decisions required:
- Retention period for passport images.
- Retention period for audit logs.
- Retention period for payment records.
- Legal basis for watchlist and document-alert processing.
- Cross-agency data sharing rules.
- Applicant privacy notice and consent language.
- Data-subject access process where applicable.
- Breach notification process.

Launch gate:
- SLID legal and data-protection owner must approve retention, sharing, and notification rules before production.
