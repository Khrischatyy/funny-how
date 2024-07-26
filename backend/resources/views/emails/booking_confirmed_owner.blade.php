<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmed</title>
</head>
<body style="background-color: #f2f2f2; font-family: 'Roboto', sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #000000; color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px 0;">
        <img src="https://funny-how.com/mail/logo.png" style="width: 110px;">
    </div>
    <div style="padding: 20px; text-align: left; font-size:18px;color:#fff;">
        <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 20px;">Booking Confirmed</h1>
        <p>Dear {{ $studioOwner->firstname }},</p>
        <p>Somebody has booked a studio. <br/>Here are the details:</p>
        <p>
           
            <strong>Name:</strong> {{ $user->firstname }}<br>
            <strong>Contacts:</strong> 
            {{ implode(' / ', array_filter([$user->email, $user->phone])) }}
            <br>

            <br/><br/>
            <strong>Booking ID:</strong> {{ $booking->id }}<br>
            <strong>Date:</strong> {{ $booking->date }}<br>
            <strong>Start Time:</strong> {{ $booking->start_time }}<br>
            <strong>End Time:</strong> {{ $booking->end_time }}<br>
            <strong>Location:</strong> <a href="https://funny-how.com/{{ '@'.$booking->address->slug }}" style="color: #ffffff;">{{ $booking->address->street }}</a>
        </p>
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