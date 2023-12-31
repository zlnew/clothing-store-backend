<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Voucher;
use App\Services\Midtrans\CreateSnapTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Ulid;

class TransactionController extends Controller
{
    protected $transaction;

    public function __construct(Transaction $transaction) {
        $this->transaction = $transaction;
    }
    
    public function index(Request $request)
    {
        $this->transaction = $this->transaction->query()
            ->where('user_id', $request->user()->id);

        if (isset($request->status) && $request->status === 'active') {
            $this->transaction = $this->transaction
                ->where('status', TransactionStatus::PENDING)
                ->orWhere('status', TransactionStatus::SETTLEMENT)
                ->orWhere('status', TransactionStatus::ON_PROCESS)
                ->orWhere('status', TransactionStatus::ON_PROCESS);
        }

        if (isset($request->status) && $request->status === 'cancelled') {
            $this->transaction = $this->transaction
                ->where('status', TransactionStatus::CANCELLED)
                ->orWhere('status', TransactionStatus::EXPIRED)
                ->orWhere('status', TransactionStatus::REFUNDED);
        }

        if (isset($request->status) && $request->status === 'finished') {
            $this->transaction = $this->transaction
                ->where('status', TransactionStatus::FINISHED);
        }

        $this->transaction = $this->transaction
            ->latest()
            ->get();

        return new TransactionResource($this->transaction);
    }

    public function store(StoreTransactionRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->safe();
            $voucherId = null;
            $totalDiscountAmount = 0;
    
            if (isset($validated->promo_code)) {
                $voucher = Voucher::query()
                    ->where('code', $validated->promo_code)
                    ->first();

                $voucherId = $voucher->id;
            }
    
            $subtotal = collect($validated->items)->map(function ($item) {
                $item = (object)$item;
                $item->product = (object)$item->product;

                $price = $item->product->price;
                $quantity = $item->quantity;
                $discountPercentage = $item->product->discount_percentage;
                $priceAmount = $price * $quantity;
                $discountAmount = ($discountPercentage / 100) * $priceAmount;
                $finalPrice = $priceAmount - $discountAmount;
    
                return $finalPrice;
            });

            if (isset($voucherId)) {
                $totalDiscountAmount = ($voucher->discount_percentage / 100) * $subtotal->sum();
            }

            $grossAmount = $subtotal->sum() - $totalDiscountAmount;
    
            $this->transaction->fill([
                'order_id' => Ulid::generate(),
                'user_id' => $request->user()->id,
                'voucher_id' => $voucherId,
                'gross_amount' => $grossAmount,
                'note' => $validated->note
            ])->save();

            foreach ($validated->items as $item) {
                $item = (object)$item;
                $item->product = (object)$item->product;
                
                $price = $item->product->price;
                $quantity = $item->quantity;
                $discountPercentage = $item->product->discount_percentage;
                $priceAmount = $price * $quantity;
                $discountAmount = ($discountPercentage / 100) * $priceAmount;
                $finalPrice = $priceAmount - $discountAmount;
                
                $detail = new TransactionDetail([
                    'transaction_id' => $this->transaction->id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $finalPrice,
                    'size' => $item->size,
                    'product' => $item->product
                ]);
                
                Product::query()
                    ->where('id', $item->product->id)
                    ->decrement('stock', $item->quantity);
    
                $this->transaction->details()->save($detail);
            }

            $itemDetails = collect($this->transaction->details)->map(function ($item) {
                $item = (object)$item;
                $price = $item->price / $item->quantity;
             
                return [
                    'id' => $item->id,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'name' => $item->name
                ];
            });

            if (isset($voucherId)) {
                $itemDetails = $itemDetails->push([
                    'id' => $voucher->code,
                    'price' => -$totalDiscountAmount,
                    'quantity' => 1,
                    'name' => $voucher->name
                ]);
            }

            $customerDetails = [
                'first_name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $request->user()->customerDetails->phone_number,
            ];
    
            $transactionParams = (object)[
                'order_id' => $this->transaction->order_id,
                'gross_amount' => $this->transaction->gross_amount,
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails
            ];
            
            $midtrans = new CreateSnapTokenService($transactionParams);
            $snap = $midtrans->getSnap();

            $this->transaction->fill($snap)->save();

            DB::commit();
    
            return response($snap);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, Transaction $transaction)
    {
        $transaction = $transaction->query()
            ->where('user_id', $request->user()->id)
            ->get();

        return new TransactionResource($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response([
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
