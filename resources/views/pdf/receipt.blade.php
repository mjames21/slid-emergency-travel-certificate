{{-- FILE: resources/views/pdf/receipt.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $receipt->receipt_no }}</title>
    <style>
        @page {
            margin: 10mm;
        }

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

        .security-box {
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
            border: 1px solid #10b981;
            background: #ecfdf5;
            color: #065f46;
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
    use App\Support\PrintableSecurityValue;

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
        OFFICIAL PAYMENT RECEIPT
    </div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">Receipt Number</div>
                <div class="value">{{ $receipt->receipt_no }}</div>
            </td>
            <td>
                <div class="label">Issued At</div>
                <div class="value">{{ optional($receipt->issued_at)->format('d-M-Y h:i A') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Invoice Number</div>
                <div class="value">{{ $receipt->payment->invoice->invoice_no }}</div>
            </td>
            <td>
                <div class="label">Application Number</div>
                <div class="value">{{ $receipt->payment->invoice->visaApplication->application_no }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Gateway</div>
                <div class="value">{{ strtoupper($receipt->payment->gateway) }}</div>
            </td>
            <td>
                <div class="label">Gateway Reference</div>
                <div class="value">{{ $receipt->payment->gateway_reference ?: '—' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Transaction ID</div>
                <div class="value">{{ $receipt->payment->gateway_transaction_id ?: '—' }}</div>
            </td>
            <td>
                <div class="label">Payment Channel</div>
                <div class="value">{{ strtoupper($receipt->payment->payment_channel ?: '—') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Receipt Source</div>
                <div class="value">{{ strtoupper(str_replace('_', ' ', $receipt->receipt_source ?: 'internal')) }}</div>
            </td>
            <td>
                <div class="label">Evidence Hash</div>
                <div class="value">{{ $receipt->evidence_hash ? PrintableSecurityValue::short($receipt->evidence_hash) : '—' }}</div>
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
                <td>{{ strtoupper($receipt->payment->invoice->visaApplication->passenger->full_name) }}</td>
                <td>{{ strtoupper($receipt->payment->invoice->visaApplication->passenger->passport_number) }}</td>
                <td>{{ strtoupper($receipt->payment->invoice->visaApplication->passenger->nationality) }}</td>
                <td>{{ strtoupper($receipt->payment->invoice->visaApplication->point_of_entry) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Payment Summary</div>

    <table class="details">
        <thead>
            <tr>
                <th>Amount Due</th>
                <th>Amount Paid</th>
                <th>Currency</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format((float) $receipt->payment->amount_due, 2) }}</td>
                <td>{{ number_format((float) $receipt->payment->amount_paid, 2) }}</td>
                <td>{{ strtoupper($receipt->payment->currency) }}</td>
                <td>{{ strtoupper($receipt->payment->status->value) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount-label">Official Amount Received</div>
        <div class="amount-value">
            {{ number_format((float) $receipt->payment->amount_paid, 2) }} {{ strtoupper($receipt->payment->currency) }}
        </div>
    </div>

    <div class="security-box">
        <strong>Receipt Hash Ref:</strong> {{ PrintableSecurityValue::short($receipt->document_hash) }}<br>
        <strong>Invoice Ref:</strong> {{ $receipt->payment->invoice->invoice_no }}<br>
        <strong>Payment Ref:</strong> {{ $receipt->payment->invoice->payment_reference }}
    </div>

    <div class="warning-note">
        This is an official government receipt confirming payment recorded in the Sierra Leone Immigration Department workflow.
        Retain this receipt for permit issuance, traveler inspection, and audit verification.
    </div>

    <div class="footer">
        Sierra Leone Immigration Department Official Document<br>
        This receipt should be presented together with the related visa permit where required.
    </div>
</div>
</body>
</html>
