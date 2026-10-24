<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Nullable to preserve all existing transaction records.
            $table->decimal('previous_balance', 15, 2)->nullable();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('idempotency_key', 36)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_idempotency_key_unique');
            $table->dropIndex(['admin_id']);
            $table->dropColumn(['previous_balance', 'admin_id', 'idempotency_key']);
        });
    }
};
