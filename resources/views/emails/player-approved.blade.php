<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a1a2e; color: #d4af37; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 20px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 24px; background: #d4af37; color: #1a1a2e; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Approved</h1>
        </div>
        <div class="body">
            <h2>Congratulations, {{ $name }}!</h2>
            <p>Your registration for the CIO International Golf Championship has been <strong>approved</strong>.</p>
            <p>To confirm your participation, please complete the payment using the link below:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $paymentLink }}" class="btn">Make Payment</a>
            </p>
            <p>If you have any questions, please contact the tournament organizers.</p>
            <p>Best regards,<br>CIO International Golf Classic Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CIO International Golf Classic. All rights reserved.
        </div>
    </div>
</body>
</html>
