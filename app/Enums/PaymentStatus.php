<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Reversed = 'reversed';
    case Refunded = 'refunded';
    case UnderReview = 'under_review';
}
