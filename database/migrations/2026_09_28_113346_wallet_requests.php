<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('request_type', ['credit', 'debit']);
            $table->string('payment_method', 30);
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('pending');
            $table->string('utr', 100)->nullable();
            $table->string('client_txn_id', 100)->nullable()->unique();
            $table->string('gateway_txn_id', 100)->nullable();
            $table->string('customer_vpa', 150)->nullable();
            $table->string('gateway_status', 50)->nullable();
            $table->string('screenshot', 500)->nullable();
            $table->string('account_name', 150)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->string('upi_id', 150)->nullable();
            $table->string('qr_code_image', 500)->nullable();
            $table->text('remark')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_remark')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'request_type']);
            $table->index(['user_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index('utr');
            $table->index('gateway_txn_id');
            $table->index(
                ['request_type', 'status', 'processed_at'],
                'wallet_requests_dashboard_chart_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
