<!DOCTYPE html>
<html>
<head>
    <title>Invitation to Join as Studio Engineer</title>
</head>
<body style="background-color: #f2f2f2; font-family: 'Roboto', sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #000000; color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px 0;">
        <img src="https://funny-how.com/mail/logo.png" style="width: 110px;">
    </div>
    <div style="padding: 20px; text-align: left; font-size:18px;color:#fff;">
        <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Invitation to Join as Studio Engineer</h1>
        <p>Dear {{ $user->username }},</p>
        <p>You have been invited to join our team as a Studio Engineer. To accept the invitation, please reset your password by clicking the link below:</p>
        <p>
            <a href="{{ $resetLink }}" style="color: #ffffff; font-weight: bold;">Reset Your Password</a>
        </p>
        <p>Thank you, and we look forward to having you on board!</p>
    </div>
    <div style="text-align: center; padding: 20px 0; margin-top: 20px; font-size: 12px; color: #cccccc;">
        &copy; 2024 Your Company. All rights reserved.
    </div>
</div>
</body>
</html>