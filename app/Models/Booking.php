<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'room_id', 'guest_name', 'guest_email',
        'check_in', 'check_out', 'guests', 'notes', 'coupon_code',
        'total_amount', 'status', 'guest_code',
    ];

    protected $hidden = ['guest_code'];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nights(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }
}
