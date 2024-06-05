<?php

namespace App\Enums;

enum PaymentStatusEnum: int
{
    case PENDING = 1;
    case PAID = 2;
    case CANCELLED = 3;
    case REFUNDED = 4;
}
