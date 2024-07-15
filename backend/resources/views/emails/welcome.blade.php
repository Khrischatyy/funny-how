<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Funny How</title>
</head>
<body style="background-color: #f2f2f2; font-family: 'Roboto', sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #000000; color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px 0;">
        <img src="https://funny-how.com/mail/favicon.png" style="width: 110px;">
    </div>
    <div style="padding: 20px; text-align: center;">
        <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Welcome to Funny How</h1>
        <p>Hi {{$user['firstname']}} {{$user['lastname']}},</p>
        <p>We're excited to have you on board.</p>
        <p>You can start booking studios right now!</p>
        <div style="font-family: 'Bebas Neue', sans-serif; text-align: center; gap: 20px;">
            <a href="https://funny-how.com/map" style="font-size: 24px; color: #ffffff; text-transform: uppercase; text-decoration: none; display: flex; gap: 12px; justify-content: center; align-items: center; opacity: 1; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                <div>Find Some Here</div>
                <img src="https://funny-how.com/mail/map.png">
            </a><br/><br/>
            <a href="https://funny-how.com/studios" style="font-size: 24px; color: #ffffff; text-transform: uppercase; text-decoration: none; display: flex; justify-content: center; align-items: center; opacity: 1; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                <div>Lock In Your Session</div>
                <div style="display: flex; align-items: center; justify-content: center; position: relative; transform: translateY(-4px) translateX(8px);">
                <img src="https://funny-how.com/mail/lockin.png">
                </div>
            </a>
        </div>
    </div>
    <div style="text-align: center; padding: 20px 0; margin-top: 20px; font-size: 12px; color: #cccccc;">
        &copy; 2024 Funny How. All rights reserved.
    </div>
</div>
</body>
</html>