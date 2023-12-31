<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category' => ['required', 'in:all,men,women'],
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'sizes' => ['required', 'json'],
            'image' => ['required', Rule::imageFile()->types('jpg')],
            'description' => ['required', 'string'],
            'discount' => ['required', 'numeric', 'min:1', 'max:100'],
        ];
    }
}
