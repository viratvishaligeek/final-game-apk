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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->bigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('subject');
            $table->decimal('balance', 15, 2);
            $table->enum('status', ['pending','rejected','completed']);
            $table->enum('type', ['debit', 'credit']);
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
