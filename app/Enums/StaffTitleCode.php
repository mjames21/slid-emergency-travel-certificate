<?php

namespace App\Enums;

enum StaffTitleCode: string
{
    case SystemAdministrator = 'system_administrator';
    case EtcIssuer = 'etc_issuer';
    case ExecutiveObserver = 'executive_observer';
}
