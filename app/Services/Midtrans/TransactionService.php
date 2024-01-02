<?php

namespace App\Services\Midtrans;

use Midtrans\Transaction;

class TransactionService extends Midtrans
{
    protected $orderId;

    public function __construct(string $orderId)
    {
        parent::__construct();
        
        $this->orderId = $orderId;
    }

    public function cancel()
    {
        try {
            $code = Transaction::cancel($this->orderId);
            return response(['message' => 'Transaction cancelled successfully'], $code);
        }
        
        catch (\Exception $e) {
             return response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function refund()
    {
        try {
            $code = Transaction::refund($this->orderId, null);
            return response(['message' => 'Transaction refunded successfully'], $code);
        }
        
        catch (\Exception $e) {
            return response(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
