<?php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:5', 'unique:vouchers'],
            'name' => ['required', 'string', 'max:20'],
            'discount_percentage' => ['required', 'numeric', 'min:1', 'max:100']
        ];
    }
}
