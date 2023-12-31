<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category' => ['required', 'in:all,men,women'],
            'name' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:1'],
            'sizes' => ['required', 'json'],
            'image' => ['nullable', Rule::imageFile()->types('jpg')],
            'description' => ['required', 'string'],
        ];
    }
}
