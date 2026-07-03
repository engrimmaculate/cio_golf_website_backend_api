<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #0a1628 0%, #1a3a2a 100%); padding: 30px; text-align: center; }
        .header h1 { color: #d4a853; margin: 0; font-size: 24px; }
        .body { padding: 30px; color: #333; }
        .body p { line-height: 1.7; margin: 0 0 16px; }
        .btn { display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #d4a853 0%, #b8943a 100%); color: #0a1628 !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; margin: 20px 0; }
        .footer { padding: 20px 30px; background: #f9f9f9; text-align: center; color: #888; font-size: 12px; }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CIO International Golf Championship</h1>
        </div>
        <div class="body">
            <p>Hello {{ $name }},</p>
            <p>Thank you for registering for the CIO International Golf Championship. Please verify your email address by clicking the button below.</p>
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
            </p>
            <p>If you did not create an account, no further action is required.</p>
            <p>Best regards,<br>CIO Golf Championship Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CIO International Golf Championship. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
