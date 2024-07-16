<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body style="background-color: #f2f2f2; font-family: 'Roboto', sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #000000; color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px 0;">
        <img src="https://funny-how.com/mail/favicon.png" style="width: 110px;">
    </div>
    <div style="padding: 20px; text-align: center;  font-size:18px;color:#fff;">
        <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Password Reset Request</h1>
        <p>Dear {{ $email }},</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <p>Click the button below to reset your password:</p>
        <p><a href="{{ $resetUrl }}" style="display: inline-block; width: 100%; height: 44px; background-color: #ffffff; color: #333333; border-radius: 10px; text-align: center; line-height: 44px; font-size: 14px; font-weight: 500; letter-spacing: 0.05em; text-decoration: none; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=1">Reset Password</a></p>
        <p>If you did not request a password reset, no further action is required.</p>
    </div>
    <div style="text-align: center; padding: 20px 0; margin-top: 20px; font-size: 12px; color: #cccccc;">
        &copy; 2024 Funny How. All rights reserved.
    </div>
    <div style="text-align: center; padding: 5px 0;">
        <a style="font-size: 12px; color: #cccccc;" href="{{env('UNSUBSCRIBE_URL')}}">Unsubscribe</a>
    </div>
</div>
</body>
</html>