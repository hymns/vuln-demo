<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'guest_name' => ['required', 'string', 'max:120'],
            'guest_email' => ['required', 'email', 'max:190'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'max:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
