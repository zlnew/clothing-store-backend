<?php

namespace App\Models;

use App\Enums\PromoCodeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_percentage',
        'status'
    ];

    protected $casts = [
        'status' => PromoCodeStatus::class
    ];
}
