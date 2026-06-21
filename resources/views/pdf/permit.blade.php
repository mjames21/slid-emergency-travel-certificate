{{-- FILE: resources/views/pdf/permit.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Permit {{ $permit->permit_no }}</title>
    <style>
        @page {
            margin: 8mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        .watermark {
            position: fixed;
            top: 34%;
            left: 6%;
            width: 88%;
            text-align: center;
            transform: rotate(-28deg);
            font-size: 40px;
            font-weight: 800;
            color: rgba(120, 120, 120, 0.08);
            line-height: 1.2;
            letter-spacing: 2px;
            z-index: 0;
        }

        .page {
            position: relative;
            z-index: 1;
            border: 2px solid #555;
            border-radius: 16px;
            padding: 10px 12px;
        }

        .top-logo {
            text-align: center;
            margin-bottom: 4px;
        }

        .top-logo img {
            max-height: 58px;
            margin: 0 auto;
        }

        .header-main {
            font-family: DejaVu Serif, serif;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.05;
            text-align: center;
            margin-bottom: 4px;
        }

        .header-sub,
        .header-contact {
            text-align: center;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .top-meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .top-meta td {
            font-size: 10px;
            vertical-align: top;
        }

        .left {
            width: 22%;
            text-align: left;
        }

        .middle {
            width: 56%;
            text-align: center;
        }

        .right {
            width: 22%;
            text-align: right;
        }

        .rule {
            border-top: 1px solid #777;
            margin: 4px 0 6px 0;
        }

        .permit-title {
            font-family: DejaVu Serif, serif;
            font-size: 17px;
            font-weight: 700;
            text-align: center;
            margin: 4px 0 2px 0;
        }

        .permit-subtitle {
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 5px 3px;
            vertical-align: top;
        }

        .label-col {
            width: 24%;
            font-weight: 700;
            text-transform: uppercase;
        }

        .value-col {
            width: 52%;
            font-weight: 700;
        }

        .qr-col {
            width: 24%;
            text-align: center;
            vertical-align: top;
        }

        .qr-box {
            border: 1px solid #999;
            padding: 4px;
            display: inline-block;
            background: #fff;
        }

        .qr-box img {
            width: 88px;
            height: 88px;
            display: block;
        }

        .verify-code {
            margin-top: 4px;
            font-size: 8px;
            word-break: break-word;
            font-weight: 700;
            line-height: 1.2;
        }

        .instruction-band {
            margin-top: 6px;
            background: #333;
            color: #fff;
            text-align: center;
            font-weight: 700;
            font-size: 9px;
            padding: 6px 8px;
            border-radius: 5px;
            line-height: 1.2;
        }

        .verify-note {
            margin-top: 5px;
            font-size: 8px;
            text-align: center;
            line-height: 1.25;
        }

        .signature-area {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }

        .signature-area td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 10px;
        }

        .signature-line {
            border-top: 1px dotted #777;
            margin: 0 18px 4px 18px;
            height: 1px;
        }

        .sig-name {
            font-weight: 700;
            color: #1d4ed8;
            font-size: 11px;
        }

        .sig-title {
            font-style: italic;
            font-size: 10px;
            margin-top: 3px;
        }

        .stamp-note {
            font-size: 9px;
            text-align: left;
            margin-top: 2px;
        }

        .security {
            margin-top: 6px;
            font-size: 7px;
            line-height: 1.2;
        }

        .mrz {
            margin-top: 6px;
            border: 1px solid #555;
            padding: 5px 8px;
            font-family: "Courier New", monospace;
            font-size: 11px;
            letter-spacing: 1px;
            background: #fafafa;
        }
    </style>
</head>
<body>
@php
    use App\Models\SystemSetting;
    use App\Support\PrintableSecurityValue;

    $signingOfficerName = SystemSetting::getValue('permit_signing_officer_name', 'Dr. Moses Tiffa Baio, Esq');
    $signingOfficerTitle = SystemSetting::getValue('permit_signing_officer_title', 'IMMIGRATION OFFICER');

    $chiefOfficerName = SystemSetting::getValue('permit_chief_officer_name', 'CHIEF IMMIGRATION OFFICER');
    $chiefOfficerTitle = SystemSetting::getValue('permit_chief_officer_title', 'CHIEF IMMIGRATION OFFICER');

    $attentionLine1 = SystemSetting::getValue('permit_attention_line', 'ATTN: IMMIGRATION - LUNGI');
    $attentionLine2 = SystemSetting::getValue('permit_attention_line_2', 'ONS - STATE HOUSE');
    $officialAddress = SystemSetting::getValue('permit_official_address', '14 GLOUSCESTER STREET, FREETOWN, SIERRA LEONE');
    $officialPhone = SystemSetting::getValue('permit_official_phone', 'TEL: (+232) 22 224446 / 22 224447');

    $watermarkMain = 'SIERRA LEONE IMMIGRATION';
    $watermarkSub = 'OFFICIAL PERMIT';

    if ($permit->is_duplicate_print) {
        $watermarkSub = 'OFFICIAL REPRINT';
    }

    if ($permit->status->value === 'cancelled') {
        $watermarkSub = 'CANCELLED';
    }

    if ($permit->status->value === 'revoked') {
        $watermarkSub = 'REVOKED';
    }
@endphp

<div class="watermark">
    {{ $watermarkMain }}<br>
    {{ $watermarkSub }}<br>
    {{ $permit->visa_id ?: $permit->permit_no }}
</div>

<div class="page">
    <div class="top-logo">
        <img src="{{ public_path('images/slid-logo.png') }}" alt="SLID Logo">
    </div>

    <div class="header-main">
        MINISTRY OF INTERNAL AFFAIRS<br>
        SIERRA LEONE IMMIGRATION SERVICE
    </div>

    <div class="header-sub">{{ strtoupper($officialAddress) }}</div>
    <div class="header-contact">{{ $officialPhone }}</div>

    <table class="top-meta">
        <tr>
            <td class="left">
                <strong>VISA ID</strong><br>
                {{ $permit->visa_id ?: $permit->permit_no }}
            </td>
            <td class="middle"></td>
            <td class="right">
                <strong>{{ optional($permit->issued_at)->format('d-M-y') }}</strong><br>
                <strong>{{ optional($permit->issued_at)->format('g:i A') }}</strong>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    <div class="permit-title">PERMISSION TO ENTER SIERRA LEONE</div>
    <div class="permit-subtitle">
        PERMISSION IS HEREBY GRANTED TO THE FOLLOWING PERSON / PERSONS TO ENTER SIERRA LEONE
    </div>

    <div class="rule"></div>

    <table class="details">
        <tr>
            <td class="label-col">FULL NAME</td>
            <td class="value-col">{{ strtoupper($permit->visaApplication->passenger->full_name) }}</td>
            <td class="qr-col" rowspan="8">
                @if (!empty($qrImageBase64))
                    <div class="qr-box">
                        <img src="{{ $qrImageBase64 }}" alt="QR Code">
                    </div>
                @endif
                <div class="verify-code">
                    Verification Code:<br>
                    {{ $permit->verification_code }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="label-col">NATIONALITY</td>
            <td class="value-col">{{ strtoupper($permit->visaApplication->passenger->nationality) }}</td>
        </tr>
        <tr>
            <td class="label-col">PASSPORT NUMBER</td>
            <td class="value-col">{{ strtoupper($permit->visaApplication->passenger->passport_number) }}</td>
        </tr>
        <tr>
            <td class="label-col">OCCUPATION</td>
            <td class="value-col">{{ strtoupper($permit->visaApplication->passenger->occupation ?: 'VISITOR') }}</td>
        </tr>
        <tr>
            <td class="label-col">PURPOSE</td>
            <td class="value-col">{{ strtoupper($permit->visaApplication->purpose_of_visit) }}</td>
        </tr>
        <tr>
            <td class="label-col">POINT OF ENTRY</td>
            <td class="value-col">{{ strtoupper($permit->visaApplication->point_of_entry) }}</td>
        </tr>
        <tr>
            <td class="label-col">VALID UNTIL</td>
            <td class="value-col">{{ optional($permit->valid_until)->format('d-M-y') }}</td>
        </tr>
        <tr>
            <td class="label-col">PERIOD OF STAY</td>
            <td class="value-col">
                {{ strtoupper($permit->visaApplication->period_of_stay_text ?: $permit->visaApplication->period_of_stay_days . ' DAYS') }}
            </td>
        </tr>
        <tr>
            <td class="label-col">FLIGHT</td>
            <td class="value-col" colspan="2">
                {{ strtoupper($permit->visaApplication->flight_details ?: trim(($permit->visaApplication->flight_carrier ?: '') . '/' . ($permit->visaApplication->flight_number ?: ''))) }}
            </td>
        </tr>
        <tr>
            <td class="label-col">HOST NAME</td>
            <td class="value-col" colspan="2">{{ strtoupper($permit->visaApplication->host_name ?: '—') }}</td>
        </tr>
        <tr>
            <td class="label-col">HOST ADDRESS</td>
            <td class="value-col" colspan="2">{{ strtoupper($permit->visaApplication->host_address ?: '—') }}</td>
        </tr>
        <tr>
            <td class="label-col">FEES PAID</td>
            <td class="value-col" colspan="2">
                @if ($permit->payment_id)
                    {{ number_format((float) $permit->payment->amount_paid, 2) }} {{ strtoupper($permit->payment->currency) }}
                @elseif ($permit->waiver_approval_id)
                    GRATIS
                @else
                    NOT AVAILABLE
                @endif
            </td>
        </tr>
    </table>

    <div class="instruction-band">
        SPECIAL INSTRUCTION FROM THE CHIEF IMMIGRATION OFFICER<br>
        THIS PERMIT IS ONLY VALID WITH RECEIPT OF PAYMENT ATTACHED
    </div>

    <div class="verify-note">
        TRAVELER VERIFICATION NOTICE — VERIFY BEFORE LEAVING THE IMMIGRATION DESK.<br>
        Scan the QR code or visit the official Sierra Leone Immigration Department portal and enter the verification code.
    </div>

    <table class="signature-area">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div class="sig-name">{{ strtoupper($signingOfficerName) }}</div>
                <div class="sig-title">{{ strtoupper($signingOfficerTitle) }}</div>
                <div class="stamp-note">{{ $attentionLine1 }}<br>{{ $attentionLine2 }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div class="sig-name">{{ strtoupper($chiefOfficerName) }}</div>
                <div class="sig-title">{{ strtoupper($chiefOfficerTitle) }}</div>
            </td>
        </tr>
    </table>

    <div class="security">
        <strong>Security Seal Ref:</strong> {{ PrintableSecurityValue::short($permit->security_seal) }}<br>
        <strong>Document Hash Ref:</strong> {{ PrintableSecurityValue::short($permit->document_hash) }}<br>
        <strong>Payment Basis:</strong>
        @if ($permit->payment_id)
            VERIFIED PAYMENT / RECEIPT {{ $permit->receipt?->receipt_no ?: 'PENDING' }}
        @elseif ($permit->waiver_approval_id)
            APPROVED WAIVER
        @else
            UNKNOWN
        @endif
    </div>

    <div class="mrz">
        {{ $permit->mrz_line_1 }}<br>
        {{ $permit->mrz_line_2 }}
    </div>
</div>
</body>
</html>
