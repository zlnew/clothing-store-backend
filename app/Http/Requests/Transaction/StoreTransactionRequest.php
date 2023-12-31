<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'promo_code' => ['nullable', 'string', 'exists:vouchers,code'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.price' => ['required', 'numeric'],
            'items.*.size' => ['required', 'string'],
            'items.*.product' => ['required', 'array'],
            'items.*.product.id' => ['required', 'numeric', 'exists:products'],
            'items.*.product.category' => ['required', 'in:all,men,women'],
            'items.*.product.name' => ['required', 'string'],
            'items.*.product.price' => ['required', 'numeric', 'min:1'],
            'items.*.product.sizes' => ['required', 'array'],
            'items.*.product.image' => ['required', 'string'],
            'items.*.product.description' => ['required', 'string'],
            'items.*.product.discount_percentage' => ['required', 'numeric']
        ];
    }
}
