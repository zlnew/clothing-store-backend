<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:cancel,refund']
        ];
    }
}
