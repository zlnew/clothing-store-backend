<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'voucher_id',
        'gross_amount',
        'note',
        'snap_token',
        'snap_url',
        'status'
    ];

    protected $casts = [
        'gross_amount' => 'double',
        'status' => TransactionStatus::class
    ];

    protected $with = ['details'];

    public function getRouteKeyName()
    {
        return 'order_id';
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
