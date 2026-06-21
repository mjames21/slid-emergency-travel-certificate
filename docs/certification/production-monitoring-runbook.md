# Production Monitoring Runbook

Purpose: define alerts needed for live national visa and border operations.

Application alerts:
- HTTP 5xx rate above threshold.
- Queue failures.
- Mail delivery failures.
- Login failure spikes.
- Permit verification spikes or repeated invalid code checks.
- PDF generation failures.
- Passport/MRZ OCR processing failures.

Payment alerts:
- WanGov webhook unauthorized attempts.
- WanGov duplicate event spikes.
- Payment amount or currency mismatch events.
- Payment initiated but not completed within SLA.
- Paid invoice without application status progression.

Border operations alerts:
- Watchlist hit.
- Lost/stolen/revoked document alert.
- MRZ verification failure at entry.
- Supervisor override.
- Refusal or referral decision.

Infrastructure alerts:
- Disk usage.
- Database connection saturation.
- Backup failure.
- TLS certificate expiry.
- Uptime check failure.
- SMTP authentication or reputation failure.

Daily operations checks:
- Review failed jobs.
- Review application error log.
- Review payment mismatch queue.
- Review pending HQ approvals.
- Review permits expiring soon.
- Review audit activity anomalies.

External gate:
- Alerts must be routed to named SLID operations contacts before production use.
