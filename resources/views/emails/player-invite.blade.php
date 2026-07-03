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
            <h1>CIO International Golf Championship</h1>
        </div>
        <div class="body">
            <h2>Welcome, {{ $name }}!</h2>
            <p>Your club, <strong>{{ $clubName }}</strong>, has submitted your details for the CIO International Golf Championship tournament edition.</p>
            <p>To complete your registration and access the portal, please verify your email address by clicking the button below:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $verificationUrl }}" class="btn">Verify Email & Complete Registration</a>
            </p>
            <p><strong>Your temporary login credentials:</strong></p>
            <p>Email: {{ $email }}<br>
            Password: {{ $password }}</p>
            <p>After logging in, you'll be prompted to complete your profile with additional details.</p>
            <p>Best regards,<br>CIO International Golf Classic Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CIO International Golf Classic. All rights reserved.
        </div>
    </div>
</body>
</html>
