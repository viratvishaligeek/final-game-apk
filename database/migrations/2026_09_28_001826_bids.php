<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 100)->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->date('game_date');
            $table->enum('type', ['jodi', 'haruf', 'cross']);
            $table->string('number', 20);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'win', 'loss'])->default('pending');
            $table->decimal('winning_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->index(['game_id', 'game_date', 'status']);
            $table->index(['user_id', 'game_date']);
            $table->index(['game_id', 'game_date', 'type', 'number', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
