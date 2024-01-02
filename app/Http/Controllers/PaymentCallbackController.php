<?php
 
namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Midtrans\CallbackService;
use Illuminate\Support\Facades\DB;

class PaymentCallbackController extends Controller
{
    public function handling()
    {
        $callback = new CallbackService;
 
        if ($callback->isSignatureKeyVerified()) {
            $transaction = $callback->getTransaction();
 
            if ($callback->isSuccess()) {
                Transaction::query()
                    ->where('order_id', $transaction->order_id)
                    ->update(['status' => TransactionStatus::SETTLEMENT]);
            }

            if ($callback->isPending()) {
                Transaction::query()
                    ->where('order_id', $transaction->order_id)
                    ->update(['status' => TransactionStatus::PENDING]);
            }
 
            if ($callback->isExpire()) {
                DB::transaction(function () use ($transaction) {
                    $transactionDetail = TransactionDetail::query()
                        ->where('transaction_id', $transaction->id)
                        ->get();
                    
                    if ($transactionDetail && $transaction->status !== TransactionStatus::EXPIRED) {
                        foreach ($transactionDetail as $item) {
                            $product = (object)$item->product;

                            Product::query()
                                ->where('id', $product->id)
                                ->increment('stock', $item->quantity);
                        }
                    }
    
                    Transaction::query()
                        ->where('order_id', $transaction->order_id)
                        ->update(['status' => TransactionStatus::EXPIRED]);
                });
            }
 
            if ($callback->isCancelled()) {
                DB::transaction(function () use ($transaction) {
                    $transactionDetail = TransactionDetail::query()
                        ->where('transaction_id', $transaction->id)
                        ->get();
                    
                    if ($transactionDetail && $transaction->status !== TransactionStatus::CANCELLED) {
                        foreach ($transactionDetail as $item) {
                            $product = (object)$item->product;

                            Product::query()
                                ->where('id', $product->id)
                                ->increment('stock', $item->quantity);
                        }
                    }
                        
                    Transaction::query()
                        ->where('order_id', $transaction->order_id)
                        ->update(['status' => TransactionStatus::CANCELLED]);
                });
            }

            if ($callback->isRefunded()) {
                DB::transaction(function () use ($transaction) {
                    $transactionDetail = TransactionDetail::query()
                        ->where('transaction_id', $transaction->id)
                        ->get();
                    
                    if ($transactionDetail && $transaction->status !== TransactionStatus::REFUNDED) {
                        foreach ($transactionDetail as $item) {
                            $product = (object)$item->product;

                            Product::query()
                                ->where('id', $product->id)
                                ->increment('stock', $item->quantity);
                        }
                    }
                        
                    Transaction::query()
                        ->where('order_id', $transaction->order_id)
                        ->update(['status' => TransactionStatus::REFUNDED]);
                });
            }
 
            return response(['message' => 'Notification received']);
        }
        
        return response(['message' => "Signature key can't be verified"], 403);
    }
}

