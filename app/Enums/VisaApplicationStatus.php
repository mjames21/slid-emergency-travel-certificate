<?php

namespace App\Enums;

enum VisaApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case AwaitingPayment = 'awaiting_payment';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case PermitReady = 'permit_ready';
    case PermitIssued = 'permit_issued';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Revoked = 'revoked';
}
