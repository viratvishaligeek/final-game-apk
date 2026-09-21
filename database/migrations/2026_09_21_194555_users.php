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
            $table->string('phone', 20);
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('city', 200)->nullable();
            $table->string('gender', 100)->nullable();
            $table->integer('balance')->default(0);
            $table->string('bank', 100)->nullable();
            $table->string('acc', 100)->nullable();
            $table->string('ifsc', 100)->nullable();
            $table->string('holdername', 100)->nullable();
            $table->string('phonepe', 100)->nullable();
            $table->string('gpay', 20)->nullable();
            $table->string('paytm', 20)->nullable();
            $table->string('token', 10000)->nullable();
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
