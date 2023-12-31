<?php

namespace App\Models;

use App\Enums\VoucherStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_percentage',
        'status'
    ];

    protected $casts = [
        'discount_percentage' => 'integer',
        'status' => VoucherStatus::class
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }
}
