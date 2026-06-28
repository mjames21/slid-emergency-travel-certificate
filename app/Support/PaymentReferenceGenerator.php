<?php

namespace App\Support;

class PaymentReferenceGenerator
{
    public function __construct(
        protected DailySequenceGenerator $sequenceGenerator
    ) {}

    public function generate(?string $date = null): string
    {
        return $this->sequenceGenerator->generate('ETC-PAY', $date);
    }
}
