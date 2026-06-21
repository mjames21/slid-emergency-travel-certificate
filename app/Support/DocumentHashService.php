<?php

namespace App\Support;

class DocumentHashService
{
    public function generate(string $contents): string
    {
        return hash('sha256', $contents);
    }
}
