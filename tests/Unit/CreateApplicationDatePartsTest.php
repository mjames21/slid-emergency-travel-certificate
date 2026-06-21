<?php

namespace Tests\Unit;

use App\Livewire\Staff\Applications\Create;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class CreateApplicationDatePartsTest extends TestCase
{
    #[Test]
    public function it_allows_zero_padded_date_parts_and_formats_dates(): void
    {
        $component = new Create();

        $component->passport_expiry_year = '2029';
        $component->passport_expiry_month = '03';
        $component->passport_expiry_day = '12';
        $component->date_of_birth_year = '1986';
        $component->date_of_birth_month = '04';
        $component->date_of_birth_day = '21';

        $rules = array_intersect_key($this->invokeProtected($component, 'rules'), array_flip([
            'passport_expiry_year',
            'passport_expiry_month',
            'passport_expiry_day',
            'date_of_birth_year',
            'date_of_birth_month',
            'date_of_birth_day',
        ]));

        $validator = Validator::make([
            'passport_expiry_year' => $component->passport_expiry_year,
            'passport_expiry_month' => $component->passport_expiry_month,
            'passport_expiry_day' => $component->passport_expiry_day,
            'date_of_birth_year' => $component->date_of_birth_year,
            'date_of_birth_month' => $component->date_of_birth_month,
            'date_of_birth_day' => $component->date_of_birth_day,
        ], $rules);

        $this->assertFalse($validator->fails(), $validator->errors()->toJson());
        $this->assertSame('03', $component->passport_expiry_month);
        $this->assertSame('04', $component->date_of_birth_month);
        $this->assertSame('2029-03-12', $this->invokeProtected($component, 'passportExpiryDate'));
        $this->assertSame('1986-04-21', $this->invokeProtected($component, 'dateOfBirth'));
    }

    private function invokeProtected(Create $component, string $method): mixed
    {
        $reflection = new ReflectionMethod($component, $method);

        return $reflection->invoke($component);
    }
}
