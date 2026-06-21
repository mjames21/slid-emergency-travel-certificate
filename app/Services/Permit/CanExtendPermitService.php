<?php
// FILE: app/Services/Permit/CanExtendPermitService.php

namespace App\Services\Permit;

use App\Models\Permit;

class CanExtendPermitService
{
    public function handle(Permit $permit): array
    {
        if ($permit->permit_status === 'revoked') {
            return ['allowed' => false, 'message' => 'Revoked permits cannot be extended.'];
        }

        if ($permit->permit_status === 'extended') {
            return ['allowed' => false, 'message' => 'This permit has already been extended.'];
        }

        if ($permit->permit_status === 'replaced') {
            return ['allowed' => false, 'message' => 'Replaced permits cannot be extended.'];
        }

        return ['allowed' => true, 'message' => null];
    }
}