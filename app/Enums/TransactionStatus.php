<?php
namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case SETTLEMENT = 'settlement';
    case ON_PROCESS = 'on_process';
    case ON_DELIVERY = 'on_delivery';
    case FINISHED = 'finished';
}
?>
