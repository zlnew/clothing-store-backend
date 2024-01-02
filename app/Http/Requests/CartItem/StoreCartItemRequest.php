<?php

namespace App\Http\Requests\CartItem;

use App\Rules\MaxProductQuantityAvailable;
use Illuminate\Foundation\Http\FormRequest;

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
}
