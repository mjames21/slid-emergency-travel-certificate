<?php

namespace App\Enums;

enum PermitVerificationResult: string
{
    case Valid = 'valid';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Revoked = 'revoked';
    case Invalid = 'invalid';
    case NotFound = 'not_found';
}
