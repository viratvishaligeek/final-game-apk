<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bid_id')->unique()->constrained('bids')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('number', 20);
            $table->string('type', 20);
            $table->date('game_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('winning_amount', 15, 2);
            $table->timestamps();
            $table->index(['game_id', 'game_date', 'type']);
            $table->index(['user_id', 'game_date']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
