<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'quantity',
        'size',
        'product_id',
        'product_category',
        'product_name',
        'product_price',
        'product_sizes',
        'product_image'
    ];

    protected $casts = [
        'product_sizes' => 'array'
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
