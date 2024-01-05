<?php

namespace App\Http\Requests\CartItem;

use App\Rules\MaxProductQuantityAvailable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreCartItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['required', 'string'],
            'quantity' => [
                'required',
                'numeric',
                'min:1',
                new MaxProductQuantityAvailable($this->input('product_id'))
            ]
        ];
    }

    public function check()
    {
        if (!auth()->check()) {
            throw ValidationException::withMessages([
                'status' => 'You must login first to add items to the cart'
            ]);
        }
    }
}
