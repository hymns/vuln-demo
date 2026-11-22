@extends('layouts.app')

@section('title', "Booking {$booking->reference}")

@section('content')
    <h1>Booking {{ $booking->reference }}</h1>

    <dl>
        <dt>Room</dt><dd>{{ $booking->room->name }}</dd>
        <dt>Guest</dt><dd>{{ $booking->guest_name }} &lt;{{ $booking->guest_email }}&gt;</dd>
        <dt>Dates</dt><dd>{{ $booking->check_in->toFormattedDateString() }} – {{ $booking->check_out->toFormattedDateString() }}</dd>
        <dt>Total</dt><dd>RM {{ number_format($booking->total_amount, 2) }}</dd>
        <dt>Status</dt><dd>{{ ucfirst($booking->status) }}</dd>
    </dl>

    @if ($booking->notes)
        <section class="notes">
            <h2>Special requests</h2>
            {!! Str::markdown($booking->notes) !!}
        </section>
    @endif

    @if ($booking->invoice_path)
        <a href="{{ route('invoices.show', $booking->reference) }}">Download invoice</a>
    @endif

    @can('update', $booking)
        <form method="post" action="{{ route('bookings.update', $booking) }}">
            @csrf
            @method('put')
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            <label>Guest name <input name="guest_name" value="{{ $booking->guest_name }}"></label>
            <label>Special requests <textarea name="notes">{{ $booking->notes }}</textarea></label>
            <button>Save</button>
        </form>
    @endcan
@endsection
