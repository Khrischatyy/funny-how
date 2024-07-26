<!DOCTYPE html>
<html>
<head>
    <title>Booking Placed</title>
</head>
<body style="background-color: #f2f2f2; font-family: 'Roboto', sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #000000; color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px 0;">
        <img src="https://funny-how.com/mail/logo.png" style="width: 110px;">
    </div>
    <div style="padding: 20px; text-align: left; font-size:18px;color:#fff;">
        <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Booking Placed</h1>
        <p>Dear {{ $user->firstname }},</p>
        <p>Your booking has been successfully placed, but is not yet paid. <br/>Please complete the payment to confirm your booking. <br/>Here are the details:</p>
        <p>
            <strong>Booking ID:</strong> {{ $booking->id }}<br>
            <strong>Date:</strong> {{ $booking->date }}<br>
            <strong>Start Time:</strong> {{ $booking->start_time }}<br>
            <strong>End Time:</strong> {{ $booking->end_time }}<br>
            <strong>Location:</strong> <a href="https://funny-how.com/{{ '@'.$booking->address->slug }}" style="color: #ffffff;">{{ $booking->address->street }}</a>
        </p>
    </div>
    <div style="padding: 10px; text-align: center;">
        <p><a href="{{ $paymentUrl }}" style="display: inline-block; width: 100%; height: 44px; background-color: #ffffff; color: #333333; border-radius: 10px; text-align: center; line-height: 44px; font-size: 14px; font-weight: 500; letter-spacing: 0.05em; text-decoration: none; transition: opacity 0.3s;" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=1">Pay ${{ $amount }}</a></p>
    </div>
    <div style="padding: 5px; text-align: center;  font-size:18px;color:#fff;">
        <p>Don't forget to pay in advance.</p>
        <p>Booking wil be cancelled in 20 minutes, <br/>if we won't receive your payment</p>
        <p>Thank you for using our service.</p>
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