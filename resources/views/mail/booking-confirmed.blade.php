<p>Hi {{ $booking->guest_name }},</p>
<p>We've received your booking <strong>{{ $booking->reference }}</strong>.</p>
<p>To view it without signing in, use your email and this code: <strong>{{ $booking->guest_code }}</strong></p>
