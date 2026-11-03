<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_payment_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_request_id')->unique()->constrained('wallet_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_id', 120)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('gateway', 80);
            $table->text('failure_reason')->nullable();
            $table->timestamp('failed_at');
            $table->timestamps();
            $table->index('failed_at');
        });

        Schema::create('admin_payment_alert_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_payment_alert_id')->constrained('admin_payment_alerts')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(['admin_payment_alert_id', 'admin_id'], 'admin_payment_alert_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_payment_alert_reads');
        Schema::dropIfExists('admin_payment_alerts');
    }
};
