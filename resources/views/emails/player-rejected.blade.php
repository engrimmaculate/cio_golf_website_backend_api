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
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Update</h1>
        </div>
        <div class="body">
            <h2>Dear {{ $name }},</h2>
            <p>Your registration for the CIO International Golf Championship was not approved at this time.</p>
            @if($reason)
            <p><strong>Reason:</strong> {{ $reason }}</p>
            @endif
            <p>If you believe this is an error, please contact the tournament organizers for further clarification.</p>
            <p>Best regards,<br>CIO International Golf Classic Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CIO International Golf Classic. All rights reserved.
        </div>
    </div>
</body>
</html>
