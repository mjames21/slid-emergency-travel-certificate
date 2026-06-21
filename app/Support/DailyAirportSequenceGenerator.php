<?php

namespace App\Support;

use App\Models\Airport;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DailyAirportSequenceGenerator
{
    public function generate(string $prefix, Airport $airport, ?string $date = null): string
    {
        $date = $date ?: now()->format('Ymd');
        $airportCode = strtoupper($airport->code);
        $base = sprintf('%s-%s-%s-', strtoupper($prefix), $airportCode, $date);

        return DB::transaction(function () use ($base) {
            $lastValue = DB::table('daily_sequences')
                ->where('sequence_key', $base)
                ->lockForUpdate()
                ->value('current_number');

            $nextNumber = $lastValue ? ((int) $lastValue + 1) : 1;

            if ($nextNumber > 9000) {
                throw new RuntimeException('Daily limit of 9000 reached for this airport.');
            }

            DB::table('daily_sequences')->updateOrInsert(
                ['sequence_key' => $base],
                [
                    'current_number' => $nextNumber,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return $base . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}