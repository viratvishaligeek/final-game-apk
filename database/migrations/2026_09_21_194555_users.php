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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('city', 200)->nullable();
            $table->string('gender', 20)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('account_holder_name', 100)->nullable();
            $table->string('phonepe', 100)->nullable();
            $table->string('gpay', 100)->nullable();
            $table->string('paytm', 100)->nullable();
            $table->text('device_token')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
