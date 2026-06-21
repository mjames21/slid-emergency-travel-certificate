<?php
// FILE: app/Contracts/MrzExtractor.php

namespace App\Contracts;

interface MrzExtractor
{
    public function extract(string $absoluteImagePath): array;
}