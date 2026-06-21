<?php

namespace App\Enums;

enum PermitStatus: string
{
    case Generated = 'generated';
    case Issued = 'issued';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Revoked = 'revoked';
    case Superseded = 'superseded';
}
