@extends('layouts.app')

@section('title', 'My bookings')

@section('content')
    <h1>My bookings</h1>
    <table>
        <thead>
            <tr>
                <th><a href="?sort=reference">Reference</a></th>
                <th><a href="?sort=check_in">Check-in</a></th>
                <th><a href="?sort=check_out">Check-out</a></th>
                <th><a href="?sort=total_amount">Total</a></th>
                <th><a href="?sort=status">Status</a></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td><a href="{{ route('bookings.show', $booking) }}">{{ $booking->reference }}</a></td>
                    <td>{{ $booking->check_in->toDateString() }}</td>
                    <td>{{ $booking->check_out->toDateString() }}</td>
                    <td>RM {{ number_format($booking->total_amount, 2) }}</td>
                    <td>{{ ucfirst($booking->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $bookings->withQueryString()->links() }}
@endsection
