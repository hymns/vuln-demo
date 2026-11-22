@extends('layouts.app')

@section('content')
    <h1>All bookings</h1>
    <a href="{{ route('admin.reports.export') }}">Export CSV</a>
    <ul>
        @foreach ($bookings as $booking)
            <li>{{ $booking->reference }} — {{ $booking->guest_name }} — {{ $booking->status }}</li>
        @endforeach
    </ul>
    {{ $bookings->links() }}
@endsection
