<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Funny How</title>
    <style>
        body {
            background-color: #f2f2f2;
            font-family: 'Roboto', sans-serif;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #000000 !important;
            color: #ffffff !important;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .center {
            text-align: center;
        }
        .button {
            font-size: 24px;
            color: #ffffff !important;
            text-transform: uppercase;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 1;
            transition: opacity 0.3s;
        }
        .button:hover {
            opacity: 0.7;
        }
        .img-icon {
            height: 34px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" valign="top">
                <table class="email-container" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="center" style="padding: 20px 0;">
                            <img src="https://funny-how.com/mail/logo.png" style="width: 110px;">
                        </td>
                    </tr>
                    <tr>
                        <td class="center" style="padding: 20px; font-size: 18px; color: #fff !important;">
                        <h1 style="color: #ffffff !important; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Welcome to Funny How</h1>
                            <p>Hi {{$user['firstname']}} {{$user['lastname']}},</p>
                            <p>We're excited to have you on board!</p>
                            <p>Your studio is almost ready. To publish it, you need to add your payment information. Please follow the instructions in the GIF below:</p>
                            <img src="https://funny-how-s3-bucket.s3.amazonaws.com/public/email/gif.gif" alt="Instructions GIF" />
                            <p>To get started, you need to set your password as this is your first time logging in. Click the link below to set your new password:</p>
                            <div style="padding: 10px; text-align: center;">
                                <p>
                                    <a href="{{ $resetUrl }}" style="display: inline-block; width: 100%; height: 44px; background-color: #ffffff; color: #333333; border-radius: 10px; text-align: center; line-height: 44px; font-size: 14px; font-weight: 500; letter-spacing: 0.05em; text-decoration: none; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=1">Set New Password</a>
                                </p>
                            </div>
                            <p>After setting your password, log in to your account and follow the instructions in your dashboard to complete the setup.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="center" style="padding: 20px 0; margin-top: 20px; font-size: 12px; color: #cccccc !important;">
                            &copy; 2024 Funny How. All rights reserved.
                        </td>
                    </tr>
                    <tr>
                        <td class="center" style="padding: 5px 0;">
                            <a style="font-size: 12px; color: #cccccc !important;" href="{{env('UNSUBSCRIBE_URL')}}">Unsubscribe</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>