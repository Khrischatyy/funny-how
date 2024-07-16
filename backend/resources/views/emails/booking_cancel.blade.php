<!DOCTYPE html>
<html>
<head>
    <title>Booking Cancelled</title>
</head>
<body style="background-color: #f2f2f2; font-family: 'Roboto', sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #000000; color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px 0;">
        <img src="https://funny-how.com/mail/favicon.png" style="width: 110px;">
    </div>
    <div style="padding: 20px; text-align: left; font-size:18px;color:#fff;">
        <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Booking Cancelled</h1>
        <p>Dear {{ $user->firstname }},</p>
        <p>Your booking has been successfully cancelled. Here are the details:</p>
        <p>
            <strong>Booking ID:</strong> {{ $booking->id }}<br>
            <strong>Date:</strong> {{ $booking->date }}<br>
            <strong>Start Time:</strong> {{ $booking->start_time }}<br>
            <strong>End Time:</strong> {{ $booking->end_time }}<br>
            <strong>Location:</strong> <a href="https://funny-how.com/{{ '@'.$booking->address->slug }}" style="color: #ffffff;">{{ $booking->address->street }}</a>
        </p>
        <p>Thank you for using our service.</p>
    </div>
    <div style="padding: 20px; text-align: center;">
        <p>Check our studios, we've got more than 1000 of them:</p>
        <div style="color: #ffffff; font-size: 24px; font-weight: 700; text-align: center; gap: 20px;">
            <div style="width:100%; text-align: center; margin-bottom: 20px; justify-content: center;">
                <a href="https://funny-how.com/map" style="font-size: 24px; color: #ffffff; text-transform: uppercase; text-decoration: none; gap: 12px; justify-content: center; align-items: center; opacity: 1; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                    <div>Find Some Here <img style="height: 34px; margin-left: 10px;" src="https://funny-how.com/mail/map.png"></div>
                </a>
            </div>
            <div style="width:100%; text-align: center; justify-content: center;">
                <a href="https://funny-how.com/studios" style="font-size: 24px; color: #ffffff; text-transform: uppercase; text-decoration: none; justify-content: center; align-items: center; opacity: 1; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                    <div>Lock In Your Session<img style="height: 34px; margin-left: 10px;" src="https://funny-how.com/mail/lockin.png"></div>
                </a>
            </div>
        </div>
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