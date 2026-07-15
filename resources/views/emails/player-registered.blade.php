<!DOCTYPE html>
<html>
<head><meta charset="utf-8"/></head>
<body style="font-family: 'Inter', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
  <div style="background: linear-gradient(135deg, #D4AF37, #B8860B); padding: 30px; text-align: center;">
    <h1 style="color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 2px;">CIO INTERNATIONAL GOLF CLASSIC</h1>
    <p style="color: rgba(255,255,255,0.8); margin: 5px 0 0; font-size: 12px; letter-spacing: 3px; text-transform: uppercase;">7th Edition — 2026</p>
  </div>
  <div style="padding: 30px;">
    <h2 style="color: #1a1a1a; margin: 0 0 15px;">Registration Received</h2>
    <p style="color: #555; line-height: 1.7;">Dear {{ $name }},</p>
    <p style="color: #555; line-height: 1.7;">Thank you for registering for the <strong style="color: #D4AF37;">CIO International Golf Classic — 7th Edition</strong>. Your registration has been received and is being processed.</p>
    <div style="background: #fdf6e3; border-left: 4px solid #D4AF37; padding: 15px; margin: 20px 0; border-radius: 4px;">
      <p style="margin: 0; color: #555; font-size: 14px;"><strong>Tournament:</strong> CIO International Golf Classic</p>
      <p style="margin: 5px 0 0; color: #555; font-size: 14px;"><strong>Edition:</strong> 7th Edition — 2026</p>
      <p style="margin: 5px 0 0; color: #555; font-size: 14px;"><strong>Status:</strong> Registration Pending Verification</p>
    </div>
    <p style="color: #555; line-height: 1.7;">Please verify your email address to activate your account and proceed with payment.</p>
    <div style="text-align: center; margin: 25px 0;">
      <a href="{{ $verificationUrl }}" style="display: inline-block; background: #D4AF37; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Verify Email & Activate Account</a>
    </div>
    <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">This is an official communication from the CIO International Golf Classic. If you did not register, please ignore this email.</p>
  </div>
</div>
</body>
</html>
