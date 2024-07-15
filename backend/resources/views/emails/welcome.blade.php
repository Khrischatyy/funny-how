<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Funny How</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f2f2f2;
            font-family: 'Roboto', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #000000;
            color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #FFC107;
            color: #000000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 20px;
            font-size: 12px;
            color: #cccccc;
        }
        .font-bebasneue {
            font-family: 'Bebas Neue', sans-serif;
        }
        .text-4xl {
            font-size: 24px;
        }
        .text-white {
            color: #ffffff;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .hover-opacity-70:hover {
            opacity: 0.7;
        }
        .flex {
            display: flex;
        }
        .flex-col {
            flex-direction: column;
        }
        .gap-5 {
            gap: 20px;
        }
        .gap-3 {
            gap: 12px;
        }
        .justify-center {
            justify-content: center;
        }
        .items-center {
            align-items: center;
        }
        .h-30 {
            height: 30px;
        }
        .h-25 {
            height: 25px;
        }
        .relative {
            position: relative;
        }
        .translate-y-1 {
            transform: translateY(-4px);
        }
        .translate-x-2 {
            transform: translateX(8px);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://funny-how.com/logo.png" alt="Funny How Logo">
    </div>
    <div class="content">
        <h1>Welcome to Funny How</h1>
        <p>Hi {{$user['firstname']}} {{$user['lastname']}},</p>
        <p>We're excited to have you on board.</p>
        <p>You can start booking studios right now!</p>
        <div class="font-bebasneue flex flex-col text-center gap-5">
            <a href="https://funny-how.com/map" class="text-4xl text-white uppercase hover-opacity-70">
                <div class="flex gap-3 justify-center items-center">
                    <div>Find Some Here</div>
                    <img class="h-30" src="https://funny-how.com/mail/map.svg" alt="Funny How">
                </div>
            </a>
            <a href="https://funny-how.com/studios" class="text-4xl text-white uppercase hover-opacity-70 flex justify-center">
                <div>Lock In Your Session</div>
                <div class="flex items-center justify-center relative translate-y-1 translate-x-2">
                    <img class="h-25" src="https://funny-how.com/mail/lockin.svg" alt="Funny How">
                </div>
            </a>
        </div>
    </div>
    <div class="footer">
        &copy; 2024 Funny How. All rights reserved.
    </div>
</div>
</body>
</html>