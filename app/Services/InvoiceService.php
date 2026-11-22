<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class InvoiceService
{
    public function generate(Booking $booking): string
    {
        $booking->loadMissing('room');

        $html = view('invoices.pdf', ['booking' => $booking])->render();

        $pdf = new Mpdf(['tempDir' => storage_path('app/mpdf')]);
        $pdf->WriteHTML($html);

        $path = "invoices/{$booking->reference}.pdf";
        Storage::disk('local')->put($path, $pdf->Output('', 'S'));

        $booking->forceFill(['invoice_path' => $path])->save();

        return $path;
    }
}
