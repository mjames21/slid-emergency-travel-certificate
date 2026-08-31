<?php

namespace Tests\Feature;

use App\Models\Permit;
use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermitPrintAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function certificate_prints_are_attributed_and_repeat_prints_are_marked(): void
    {
        Storage::fake('local');

        $issuer = $this->issuer();
        $permit = Permit::factory()->create([
            'print_count' => 0,
            'last_printed_at' => null,
        ]);

        $this->actingAs($issuer)
            ->withHeaders([
                'X-Terminal-Name' => 'ETC-DESK-01',
                'X-Printer-Name' => 'SECURE-PRINTER-01',
            ])
            ->get(route('documents.certificates.show', $permit))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($issuer)
            ->get(route('documents.certificates.show', $permit))
            ->assertOk();

        $permit->refresh();

        $this->assertSame(2, $permit->print_count);
        $this->assertTrue($permit->is_duplicate_print);
        $this->assertDatabaseHas('permit_print_logs', [
            'permit_id' => $permit->id,
            'printed_by' => $issuer->id,
            'terminal_name' => 'ETC-DESK-01',
            'printer_name' => 'SECURE-PRINTER-01',
            'is_reprint' => false,
        ]);
        $this->assertDatabaseHas('permit_print_logs', [
            'permit_id' => $permit->id,
            'printed_by' => $issuer->id,
            'is_reprint' => true,
        ]);
    }

    private function issuer(): User
    {
        $title = StaffTitle::query()->create([
            'name' => 'ETC Issuer',
            'code' => 'etc_issuer',
            'active' => true,
        ]);
        $user = User::factory()->create();
        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        return $user->fresh(['staffTitles']);
    }
}
