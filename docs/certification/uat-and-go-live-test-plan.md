# Sierra Leone Immigration Department Permit And ETC UAT And Go-Live Test Plan

Version: 1.0
Date: 2026-06-19
System: SLID permit and Emergency Travel Certificate platform
Owner: Sierra Leone Immigration Department

## 1. Purpose

This plan defines the user acceptance testing, technical regression testing, security checks, and go-live gates required before production use by Sierra Leone Immigration Department.

The goal is to prove that the platform is ready for real operational use, not only a demonstration. UAT must confirm that each user type can complete the correct work, cannot access work outside their authority, and that payment, approval, document issuance, verification, audit, and reporting flows are reliable.

## 2. Current Production Scope

In scope for this go-live:

- Staff permit application processing.
- Permit payment through either NRA receipt upload/receipt number confirmation or WanGov/GovPay digital payment.
- Permit issuance, PDF document generation, QR verification, expiry reporting, and permit extension workflows.
- Emergency Travel Certificate public application, passport upload/MRZ read, WanGov/GovPay-only payment status, HQ review, and applicant status link.
- Staff administration, airport/desks/devices, workflow transition control, audit logs, reconciliation, and HQ reporting.
- Security hardening already included in the application, including staff access control, upload restrictions, private file storage, rate limiting, and security headers.

Feature-flagged or later-phase scope:

- Full border movement module.
- Live WanGov/GovPay production integration for permit payments if not enabled by credentials and provider sign-off. ETC production requires WanGov/GovPay because ETC does not use NRA receipt upload.
- External Interpol/watchlist, lost passport, Timatic/IATA, biometric, or ePassport chip integrations.

## 3. User Types

The application currently defines 8 configured staff user types.

| No. | User type | Code | Main purpose |
| --- | --- | --- | --- |
| 1 | System Administrator | `system_administrator` | Platform-wide technical administration, roles, devices, workflows, and emergency support. |
| 2 | HQ Administrator | `hq_administrator` | Headquarters oversight, approvals, revocation control, reporting, audit, and ETC review. |
| 3 | Airport Manager | `airport_manager` | Airport-level command oversight, approval, revocation, device control, and airport reports. |
| 4 | Shift Supervisor | `shift_supervisor` | Checker control, application review, approval, cancellation, waiver, and reprint authorization. |
| 5 | Visa Processing Officer | `visa_processing_officer` | Frontline permit application capture, billing handoff, permit preparation, and issuance. |
| 6 | Payment Officer | `payment_officer` | NRA/payment desk receipt review, camera/upload receipt capture, payment confirmation, and invoice handling only. |
| 7 | Compliance Auditor | `compliance_auditor` | Read-only audit and investigation access. |
| 8 | Executive Observer | `executive_observer` | Read-only executive oversight and reports. |

For UAT, use 9 active actor profiles because the Payment Officer represents the NRA/payment desk user type. Also test the WanGov/GovPay provider flow because ETC uses WanGov/GovPay only and permit payment may use it as an alternative.

| No. | UAT actor profile | Why it must be tested |
| --- | --- | --- |
| 1 | Public ETC applicant | Applies online, uploads passport, reviews extracted details, pays, checks status, receives decision. |
| 2 | System Administrator | Confirms technical administration, role control, workflow setup, and production restrictions. |
| 3 | HQ Administrator | Confirms HQ operational control, ETC review, reports, revocation, and audit oversight. |
| 4 | Airport Manager | Confirms airport-scoped management, approvals, revocation, device control, and reports. |
| 5 | Shift Supervisor | Confirms checker review, approval, waiver, cancellation, and reprint control. |
| 6 | Visa Processing Officer | Confirms frontline permit creation and issuance flow. |
| 7 | Payment Officer | NRA/payment desk user who sees only payment screens, can capture/upload receipt evidence, confirms receipt number, and marks invoices paid. |
| 8 | Compliance Auditor | Confirms read-only audit access and no mutation permissions. |
| 9 | Executive Observer | Confirms leadership visibility without operational modification rights. |
| Provider | WanGov/GovPay payment provider | Confirms ETC online payment and optional permit digital payment callback behavior. |

Seeded demo accounts must be used only in UAT and staging. Before production, demo passwords must be changed or demo accounts removed.

## 4. UAT Entry Criteria

UAT may start only when:

- Latest database migrations run successfully on the UAT environment.
- UAT environment is separate from development and production.
- `APP_DEBUG=false` is tested in a production-like environment.
- Public registration remains disabled.
- SMTP is configured in UAT or a controlled mail trap is available.
- NRA/manual receipt test mode is ready for permit payments, and WanGov/GovPay sandbox is ready for ETC payment testing.
- At least one airport, one desk, all staff title types, and test staff users are configured.
- Test passports and test passport images are approved for UAT use.
- Existing automated tests pass or known failures are documented.
- UAT participants understand that real citizen data must not be used unless approved by SLID policy.

## 5. Test Data

Prepare these UAT records:

- 3 adult passport cases with clean MRZ images.
- 2 blurry or incomplete passport images for fallback/manual entry testing.
- 1 duplicate passport case to confirm traveler history and duplicate handling.
- 1 expired passport case.
- 1 passport expiring before intended arrival or certificate validity.
- 1 applicant with missing email or invalid phone.
- 1 applicant with previous issued permit.
- 1 applicant with prior expired permit or overstay history, if available in test data.
- 1 payment success case.
- 1 payment pending case.
- 1 failed or mismatched payment case.
- 1 duplicate receipt number case.
- 1 waiver case, if waiver is enabled for UAT.
- 1 revoked permit case.
- 1 permit extension case.

## 6. End-To-End UAT Flows

### Flow A: Airport Permit Application

| Step | Actor | Expected result |
| --- | --- | --- |
| A1 | Visa Processing Officer | Creates a new permit application, uploads/captures passport details, and confirms traveler data. |
| A2 | Visa Processing Officer | Traveler history is visible but collapsed by default, and can be expanded when needed. |
| A3 | Visa Processing Officer | Application moves to billing or payment stage. |
| A4 | Traveler and authorized officer | Traveler pays at the NRA/customs payment desk and returns with the printed receipt; an authorized immigration or payment officer captures/uploads the receipt, enters the receipt number, and the system blocks reused receipt numbers. WanGov/GovPay remains the digital alternative when enabled. |
| A5 | Shift Supervisor or Airport Manager | Application is reviewed and approved, cancelled, or sent for correction. |
| A6 | Visa Processing Officer | Permit is prepared and issued only after the required status and payment checks pass. |
| A7 | Officer or verifier | Permit PDF opens, includes correct SLID logo, correct officer name, QR code, MRZ, passport data, and validity. |
| A8 | Public verifier or staff | QR verification page confirms document validity without exposing unnecessary personal data. |

### Flow B: Emergency Travel Certificate Online Application

| Step | Actor | Expected result |
| --- | --- | --- |
| B1 | Public ETC applicant | Opens the ETC application page and understands it is an official national application. |
| B2 | Public ETC applicant | Uploads a passport biodata image; MRZ details are read when image quality is sufficient. |
| B3 | Public ETC applicant | If MRZ read is incomplete, applicant can manually correct surname, given names, passport number, nationality, sex, and dates. |
| B4 | Public ETC applicant | Contact, travel, stay, history, security, certification, and payment sections are completed through save-and-continue steps. |
| B5 | Public ETC applicant | Application submits and status/tracking access is issued without requiring account creation. |
| B6 | Applicant and WanGov/GovPay | ETC fee is paid through WanGov/GovPay only. NRA receipt upload is not available for ETC. |
| B7 | HQ Administrator | HQ reviews ETC application after payment and makes a decision. |
| B8 | Public ETC applicant | Applicant receives an email or status update after approval or rejection. |
| B9 | Staff verifier | Issued certificate or permit can be verified on arrival. |

### Flow C: Permit Extension

| Step | Actor | Expected result |
| --- | --- | --- |
| C1 | Shift Supervisor or Airport Manager | Searches an active permit eligible for extension. |
| C2 | Shift Supervisor or Airport Manager | System rejects expired, revoked, replaced, or ineligible permits. |
| C3 | Shift Supervisor or Airport Manager | Extension request records reason, new date, and officer action. |
| C4 | Authorized approver | Extension approval creates a linked permit or updated permit record as designed. |
| C5 | Compliance Auditor | Audit trail shows original permit, extension request, approver, and timestamp. |

### Flow D: Revocation, Reprint, And Cancellation

| Step | Actor | Expected result |
| --- | --- | --- |
| D1 | Shift Supervisor | Reprint authorization requires a reason. |
| D2 | Airport Manager or HQ Administrator | Permit revocation requires a reason and is restricted to authorized titles. |
| D3 | Unauthorized officer | Revocation and reprint controls are hidden or blocked. |
| D4 | Verifier | Revoked permit fails verification or clearly shows not valid. |
| D5 | Compliance Auditor | Audit log records who acted, when, and why. |

## 7. Role-Based UAT Matrix

### Public ETC Applicant

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| PUB-01 | Open ETC application page | Page uses Sierra Leone Immigration Department wording and does not say eVisa where ETC is intended. |
| PUB-02 | Upload clear passport biodata image | MRZ fields populate correctly; applicant can review and edit. |
| PUB-03 | Upload blurry passport image | System gives useful fallback instructions and allows manual entry. |
| PUB-04 | Save and continue through all sections | Draft persists on the device/session and does not lose entered information. |
| PUB-05 | Submit incomplete required fields | Clear validation messages appear beside the missing fields. |
| PUB-06 | Submit and receive tracking/status link | Status page opens only with valid token and rate limits repeated attempts. |
| PUB-07 | Pay application fee or stage payment | Payment status changes correctly and duplicate payment is not created. |
| PUB-08 | Approved application email | Applicant receives the correct approval message and document link or instructions. |

### Visa Processing Officer

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| VPO-01 | Create permit application | Application is saved with passenger, passport, travel, and payment data. |
| VPO-02 | Create application for existing passport | Existing passenger is reused or handled gracefully; no database duplicate crash occurs. |
| VPO-03 | View traveler history | Passport details appear above traveler history; history is collapsed by default and expandable. |
| VPO-04 | Access another airport's application | Access is denied unless user has all-view authority. |
| VPO-05 | Prepare permit before approval | Action is blocked. |
| VPO-06 | Issue permit before payment | Action is blocked unless an approved waiver applies. |
| VPO-07 | Issue permit after payment and approval | Permit PDF and verification QR are generated. |
| VPO-08 | Upload NRA receipt brought back by traveler | Receipt evidence and printed receipt number can be captured from the invoice, payment becomes paid, and duplicate receipt reuse is blocked. |

### Payment Officer

This user type represents the NRA/payment desk actor at the airport for the current operational flow. In the preferred counter flow, the traveler pays NRA/customs first, then returns to the immigration desk with the receipt. The immigration officer can capture that receipt from the invoice screen, while the Payment Officer remains available for a dedicated payment queue where needed.

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| PAY-01 | View payment queue | Payment Officer sees only payment screens and airport-scoped payment records. |
| PAY-02 | Mark payment pending | Application/invoice enters payment pending status. |
| PAY-03 | Confirm valid NRA payment receipt | Payment status becomes paid and receipt record is created. |
| PAY-04 | Capture or upload receipt copy | Receipt image is captured by camera or uploaded, stored privately, and paired with the officer-entered NRA receipt number. |
| PAY-05 | Reuse same receipt number | System rejects duplicate receipt reuse. |
| PAY-06 | Amount mismatch | System blocks confirmation or raises reconciliation warning. |
| PAY-07 | Unauthorized issue permit action | Payment Officer cannot issue permit. |
| PAY-08 | Permit paid through WanGov/GovPay | Digital callback marks the invoice paid without requiring NRA receipt upload. |
| PAY-09 | Attempt application workflow access | Application list, application detail, permit, expiry report, and immigration workflow pages are blocked for payment-only users. |

### Shift Supervisor

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| SUP-01 | Send application under review | Status changes and audit log is recorded. |
| SUP-02 | Approve valid reviewed application | Status becomes approved. |
| SUP-03 | Cancel application | Reason is required and audit log is recorded. |
| SUP-04 | Approve waiver case | Checker requirement and reason are enforced. |
| SUP-05 | Authorize reprint | Reason is required and the reprint is traceable. |
| SUP-06 | Revoke permit | Action is blocked for this role. |

### Airport Manager

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| MGR-01 | View airport dashboard | Only airport-scoped operational data appears. |
| MGR-02 | Approve under-review application | Status becomes approved and audit log is recorded. |
| MGR-03 | Revoke issued permit | Reason is required and verification reflects invalid status. |
| MGR-04 | Manage airport devices | Device registration and status controls work. |
| MGR-05 | Export expiry report | Report exports only authorized airport data. |
| MGR-06 | Access HQ-only audit/report page | Access is denied unless separately assigned. |

### HQ Administrator

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| HQ-01 | View HQ dashboard | National operational counts and reports load. |
| HQ-02 | Review ETC applications | HQ can approve or reject according to configured workflow. |
| HQ-03 | Force paid application under review | Status changes only for authorized user and audit log is recorded. |
| HQ-04 | Revoke issued permit | Reason is required and verification reflects invalid status. |
| HQ-05 | View reconciliation report | Payments, receipts, and issued documents reconcile. |
| HQ-06 | Manage workflow transitions | Only HQ Administrator and System Administrator can access workflow controls. |

### System Administrator

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| SYS-01 | Create or invite staff user | User receives proper role/title and active status. |
| SYS-02 | Assign staff titles | Only intended title permissions are granted. |
| SYS-03 | Change workflow transition | Change is saved and visible in workflow behavior. |
| SYS-04 | Manage airports, desks, devices | Records can be created and updated safely. |
| SYS-05 | Disable staff account | Disabled user cannot sign in or access staff routes. |
| SYS-06 | Emergency operational override | Any bypass action is auditable and limited to intended transitions. |

### Compliance Auditor

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| AUD-01 | Open HQ audit logs | Auditor can read audit logs. |
| AUD-02 | Open transactions/reconciliation reports | Auditor can view required oversight data. |
| AUD-03 | Attempt create/update/delete action | Action is blocked. |
| AUD-04 | Attempt permit issuance or revocation | Action is blocked. |
| AUD-05 | Export reports, if permitted | Export contains only authorized data and is logged. |

### Executive Observer

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| EXE-01 | View executive dashboards | Dashboard opens in read-only mode. |
| EXE-02 | Attempt operational action | All mutation actions are blocked. |
| EXE-03 | View sensitive records | Only approved oversight data is visible. |
| EXE-04 | Export or print reports, if permitted | Exports are controlled and logged. |

### WanGov/GovPay Digital Payment Actor

Run these for ETC payment and for permit digital payment when enabled.

| Test ID | Scenario | Expected result |
| --- | --- | --- |
| EXT-01 | Valid WanGov/GovPay callback for ETC | Payment status updates once and only once. |
| EXT-02 | Replayed callback | Replay is ignored or safely idempotent. |
| EXT-03 | Bad secret or unauthorized source | Callback is rejected. |
| EXT-04 | Mismatched payment reference or amount | Payment is not confirmed without authorized correction. |
| EXT-05 | ETC without WanGov/GovPay confirmation | HQ approval is blocked until online payment is confirmed. |
| EXT-06 | Permit digital payment alternative | Permit invoice can be paid by WanGov/GovPay without using the NRA receipt upload path. |

## 8. Technical Regression Test Plan

Run these before UAT sign-off and again before production deployment.

| Area | Command or activity | Pass criteria |
| --- | --- | --- |
| PHP unit and feature tests | `php artisan test` | All tests pass, or documented skips are approved. |
| Frontend build | `npm run build` | Build succeeds with no production-blocking warnings. |
| PHP dependency audit | `composer audit` | No critical or high advisories. |
| Node dependency audit | `npm audit --omit=dev` | No critical or high advisories. |
| Route review | `php artisan route:list` | Routes match intended public/staff/HQ/admin exposure. |
| Migrations | `php artisan migrate --force` on staging clone | No failed migrations. |
| Config cache | `php artisan config:cache` | No runtime config errors. |
| Queue worker | Process test jobs | Mail, PDF, and payment jobs process. |
| Storage links/private storage | Attempt direct and authorized file access | Private uploads are not publicly accessible. |
| PDF generation | Generate permit, receipt, invoice, ETC document | Correct logo, officer name, QR code, data, and layout. |
| Email delivery | Send UAT decision email | Applicant receives email with correct wording and link. |

## 9. Security And OWASP Checks

Run these checks before production approval:

- Authentication requires verified, active staff accounts.
- Confirmed MFA is required before staff, HQ, admin, payment, and document access in production.
- Public staff registration is disabled.
- Staff routes require `auth`, `verified`, `active`, `staff.access`, and `staff.mfa`.
- Sensitive admin pages require recent password confirmation.
- Role/title access control blocks unauthorized URLs directly, not only through hidden buttons.
- Airport-scoped users cannot access another airport's applications, invoices, receipts, permits, or reports.
- Public ETC status tokens cannot be guessed through enumeration.
- Permit QR verification is rate-limited and does not leak unnecessary personal data.
- File uploads accept only approved image types and reject executable or oversized files.
- Uploaded passport and receipt files are stored privately.
- CSRF protection is active except for explicitly allowed signed or webhook routes.
- WanGov/GovPay webhooks require shared secret and source validation where provider IPs are available.
- Payment callbacks are idempotent and reject amount/reference mismatches.
- Error pages in production do not expose stack traces or secrets.
- Security headers are present: HSTS where HTTPS is enabled, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, and Permissions-Policy.
- Session cookies are secure, HTTP-only, and same-site appropriate for production.
- Rate limits exist on login, ETC submit, ETC status, MRZ read, permit verification, and webhooks.
- Audit logs record login-sensitive actions, workflow transitions, payment changes, revocations, reprints, and staff changes.

## 10. Performance And Reliability Tests

Minimum pre-go-live checks:

- 50 concurrent staff users can browse dashboard, application list, and reports without unacceptable errors.
- 100 public ETC applicants can load and submit the form over a controlled test window.
- Permit PDF generation completes within an agreed operational time.
- Search pages remain usable with realistic data volume.
- CSV and XLS exports complete for expected reporting size.
- Database backup and restore are tested successfully.
- Queue worker, scheduler, and mail delivery recover after restart.
- System monitoring alerts on 500 errors, queue failures, storage errors, mail failures, payment callback failures, and disk usage.

## 11. Data Protection And Legal Checks

Before go-live, SLID must approve:

- Data fields collected for permit and ETC applications.
- Retention period for passport images, receipt images, applications, logs, and generated PDFs.
- Who may view passport images, payment details, and audit logs.
- Production privacy notice and public applicant declaration text.
- Legal authority for digital permit/ETC issuance and verification.
- Procedure for correcting applicant data after submission.
- Procedure for revocation, appeal, refund, or cancellation where applicable.

## 12. Defect Severity

| Severity | Definition | Go-live impact |
| --- | --- | --- |
| P0 Critical | Data breach, payment corruption, unauthorized permit issuance, production crash, or security bypass. | Go-live blocked. |
| P1 High | Core workflow blocked for a required user type or incorrect official document generated. | Go-live blocked unless fixed. |
| P2 Medium | Workaround exists but operational efficiency or reporting is affected. | Requires SLID owner acceptance. |
| P3 Low | Cosmetic or wording issue that does not affect operational correctness. | Can be deferred with approval. |

## 13. UAT Exit Criteria

UAT passes only when:

- All P0 and P1 defects are closed and retested.
- All P2 defects are either fixed or formally accepted by the SLID business owner.
- Role-based access tests pass for all 8 staff user types.
- Public ETC application flow passes from application to payment/status to HQ decision.
- Airport permit flow passes from creation to payment to approval to issuance to QR verification.
- Payment and receipt reconciliation passes, including duplicate receipt prevention.
- PDF documents are approved by SLID business owner.
- Email templates and sender identity are approved.
- Audit logs prove who performed every sensitive action.
- Backup and restore test is complete.
- Security readiness checklist is signed off.
- Production secrets are rotated and no demo credentials remain.

## 14. Go-Live Checklist

| Gate | Owner | Evidence required | Status |
| --- | --- | --- | --- |
| Functional UAT sign-off | SLID Operations | Signed UAT results and defect closure list | Pending |
| HQ approval workflow sign-off | SLID HQ | ETC and permit approval test evidence | Pending |
| Payment sign-off | SLID Finance/Customs/NRA owner | Payment or receipt reconciliation evidence | Pending |
| Security sign-off | SLID IT/Security | OWASP checklist, dependency audits, penetration test plan or report | Pending |
| Data protection sign-off | SLID Legal/Data owner | Data protection review and retention approval | Pending |
| Document sign-off | SLID Document owner | Approved permit/ETC/receipt PDF samples | Pending |
| Infrastructure sign-off | DevOps/Hosting owner | Monitoring, backups, restore test, SSL, domain, mail, queues | Pending |
| Production deployment approval | Project sponsor | Final go/no-go record | Pending |

## 15. Recommended UAT Schedule

| Day | Activity |
| --- | --- |
| Day 1 | Environment smoke test, user setup, route/access check, test data preparation. |
| Day 2 | Permit application, payment, approval, issuance, document, and verification UAT. |
| Day 3 | ETC public application, MRZ fallback, payment/status, HQ review, and email UAT. |
| Day 4 | Reports, reconciliation, audit, revocation, extension, duplicate receipt, and negative tests. |
| Day 5 | Defect retest, security checklist, backup/restore evidence, and sign-off meeting. |

## 16. Go/No-Go Decision

Go-live should proceed only if:

- UAT exit criteria are met.
- Security and data protection sign-offs are complete.
- Production `.env` values are verified without exposing secrets.
- `APP_ENV=production` and `APP_DEBUG=false` are confirmed.
- HTTPS is active.
- SMTP sender is verified.
- Permit payment operating procedure is approved for both NRA receipt upload and optional WanGov/GovPay.
- ETC WanGov/GovPay-only payment procedure is approved.
- Support contacts and incident escalation are documented.
- Rollback plan and database restore plan are approved.

If any P0 or P1 issue remains open, the decision must be no-go.
