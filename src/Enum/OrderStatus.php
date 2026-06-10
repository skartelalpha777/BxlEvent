<?php

namespace App\Enum;

enum OrderStatus: string
{

    case Paid = 'paid';
    case Pending = 'pending';
    case Cancelled = 'cancelled';
}
