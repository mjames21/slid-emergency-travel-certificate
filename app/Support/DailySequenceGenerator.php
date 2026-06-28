<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class DailySequenceGenerator
{
    public function generate(string $prefix, ?string $date = null): string
    {
        $date = $date ?: now()->format('Ymd');
        $base = sprintf('%s-%s-', strtoupper($prefix), $date);

        return DB::transaction(function () use ($base) {
            $lastValue = DB::table('daily_sequences')
                ->where('sequence_key', $base)
                ->lockForUpdate()
                ->value('current_number');

            $nextNumber = $lastValue ? ((int) $lastValue + 1) : 1;

            if ($nextNumber > 9000) {
                throw new RuntimeException('Daily ETC sequence limit of 9000 reached.');
            }

            DB::table('daily_sequences')->updateOrInsert(
                ['sequence_key' => $base],
                [
                    'current_number' => $nextNumber,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return $base.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
