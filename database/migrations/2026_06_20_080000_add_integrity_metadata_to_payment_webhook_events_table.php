<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_webhook_events', 'request_id')) {
                $table->string('request_id', 100)->nullable()->after('event_id')->index();
            }

            if (! Schema::hasColumn('payment_webhook_events', 'source_ip')) {
                $table->string('source_ip', 64)->nullable()->after('request_id');
            }

            if (! Schema::hasColumn('payment_webhook_events', 'payload_sha256')) {
                $table->char('payload_sha256', 64)->nullable()->after('payload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            foreach (['request_id', 'source_ip', 'payload_sha256'] as $column) {
                if (Schema::hasColumn('payment_webhook_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
