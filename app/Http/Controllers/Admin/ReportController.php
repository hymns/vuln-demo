<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports', [
            'bookings' => Booking::with('room', 'user')->latest()->paginate(50),
        ]);
    }

    public function export()
    {
        $rows = Booking::with('user')->get()->map(fn (Booking $b) => [
            $b->reference, $b->guest_name, $b->guest_email, $b->user?->phone,
            $b->total_amount, $b->status, $b->guest_code,
        ]);

        $csv = $rows->map(fn ($r) => implode(',', $r))->prepend('reference,name,email,phone,total,status,code')->implode("\n");

        return response($csv, 200, ['Content-Type' => 'text/csv']);
    }
}
