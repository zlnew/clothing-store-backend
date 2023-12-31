<?php

namespace App\Http\Requests\Voucher;

use App\Models\Voucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:5', Rule::unique(Voucher::class, 'code')->ignore($this->id)],
            'name' => ['required', 'string', 'max:20'],
            'discount_percentage' => ['required', 'numeric', 'min:1', 'max:100']
        ];
    }
}
