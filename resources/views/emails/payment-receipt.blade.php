<!DOCTYPE html>
<html>
<head><meta charset="utf-8"/></head>
<body style="font-family: 'Inter', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
  <div style="background: linear-gradient(135deg, #D4AF37, #B8860B); padding: 30px; text-align: center;">
    <h1 style="color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 2px;">CIO INTERNATIONAL GOLF CLASSIC</h1>
    <p style="color: rgba(255,255,255,0.8); margin: 5px 0 0; font-size: 12px; letter-spacing: 3px; text-transform: uppercase;">Payment Receipt</p>
  </div>
  <div style="padding: 30px;">
    <div style="text-align: center; margin-bottom: 20px;">
      <div style="width: 60px; height: 60px; background: #0B5D3D; border-radius: 50%; margin: 0 auto; line-height: 60px; color: #fff; font-size: 28px;">✓</div>
      <h2 style="color: #1a1a1a; margin: 15px 0 5px;">Payment Confirmed</h2>
      <p style="color: #999; margin: 0;">7th Edition — 2026</p>
    </div>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
      <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">Receipt No.</td><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #1a1a1a; font-weight: 600; text-align: right;">{{ $receiptNumber }}</td></tr>
      <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">Player Name</td><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #1a1a1a; font-weight: 600; text-align: right;">{{ $playerName }}</td></tr>
      <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">Amount</td><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #0B5D3D; font-weight: 700; font-size: 18px; text-align: right;">₦{{ number_format($amount, 2) }}</td></tr>
      <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">Reference</td><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #1a1a1a; font-family: monospace; font-size: 12px; text-align: right;">{{ $reference }}</td></tr>
      <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">Payment Method</td><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #1a1a1a; text-align: right;">{{ $paymentMethod ?? 'Card' }} ({{ $paymentChannel ?? 'Online' }})</td></tr>
      <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">Date</td><td style="padding: 10px 0; border-bottom: 1px solid #eee; color: #1a1a1a; text-align: right;">{{ $paidAt }}</td></tr>
      <tr><td style="padding: 10px 0; color: #888; font-size: 13px;">Status</td><td style="padding: 10px 0; color: #0B5D3D; font-weight: 700; text-align: right;">COMPLETED</td></tr>
    </table>
    <p style="color: #999; font-size: 11px; text-align: center; margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px;">This receipt is computer-generated and does not require a signature.<br/>CIO International Golf Classic — Where Leadership Meets Excellence</p>
  </div>
</div>
</body>
</html>
