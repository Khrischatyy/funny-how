<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body>
<h1>Password Reset Request</h1>
<h1>DAAAA you here</h1>
<p>Dear {{ $email }},</p>
<p>You are receiving this email because we received a password reset request for your account. Click the button below to
    reset your password:</p>
<p><a href="{{ $resetUrl }}"
      style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; border-radius: 5px; text-decoration: none;">Reset
        Password</a></p>
<p>If you did not request a password reset, no further action is required.</p>
<p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>