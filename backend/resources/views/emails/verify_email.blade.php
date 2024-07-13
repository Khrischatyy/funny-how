<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #000000;
            color: #ffffff;
            font-family: Arial, sans-serif;
            border-radius: 10px;
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #FFC107;
            color: #000000;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
            font-size: 12px;
            color: #ffffff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Funny How Logo">
    </div>
    <div class="content">
        <h1>Welcome to Funny How!</h1>
        <p>Hey {{ $user->firstname }} {{ $user->lastname }},</p>
        <p>Wowwee! Thanks for registering an account with Funny How! You're the coolest person in all the land (and I've met a lot of really cool people).</p>
        <p>Before we get started, we'll need to verify your email.</p>
        <a href="{{ $url }}" class="button">Verify Email</a>
    </div>
    <div class="footer">
        &copy; 2024 Funny How LLC. All rights reserved.
    </div>
</div>
</body>
</html>