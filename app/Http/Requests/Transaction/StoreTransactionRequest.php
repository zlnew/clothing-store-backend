<?php

namespace App\Http\Requests\Transaction;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'voucher_code' => ['nullable', 'string', 'exists:vouchers,code'],
            'note' => ['nullable', 'string']
        ];
    }

    public function check(): void
    {
        $user = $this->user();

        $cartItemsCount = CartItem::query()->where('user_id', $user->id)->count();

        if ($cartItemsCount < 1) {
            throw ValidationException::withMessages([
                'checkout' => 'You must have at least one item in your cart'
            ]);
        }

        if (!isset($user->customerDetails)) {
            throw ValidationException::withMessages([
                'checkout' => 'You must fill your customer details before making a checkout'
            ]);
        }
    }
}
