<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Voucher;
use App\Services\Midtrans\CreateSnapTokenService;
use App\Services\Midtrans\TransactionService;
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
            $this->transaction->where(function ($query) {
                $query->where('status', TransactionStatus::CREATED)
                    ->orWhere('status', TransactionStatus::PENDING)
                    ->orWhere('status', TransactionStatus::SETTLEMENT)
                    ->orWhere('status', TransactionStatus::ON_PROCESS)
                    ->orWhere('status', TransactionStatus::ON_PROCESS);
            });
        }

        if (isset($request->status) && $request->status === 'cancelled') {
            $this->transaction->where(function ($query) {
                $query->where('status', TransactionStatus::CANCELLED)
                    ->orWhere('status', TransactionStatus::EXPIRED)
                    ->orWhere('status', TransactionStatus::REFUNDED);
            });
        }

        if (isset($request->status) && $request->status === 'finished') {
            $this->transaction->where('status', TransactionStatus::FINISHED);
        }

        $this->transaction = $this->transaction->latest()->get();

        return new TransactionResource($this->transaction);
    }

    public function store(StoreTransactionRequest $request)
    {
        try {
            DB::beginTransaction();

            $request->check();

            $validated = $request->safe();
            $cartItems = CartItem::query()->where('user_id', $request->user()->id);
            $voucherId = null;
            $totalDiscountAmount = 0;
    
            if (isset($validated->voucher_code)) {
                $voucher = Voucher::query()
                    ->where('code', $validated->voucher_code)
                    ->first();

                $voucherId = $voucher->id;
            }
    
            $subtotal = collect($cartItems->get())->map(function ($item) {
                $quantity = $item->quantity;
                $productPrice = $item->product->price;
                $productDiscountPercentage = $item->product->discount_percentage;
                $productSubtotal = $productPrice * $quantity;
                $discountAmount = ($productDiscountPercentage / 100) * $productSubtotal;
                $finalPrice = $productSubtotal - $discountAmount;
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

            foreach ($cartItems->get() as $item) {
                $quantity = $item->quantity;
                $productPrice = $item->product->price;
                $productDiscountPercentage = $item->product->discount_percentage;
                $productSubtotal = $productPrice * $quantity;
                $discountAmount = ($productDiscountPercentage / 100) * $productSubtotal;
                $finalPrice = $productSubtotal - $discountAmount;
                
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
                return [
                    'id' => $item->id,
                    'price' => $item->price / $item->quantity,
                    'quantity' => $item->quantity,
                    'name' => $item->name.' - '.$item->size
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
            $cartItems->delete();

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
        $validated = $request->safe();
        $transactionService = new TransactionService($transaction->order_id);

        switch ($validated->action) {
            case 'cancel':
                if ($transaction->status === TransactionStatus::PENDING) {
                    return $transactionService->cancel();
                }
                
                if ($transaction->status === TransactionStatus::CREATED) {
                    DB::transaction(function () use ($transaction) {
                        $transactionDetails = TransactionDetail::query()
                            ->where('transaction_id', $transaction->id);
    
                        foreach ($transactionDetails->get() as $item) {
                            $product = (object)$item->product;
    
                            Product::query()
                                ->where('id', $product->id)
                                ->increment('stock', $item->quantity);
                        }
    
                        $transactionDetails->delete();
                        $transaction->delete();
                    });

                    return response(['message' => 'Transaction cancelled successfully'], 200);
                }
                
                break;

            case 'refund':
                return $transactionService->refund();
                break;
            
            default:
                break;
        }

        return response([
            'message' => 'Error occurred while updating transaction'
        ], 500);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response([
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
