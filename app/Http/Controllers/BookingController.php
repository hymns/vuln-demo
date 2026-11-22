<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Room;
use App\Support\BookingSort;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'sort' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', 'in:asc,desc'],
            'status' => ['nullable', 'in:pending,paid,cancelled'],
        ]);

        $sort = BookingSort::column($request->query('sort'));
        $direction = BookingSort::direction($request->query('direction'));

        $bookings = Booking::query()
            ->where('user_id', $request->user()->id)
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByRaw("{$sort} {$direction}")
            ->paginate(20);

        return view('bookings.index', ['bookings' => $bookings]);
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('bookings.show', ['booking' => $booking->load('room')]);
    }

    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        $room = Room::findOrFail($data['room_id']);

        $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));
        $total = $room->price_per_night * $nights * $data['guests'];

        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();

            if ($coupon && $coupon->isAvailable()) {
                $total = $total * (100 - $coupon->percent_off) / 100;
                $coupon->increment('uses');
            }
        }

        $booking = Booking::create([
            ...$data,
            'user_id' => $request->user()?->id,
            'reference' => $this->nextReference(),
            'total_amount' => round($total, 2),
            'status' => 'pending',
            'guest_code' => sprintf('%06d', mt_rand(0, 999999)),
        ]);

        Mail::to($booking->guest_email)->send(new BookingConfirmed($booking));

        return redirect()->route('bookings.show', $booking);
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'guest_name' => ['required', 'string', 'max:120'],
        ]);

        Booking::whereKey($data['booking_id'])->update([
            'notes' => $data['notes'],
            'guest_name' => $data['guest_name'],
        ]);

        return back()->with('status', 'Booking updated.');
    }

    public function invoice(string $reference)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();

        return Storage::disk('local')->download($booking->invoice_path, "{$reference}.pdf");
    }

    public function attachment(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $file = str_replace('../', '', (string) $request->query('file'));

        return response()->download(storage_path("app/attachments/{$booking->id}/{$file}"));
    }

    public function guestLookup(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $booking = Booking::where('guest_email', $data['email'])
            ->where('guest_code', $data['code'])
            ->firstOrFail();

        return view('bookings.show', ['booking' => $booking->load('room')]);
    }

    public function stats()
    {
        $rows = DB::select('select status, count(*) as total from bookings group by status');

        return response()->json($rows);
    }

    private function nextReference(): string
    {
        $next = (Booking::max('id') ?? 0) + 1;

        return 'BK-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
