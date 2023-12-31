<?php

namespace App\Http\Controllers;

use App\Enums\VoucherStatus;
use App\Http\Requests\Voucher\StoreVoucherRequest;
use App\Http\Requests\Voucher\UpdateVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    protected $voucher;

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }

    public function index(Request $request)
    {
        if (isset($request->active) && $request->active == 'true') {
            $this->voucher = $this->voucher
                ->where('status', VoucherStatus::ACTIVE)
                ->first();
        } else {
            $this->voucher = $this->voucher->all();
        }

        return new VoucherResource($this->voucher);
    }

    public function store(StoreVoucherRequest $request)
    {
        $this->voucher->fill($request->safe());
        $this->voucher->save();

        return response([
            'data' => new VoucherResource($this->voucher),
            'message' => 'Voucher created successfully'
        ]);
    }

    public function show(Voucher $voucher)
    {
        return new VoucherResource($voucher);
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $voucher->fill($request->safe());
        $voucher->save();

        return response([
            'data' => new VoucherResource($voucher),
            'message' => 'Voucher updated successfully'
        ]);
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return response([
            'message' => 'Voucher deleted successfully'
        ]);
    }
}
