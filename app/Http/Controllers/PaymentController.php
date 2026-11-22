<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Server-to-server callback from the payment gateway. Excluded from CSRF
     * because the gateway can't send a token; authenticity comes from the signature.
     */
    public function callback(Request $request, InvoiceService $invoices)
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'status' => ['required', 'string'],
            'signature' => ['required', 'string'],
        ]);

        $expected = hash_hmac(
            'sha256',
            $data['reference'] . '|' . $data['amount'] . '|' . $data['status'],
            config('services.gateway.secret'),
        );

        abort_unless(hash_equals($expected, $data['signature']), 403);

        $booking = Booking::where('reference', $data['reference'])->firstOrFail();

        if ($data['status'] === 'success') {
            $booking->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
            $invoices->generate($booking);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Where the gateway sends the customer's browser afterwards.
     */
    public function return(Request $request)
    {
        $next = (string) $request->query('next', '/bookings');

        if (Str::startsWith($next, ['/', config('app.url')])) {
            return redirect()->to($next);
        }

        return redirect('/bookings');
    }
}
