<?php

namespace App\Services\Midtrans;

use Midtrans\Snap;

class CreateSnapTokenService extends Midtrans
{
    protected $transaction;

    public function __construct($transaction)
    {
        parent::__construct();

        $this->transaction = $transaction;
    }

    public function getSnap()
    {
        $params = [
            'transaction_details' => [
                'order_id' => $this->transaction->order_id,
                'gross_amount' => $this->transaction->gross_amount,
            ],
            'item_details' => $this->transaction->item_details,
            'customer_details' => $this->transaction->customer_details,
        ];

        $snapToken = Snap::getSnapToken($params);
        $snapUrl = Snap::getSnapUrl($params);

        return [
            'snap_token' => $snapToken,
            'snap_url' => $snapUrl
        ];
    }
}
