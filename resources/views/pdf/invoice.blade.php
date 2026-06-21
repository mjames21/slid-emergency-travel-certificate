{{-- FILE: resources/views/pdf/invoice.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        @page { margin: 10mm; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .page {
            border: 2px solid #555;
            border-radius: 16px;
            padding: 14px 16px;
        }

        .top-logo {
            text-align: center;
            margin-bottom: 6px;
        }

        .top-logo img {
            max-height: 58px;
            margin: 0 auto;
        }

        .header-main {
            font-family: DejaVu Serif, serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
            text-align: center;
            margin-bottom: 4px;
        }

        .header-sub,
        .header-contact {
            text-align: center;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .title-band {
            margin-top: 10px;
            background: #1f2937;
            color: #fff;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            padding: 8px 10px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .meta td {
            width: 50%;
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            vertical-align: top;
        }

        .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .value {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
        }

        .section-title {
            margin-top: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #111827;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .details th,
        .details td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        .details th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            color: #374151;
        }

        .amount-box {
            margin-top: 14px;
            border: 2px solid #111827;
            border-radius: 10px;
            padding: 12px;
            text-align: right;
        }

        .amount-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 700;
        }

        .amount-value {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            margin-top: 4px;
        }

        .bank-note {
            margin-top: 12px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 9px;
            line-height: 1.35;
        }

        .warning-note {
            margin-top: 10px;
            border: 1px solid #f59e0b;
            background: #fffbeb;
            color: #92400e;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 9px;
            line-height: 1.35;
        }

        .footer {
            margin-top: 14px;
            font-size: 9px;
            color: #4b5563;
            text-align: center;
            line-height: 1.35;
        }
    </style>
</head>
<body>
@php
    use App\Models\SystemSetting;

    $officialAddress = SystemSetting::getValue('permit_official_address', '14 GLOUSCESTER STREET, FREETOWN, SIERRA LEONE');
    $officialPhone = SystemSetting::getValue('permit_official_phone', 'TEL: (+232) 22 224446 / 22 224447');
@endphp

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

    <div class="title-band">
        OFFICIAL VISA ON ARRIVAL PAYMENT ORDER
    </div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">Invoice Number</div>
                <div class="value">{{ $invoice->invoice_no }}</div>
            </td>
            <td>
                <div class="label">Payment Reference</div>
                <div class="value">{{ $invoice->payment_reference }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Application Number</div>
                <div class="value">{{ $invoice->visaApplication->application_no }}</div>
            </td>
            <td>
                <div class="label">Issued At</div>
                <div class="value">{{ optional($invoice->issued_at)->format('d-M-Y h:i A') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Airport</div>
                <div class="value">{{ strtoupper($invoice->visaApplication->airport->name) }}</div>
            </td>
            <td>
                <div class="label">Desk</div>
                <div class="value">{{ strtoupper($invoice->visaApplication->desk?->name ?: '—') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Status</div>
                <div class="value">{{ strtoupper($invoice->status->value) }}</div>
            </td>
            <td>
                <div class="label">Gateway</div>
                <div class="value">{{ strtoupper($invoice->gateway) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Traveler Details</div>

    <table class="details">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Passport Number</th>
                <th>Nationality</th>
                <th>Point of Entry</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ strtoupper($invoice->visaApplication->passenger->full_name) }}</td>
                <td>{{ strtoupper($invoice->visaApplication->passenger->passport_number) }}</td>
                <td>{{ strtoupper($invoice->visaApplication->passenger->nationality) }}</td>
                <td>{{ strtoupper($invoice->visaApplication->point_of_entry) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Visit Details</div>

    <table class="details">
        <thead>
            <tr>
                <th>Purpose</th>
                <th>Period of Stay</th>
                <th>Host Name</th>
                <th>Valid Until</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ strtoupper($invoice->visaApplication->purpose_of_visit) }}</td>
                <td>{{ strtoupper($invoice->visaApplication->period_of_stay_text ?: $invoice->visaApplication->period_of_stay_days . ' DAYS') }}</td>
                <td>{{ strtoupper($invoice->visaApplication->host_name ?: '—') }}</td>
                <td>{{ optional($invoice->visaApplication->valid_until)->format('d-M-Y') ?: '—' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount-label">Amount Payable</div>
        <div class="amount-value">{{ number_format((float) $invoice->amount, 2) }} {{ strtoupper($invoice->currency) }}</div>
    </div>

    <div class="bank-note">
        <strong>Payment Instruction:</strong><br>
        Present this payment order through the official government payment channel using the payment reference:
        <strong>{{ $invoice->payment_reference }}</strong>.
    </div>

    <div class="warning-note">
        This is an official government payment order. A visa permit must not be issued unless payment is successfully confirmed in the official system or a formally approved waiver exists.
    </div>

    <div class="footer">
        Sierra Leone Immigration Department Official Document<br>
        Retain this invoice for payment confirmation, receipt issuance, and permit processing.
    </div>
</div>
</body>
</html>
