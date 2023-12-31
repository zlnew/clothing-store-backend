<?php

namespace App\Models;

use App\Enums\ProductCategories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'price',
        'sizes',
        'image',
        'description',
        'discount_percentage',
        'slug',
    ];

    protected $casts = [
      'category' => ProductCategories::class,
      'price' => 'double',
      'sizes' => 'array',
      'discount_percentage' => 'integer'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
