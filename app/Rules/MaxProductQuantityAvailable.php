<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxProductQuantityAvailable implements ValidationRule
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $product = Product::findOrFail($this->id);

        if ($value > $product->stock) {
            $fail('The selected :attribute is not available in stock for the specified product');
        }
    }
}
