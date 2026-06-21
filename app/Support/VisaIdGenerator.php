<?php

namespace App\Support;

use App\Models\Airport;

class VisaIdGenerator
{
    public function __construct(
        protected DailyAirportSequenceGenerator $sequenceGenerator
    ) {
    }

    public function generate(Airport $airport, ?string $date = null): string
    {
        return $this->sequenceGenerator->generate('SVA', $airport, $date);
    }
}