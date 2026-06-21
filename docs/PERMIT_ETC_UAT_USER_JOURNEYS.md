# SLID Permit And Emergency Travel Certificate UAT User Journey Pack

Purpose: validate the platform end to end by role, not only by page. Each tester should record actual result, pass/fail, defects, screenshots, and notes.

System: Sierra Leone Immigration Department Permit And Emergency Travel Certificate platform

## Test Setup

Base URL:

- Local desktop: `http://127.0.0.1:8000`
- LAN UAT: `http://<uat-server-ip-or-domain>`
- Public ETC application: `/emergency-travel-certificate/apply`
- Staff sign in: `/login`
- Staff dashboard: `/dashboard`
- Permit verification: `/verify/{code}`

Seed/reference setup:

- Run migrations on the UAT database.
- Seed airports, desks, staff titles, points of entry, countries, nationalities, purpose of visit, and test users.
- Configure one Freetown International Airport desk and one Bo airport desk for cross-airport access testing.
- Configure SMTP or a controlled mail trap.
- Configure WanGov/GovPay sandbox for ETC testing.
- Prepare test passport biodata images and sample NRA/customs receipt images.

UAT password handling:

- Use temporary UAT-only passwords issued by the test lead.
- Seeded demo accounts must be removed or password-rotated before production.
- Do not record real production passwords in this document.

Suggested UAT users:

| Test user | Role/title | Airport/scope | Purpose |
| --- | --- | --- | --- |
| `admin@immigration.gov.sl` | System Administrator | National | Users, roles, workflow, reference setup, emergency support |
| `hq@immigration.gov.sl` | HQ Administrator | National | HQ dashboard, ETC review, national reports, revocation, audit |
| `manager.fna@immigration.gov.sl` | Airport Manager | FNA | Airport oversight, approval, device/report controls |
| `supervisor.fna@immigration.gov.sl` | Shift Supervisor | FNA | Review, approval, waiver, reprint, checker control |
| `officer1.fna@immigration.gov.sl` | Visa Processing Officer | FNA desk 1 | Frontline permit application and issuance flow |
| `officer2.fna@immigration.gov.sl` | Visa Processing Officer | FNA desk 2 | Desk separation and same-airport workflow |
| `payment.fna@immigration.gov.sl` | Payment Officer | FNA | Payment queue, NRA receipt capture, invoice payment confirmation |
| `audit@immigration.gov.sl` | Compliance Auditor | National read-only | Audit, reports, reconciliation, negative mutation tests |
| `officer.boa@immigration.gov.sl` | Visa Processing Officer | BOA | Cross-airport access and regional data-scope tests |
| Public applicant | No account | Public | ETC online application, MRZ fallback, payment/status |

## UAT Recording Template

| Test ID | Role | Journey | Tester | Date | Pass/Fail | Defect ID | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |

Defect severity:

- P0 Critical: data breach, unauthorized permit issuance, payment corruption, production crash, or security bypass.
- P1 High: required user cannot complete a core workflow, or an official document is incorrect.
- P2 Medium: workaround exists but operational efficiency, reporting, or clarity is affected.
- P3 Low: cosmetic, wording, spacing, or minor usability issue.

## Journey 1: System Administrator Prepares UAT

Role: System Administrator

Goal: confirm technical administration, staff-title assignment, reference data, airport/desk setup, and workflow readiness.

Steps:

1. Log in as System Administrator.
2. Open `Admin -> Staff Users`.
3. Confirm each UAT user is active, verified, and assigned to the correct airport and desk.
4. Open `Admin -> Assign Titles`.
5. Confirm each user has the correct staff title and only the intended title.
6. Open `Admin -> Airports`, `Desks`, and `Devices`.
7. Confirm FNA and BOA test data exist.
8. Open `Admin -> Workflow Transitions`.
9. Confirm permit workflow transitions are visible and editable only to authorized users.
10. Open `HQ -> Audit Logs`.

Expected result:

- UAT users exist and can sign in.
- Payment Officer has payment-only access.
- Airport-scoped users are assigned to the correct airport.
- Workflow and reference data are ready.
- Audit log records administrative changes.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Staff users | Users active and scoped correctly |  |  |  |
| Staff titles | Titles match matrix |  |  |  |
| Airports/desks | FNA and BOA test desks exist |  |  |  |
| Workflow | Transitions visible to authorized users |  |  |  |
| Audit | Admin actions logged |  |  |  |

## Journey 2: Visa Processing Officer Creates A Permit Application

Role: Visa Processing Officer

Goal: validate frontline permit application capture, passport review, traveler history, travel details, payment handoff, and save behavior.

Steps:

1. Log in as `officer1.fna@immigration.gov.sl`.
2. Open `Staff -> New Application`.
3. Upload or capture a clear passport biodata image.
4. Confirm passport details appear above traveler history.
5. Confirm traveler history is collapsed by default and can be expanded.
6. Review and correct surname, given names, passport number, nationality, sex, birth date, and expiry date.
7. Enter arrival, stay, point of entry, purpose of visit, and flight carrier.
8. Use the searchable nationality and carrier dropdowns.
9. Enter contact, occupation, country of birth, and country of residence.
10. Save the application.

Expected result:

- MRZ/OCR values populate when readable.
- Blurry image fallback allows manual correction without blocking the officer.
- `03` and `04` month values are accepted as valid integers.
- Existing passport records are reused or handled gracefully; no duplicate database crash occurs.
- Traveler history saves space but remains available.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Passport capture | Details populated or editable |  |  |  |
| Date parts | Year/month/day boxes save correctly |  |  |  |
| Nationality/carrier | Searchable dropdowns work |  |  |  |
| Duplicate passport | No crash; record handled gracefully |  |  |  |
| Save | Application appears in list |  |  |  |

## Journey 3: NRA Receipt Payment For Permit

Roles: Visa Processing Officer, Payment Officer, Shift Supervisor

Goal: validate the current operational payment flow where the traveler pays NRA/customs, returns with a receipt, and the receipt is captured in the system.

Steps:

1. Create or open an application awaiting payment.
2. Open the invoice/payment screen.
3. Upload or camera-capture the NRA/customs receipt copy.
4. Enter the printed receipt number exactly.
5. Confirm amount and currency.
6. Save payment.
7. Attempt to reuse the same receipt number on another invoice.
8. Log in as Payment Officer and open `Staff -> Payments`.
9. Confirm Payment Officer sees only payment screens and cannot issue permits.

Expected result:

- Receipt image is stored privately.
- Receipt number is required.
- Duplicate receipt reuse is blocked.
- Payment status becomes paid only after valid confirmation.
- Payment Officer is restricted to payment/invoice functions.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Capture receipt | Upload/camera evidence saved |  |  |  |
| Receipt number | Required and stored |  |  |  |
| Duplicate receipt | Reuse blocked |  |  |  |
| Payment Officer | Payment-only access enforced |  |  |  |
| Audit | Payment action logged |  |  |  |

## Journey 4: WanGov/GovPay Digital Payment Alternative

Roles: Applicant/payment provider, System Administrator, HQ Administrator

Goal: validate WanGov/GovPay remains available for digital payment, especially ETC, without deleting the future integration path.

Steps:

1. Submit an ETC application and stage WanGov/GovPay payment.
2. Trigger a valid sandbox callback or simulated callback.
3. Confirm payment status becomes paid once.
4. Replay the same callback.
5. Submit a callback with invalid secret.
6. Submit a callback with mismatched amount or reference.

Expected result:

- Valid callback is idempotent.
- Replayed callback does not double-pay.
- Bad secret or source is rejected.
- Mismatched amount/reference is rejected.
- ETC cannot proceed to HQ approval without WanGov/GovPay payment confirmation.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Valid callback | Payment becomes paid once |  |  |  |
| Replay | No duplicate payment |  |  |  |
| Bad secret | Rejected |  |  |  |
| Mismatch | Not confirmed |  |  |  |
| ETC gate | Approval blocked until paid |  |  |  |

## Journey 5: Supervisor Review, Approval, And Permit Issuance

Roles: Shift Supervisor, Airport Manager, Visa Processing Officer

Goal: validate the operational control chain from submitted/paid application to approved permit and official PDF.

Steps:

1. Log in as Shift Supervisor.
2. Open a paid permit application.
3. Review passport, traveler history, travel details, and payment evidence.
4. Send for correction, cancel, approve, or approve waiver where appropriate.
5. Log in as Visa Processing Officer.
6. Prepare and issue permit only after payment and approval are complete.
7. Open generated permit PDF.
8. Scan QR or open `/verify/{code}`.

Expected result:

- Approval requires an authorized staff title.
- Permit issuance is blocked before payment and approval.
- PDF uses the SLID logo only and correct officer details.
- Verification page validates active permit and rejects revoked/invalid code.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Review | Full record visible |  |  |  |
| Approve/cancel | Reason and audit enforced |  |  |  |
| Issue gate | Blocked until paid and approved |  |  |  |
| PDF | Logo, QR, MRZ, dates correct |  |  |  |
| Verify | Valid/invalid behavior correct |  |  |  |

## Journey 6: Permit Extension, Reprint, Revocation

Roles: Shift Supervisor, Airport Manager, HQ Administrator, Compliance Auditor

Goal: validate sensitive permit lifecycle controls.

Steps:

1. Search for an active issued permit.
2. Create an extension request with reason and new validity.
3. Attempt extension on expired, revoked, or ineligible permit.
4. Authorize a reprint with reason.
5. Revoke a permit as Airport Manager or HQ Administrator.
6. Verify revoked permit QR/code.
7. Log in as Compliance Auditor and inspect audit trail.

Expected result:

- Extension eligibility is enforced.
- Reprint requires reason and is logged.
- Revocation requires authorized title and reason.
- Revoked permit no longer verifies as valid.
- Auditor can view but not mutate.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Extension | Eligible permit processed |  |  |  |
| Ineligible | Blocked |  |  |  |
| Reprint | Reason required |  |  |  |
| Revocation | Authorized and auditable |  |  |  |
| Auditor | Read-only evidence visible |  |  |  |

## Journey 7: Public Emergency Travel Certificate Application

Role: Public applicant

Goal: validate a professional national online ETC application without requiring an account.

Steps:

1. Open `/emergency-travel-certificate/apply`.
2. Confirm page says Emergency Travel Certificate, not eVisa.
3. Upload a clear passport biodata image.
4. Review MRZ-extracted values.
5. Upload a blurry image or use manual MRZ/manual entry fallback.
6. Complete applicant identity, contact/background, travel, stay/host, history, security, certification, and payment sections.
7. Save and continue between sections.
8. Submit application.
9. Use returned tracking/status link.
10. Pay through WanGov/GovPay sandbox.

Expected result:

- Applicant does not need an account.
- MRZ read helps but does not trap the applicant.
- Nationality, country of birth, country of residence, and purpose lists are searchable where applicable.
- Required fields have clear labels and hints.
- Tracking token is issued securely.
- ETC payment is WanGov/GovPay only.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Public page | Correct ETC wording |  |  |  |
| Passport upload | MRZ read or fallback works |  |  |  |
| Sections | Save and continue works |  |  |  |
| Submission | Tracking/status issued |  |  |  |
| Payment | WanGov/GovPay path works |  |  |  |

## Journey 8: HQ ETC Review And Applicant Notification

Role: HQ Administrator

Goal: validate headquarters review and decision communication for ETC applications.

Steps:

1. Log in as HQ Administrator.
2. Open `HQ -> Emergency Travel Certificates`.
3. Filter by awaiting payment, paid, under review, approved, and rejected.
4. Open a paid application.
5. Review passport image, applicant details, travel reason, host/contact details, declarations, and payment.
6. Approve or reject with reason.
7. Confirm applicant email/status reflects the decision.

Expected result:

- HQ can review only after payment where required.
- Decision requires appropriate reason or note.
- Applicant receives correct decision email or status update.
- Audit logs record reviewer, decision, and timestamp.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| List/filter | Applications visible by status |  |  |  |
| Detail review | Required evidence visible |  |  |  |
| Approve/reject | Decision saved and logged |  |  |  |
| Notification | Applicant can see outcome |  |  |  |

## Journey 9: Access Control And Negative Tests

Roles: All staff titles

Goal: prove that restricted data and actions are blocked by server-side authorization.

Steps:

1. Log in as Payment Officer and try `/staff/applications`.
2. Log in as Visa Processing Officer and try `/staff/payments` if not authorized.
3. Log in as BOA officer and try to open an FNA-only application/invoice/permit URL.
4. Log in as Compliance Auditor and attempt create/update/delete actions.
5. Log in as Executive Observer and attempt operational mutation.
6. Open `/verify/not-real-code` repeatedly and confirm rate limiting/404 behavior.
7. Open an ETC status URL with an invalid token.

Expected result:

- Unauthorized URLs are denied directly.
- Hidden buttons are not the only protection.
- Airport access is scoped.
- Public token/code guessing is rate-limited and does not leak personal data.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Payment-only access | Application routes blocked |  |  |  |
| Airport scope | Cross-airport access denied |  |  |  |
| Read-only roles | Mutation blocked |  |  |  |
| Public guessing | Rate limited/no leakage |  |  |  |

## Journey 10: Reporting, Reconciliation, And Operations

Roles: HQ Administrator, Airport Manager, Compliance Auditor, Executive Observer

Goal: validate operational reporting, expiry reports, payment reconciliation, and leadership read-only access.

Steps:

1. Open HQ dashboard and staff dashboard.
2. Open permit expiry reports and export CSV/XLS.
3. Open HQ reconciliation and transactions pages.
4. Compare issued permits, paid invoices, receipts, and WanGov/GovPay callbacks.
5. Confirm Executive Observer can view approved dashboards/reports only.
6. Confirm Compliance Auditor can inspect logs and reports without editing records.

Expected result:

- Reports load with correct airport/national scope.
- CSV/XLS exports work.
- Payment totals reconcile with documents.
- Oversight users are read-only.

Pass/Fail:

| Step | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Dashboards | Correct role/scope |  |  |  |
| Exports | CSV/XLS generated |  |  |  |
| Reconciliation | Totals match |  |  |  |
| Read-only | No mutation allowed |  |  |  |

## Technical Regression Commands

Run before UAT sign-off and before go-live:

```bash
php artisan migrate --force
composer validate --strict
php artisan test
composer security:test
composer security:owasp
composer audit --locked
npm audit --audit-level=high
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Minimum smoke URLs:

| URL | Expected |
| --- | --- |
| `/` | 200 |
| `/login` | 200 |
| `/up` | 200 |
| `/emergency-travel-certificate/apply` | 200 when ETC feature is enabled |
| `/verify/not-real-code` | 404 or controlled invalid document response |

## UAT Exit Criteria

UAT passes only when:

- All P0 and P1 defects are closed and retested.
- P2 defects are fixed or formally accepted by the SLID business owner.
- Permit flow passes from application to payment to approval to issuance to QR verification.
- ETC flow passes from public application to WanGov/GovPay payment to HQ decision to notification/status.
- Duplicate passport and duplicate NRA receipt cases do not crash and are handled correctly.
- Role-based access passes for all staff titles.
- Payment reconciliation passes for NRA receipt and WanGov/GovPay flows.
- PDF samples are approved by SLID.
- Email templates and sender identity are approved.
- OWASP readiness, dependency audits, backup/restore, monitoring, and data protection sign-offs are complete.
