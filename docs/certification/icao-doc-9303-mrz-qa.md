# ICAO Doc 9303 MRZ QA Plan

Purpose: prepare SLID visa and border-control workflows for formal MRZ acceptance testing.

Scope:
- Passport TD3 MRZ intake parsing.
- Visa MRV-A permit MRZ generation and validation.
- MRV-B validation support for smaller visa formats.
- Check-digit failure detection for passport number, date of birth, and document/visa expiry.
- Officer review path when OCR confidence or check digits fail.

Engineering evidence:
- `tests/Unit/PassportMrzParserTest.php`
- `tests/Unit/VisaMrzValidatorTest.php`
- `app/Services/Mrz/MrzParser.php`
- `app/Services/Mrz/VisaMrzValidator.php`
- `app/Support/MrzGenerator.php`

Acceptance test sample set:
- Valid Sierra Leone passport specimen.
- Expired passport specimen.
- Passport with damaged or low-contrast MRZ.
- Passport with long name and filler characters.
- Passport with different sex markers.
- Valid SLID visa permit PDF MRZ.
- Permit MRZ with edited passport-number check digit.
- Permit MRZ with edited date check digit.
- Permit MRZ with edited expiry check digit.

Pass criteria:
- Valid samples parse with all required check digits passing.
- Failed check digits stop automatic “MRZ verified” decisions.
- Manual exception decisions are visible in admissibility screening notes.
- Permit PDF MRZ lines remain exactly 44 characters for MRV-A.
- Officer cannot confuse OCR text with verified MRZ when check digits fail.

External gate:
- Formal acceptance must be signed off by SLID using approved real or controlled specimen documents.
