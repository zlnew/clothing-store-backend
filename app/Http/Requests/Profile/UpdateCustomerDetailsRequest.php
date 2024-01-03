<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:15'],
            'address' => ['required', 'string'],
            'postal_code' => ['required', 'numeric']
        ];
    }
}
