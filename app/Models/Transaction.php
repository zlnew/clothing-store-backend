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
        'promo_code_id',
        'total',
        'note',
        'status'
    ];

    protected $casts = [
        'status' => TransactionStatus::class
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function promoCode(): HasOne
    {
        return $this->hasOne(PromoCode::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
