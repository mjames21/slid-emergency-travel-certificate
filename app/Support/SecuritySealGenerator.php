<?php

namespace App\Support;

class SecuritySealGenerator
{
    public function generate(array $data): string
    {
        ksort($data);

        return hash_hmac('sha256', json_encode($data, JSON_UNESCAPED_SLASHES), config('app.key'));
    }
}
