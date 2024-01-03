<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateAccountRequest;
use App\Http\Requests\Profile\UpdateCustomerDetailsRequest;
use App\Models\CustomerDetail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    protected $profile;

    public function __construct(User $profile)
    {
        $this->profile = $profile;
    }

    public function updateAccount(UpdateAccountRequest $request)
    {
        $user = $request->user();
        $validated = $request->safe();

        $this->profile->query()
            ->where('id', $user->id)
            ->update($validated->all());

        return response(['status' => 'Account successfully updated']);
    }

    public function updateCustomerDetails(UpdateCustomerDetailsRequest $request)
    {
        $user = $request->user();
        $validated = $request->safe();

        $customerDetails = new CustomerDetail($validated->all());

        $this->profile->query()
            ->where('id', $user->id)->first()
            ->customerDetails()->save($customerDetails);

        return response(['status' => 'Customer Details successfully updated']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        $validated = $request->safe();

        $this->profile->query()
            ->where('id', $user->id)
            ->update(['password' => Hash::make($validated->password)]);

        return response(['status' => 'Password successfully changed']);
    }
}
