<?php

namespace App\Models;

use App\Enums\ProductCategories;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'slug',
        'discount'
    ];

    protected $casts = [
      'category' => ProductCategories::class,
      'sizes' => 'array'
    ];

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null) { return false; }
                return $value;
            }
        );
    }


    public function getRouteKeyName()
    {
        return 'slug';
    }
}
