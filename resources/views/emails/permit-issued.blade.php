<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Travel Certificate Issued</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
    <h2>Sierra Leone Immigration Department</h2>
    <p>Your Emergency Travel Certificate has been issued.</p>

    <p><strong>Certificate Number:</strong> {{ $permit->permit_no }}</p>
    <p><strong>Verification Code:</strong> {{ $permit->verification_code }}</p>
    <p><strong>Valid Until:</strong> {{ optional($permit->valid_until)->format('Y-m-d') }}</p>

    <p>
        Verify your certificate on the official portal:
        <br>
        {{ route('verify.permit', $permit->verification_code) }}
    </p>

    <p>
        Open your Digital Emergency Travel Certificate:
        <br>
        {{ route('digital.certificates.show', $permit->verification_code) }}
    </p>

    <p>
        Traveler verification notice:
        verify before travel.
        A certificate that cannot be verified on the official system should not be accepted as valid.
    </p>
</body>
</html>
