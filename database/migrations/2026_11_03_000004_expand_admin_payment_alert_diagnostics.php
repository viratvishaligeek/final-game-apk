<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_payment_alerts', function (Blueprint $table) {
            $table->foreignId('wallet_request_id')->nullable()->change();
            $table->string('attempt_id', 120)->nullable()->after('wallet_request_id');
            $table->string('gateway_order_id', 120)->nullable()->after('transaction_id');
            $table->string('error_category', 80)->default('PAYMENT_FAILURE')->after('gateway');
            $table->string('error_code', 120)->nullable()->after('error_category');
            $table->unsignedSmallInteger('http_status')->nullable()->after('error_code');
            $table->string('processing_stage', 80)->nullable()->after('http_status');
            $table->string('diagnostic_reference', 80)->nullable()->after('processing_stage');
            $table->string('severity', 20)->default('high')->after('diagnostic_reference');
            $table->string('fingerprint', 64)->nullable()->index();
            $table->unsignedInteger('occurrence_count')->default(1);
            $table->timestamp('first_occurred_at')->nullable();
            $table->timestamp('latest_occurred_at')->nullable();
            $table->unique('attempt_id');
        });
    }

    public function down(): void
    {
        Schema::table('admin_payment_alerts', function (Blueprint $table) {
            $table->dropUnique(['attempt_id']);
            $table->dropIndex(['fingerprint']);
            $table->dropColumn([
                'attempt_id', 'gateway_order_id', 'error_category', 'error_code',
                'http_status', 'processing_stage', 'diagnostic_reference', 'severity',
                'fingerprint', 'occurrence_count', 'first_occurred_at', 'latest_occurred_at',
            ]);
        });
    }
};
