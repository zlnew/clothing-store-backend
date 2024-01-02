<?php

namespace App\Http\Requests\CartItem;

use App\Rules\MaxProductQuantityAvailable;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'numeric',
                'min:1',
                new MaxProductQuantityAvailable($this->get('product_id'))
            ]
        ];
    }
}
