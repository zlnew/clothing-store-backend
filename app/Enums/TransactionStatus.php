<?php
namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = '100';
    case PAID = '200';
    case FINISHED = '300';
}
?>
