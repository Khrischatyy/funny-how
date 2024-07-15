<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
</head>
<body>
<h1>Booking Confirmation</h1>
<!-- <p>Dear {{ $booking->user->name }},</p>
<p>Your booking for the studio has been confirmed. Here are the details:</p>
<ul>
    <li>Studio: {{ $booking->address->name }}</li>
    <li>Date: {{ $booking->date }}</li>
    <li>Start Time: {{ $booking->start_time }}</li>
    <li>End Time: {{ $booking->end_time }}</li>
    <li>Total Cost: {{ $booking->charge->amount }}</li>
</ul> -->
<p>{{$booking->user}}</p>
<!-- {"id":3,"firstname":"Ruslan","lastname":"Shadaev","email":"rushadaev@gmail.com","phone":null,"profile_photo":"https:\/\/lh3.googleusercontent.com\/a\/ACg8ocKAhAWZfkzceVRXxDMXwgP2KvnTIjSasz-7c8fe8T_exwIcsb7O=s96-c","username":null,"date_of_birth":null,"email_verified_at":null,"created_at":"2024-07-15T16:31:07.000000Z","updated_at":"2024-07-15T16:32:41.000000Z","two_factor_secret":null,"two_factor_recovery_codes":null,"stripe_id":"cus_QTpUBiYwMshONx","pm_type":null,"pm_last_four":null,"trial_ends_at":null,"google_id":"113028171119841162730"} -->
<p>Thank you for booking with us!</p>
</body>
</html>