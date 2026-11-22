<html>
<body style="font-family: sans-serif;">
    <h1>Invoice {{ $booking->reference }}</h1>
    <p>{{ $booking->guest_name }} &middot; {{ $booking->guest_email }}</p>
    <table width="100%" border="1" cellpadding="6">
        <tr><td>{{ $booking->room->name }} &times; {{ $booking->nights() }} night(s), {{ $booking->guests }} guest(s)</td>
            <td align="right">RM {{ number_format($booking->total_amount, 2) }}</td></tr>
    </table>
    @if ($booking->notes)
        <h3>Special requests</h3>
        {!! Str::markdown($booking->notes) !!}
    @endif
</body>
</html>
