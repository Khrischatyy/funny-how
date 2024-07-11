<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
</head>
<body>
<h1>Booking Confirmation</h1>
<p>Dear {{ $booking->user->name }},</p>
<p>Your booking for the studio has been confirmed. Here are the details:</p>
<ul>
    <li>Studio: {{ $booking->address->name }}</li>
    <li>Date: {{ $booking->date }}</li>
    <li>Start Time: {{ $booking->start_time }}</li>
    <li>End Time: {{ $booking->end_time }}</li>
    <li>Total Cost: {{ $booking->charge->amount }}</li>
</ul>
<p>Thank you for booking with us!</p>
</body>
</html>