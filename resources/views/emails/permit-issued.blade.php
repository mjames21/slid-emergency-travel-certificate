<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Permit Issued</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
    <h2>Sierra Leone Immigration Department</h2>
    <p>Your Visa on Arrival permit has been issued.</p>

    <p><strong>Permit Number:</strong> {{ $permit->permit_no }}</p>
    <p><strong>Verification Code:</strong> {{ $permit->verification_code }}</p>
    <p><strong>Valid Until:</strong> {{ optional($permit->valid_until)->format('Y-m-d') }}</p>

    <p>
        Verify your permit on the official portal:
        <br>
        {{ route('verify.permit', $permit->verification_code) }}
    </p>

    <p>
        Traveler verification notice:
        verify before leaving the immigration desk.
        A permit that cannot be verified on the official system should not be accepted as valid.
    </p>
</body>
</html>
