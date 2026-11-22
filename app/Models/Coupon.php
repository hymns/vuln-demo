<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'percent_off', 'max_uses', 'uses'];

    public function isAvailable(): bool
    {
        return $this->uses < $this->max_uses;
    }
}
