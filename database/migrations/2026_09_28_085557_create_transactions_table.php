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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->string('subject', 255);
            $table->enum('type', ['credit', 'debit']);
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('completed');
            $table->timestamps();
            $table->index(['user_id', 'type', 'created_at']);
            $table->foreignId('game_id')->nullable()->constrained('games')->nullOnDelete();
            $table->foreignId('bid_id')->nullable()->constrained('bids')->nullOnDelete();
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
