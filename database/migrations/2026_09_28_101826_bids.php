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
            $table->string('phone', 20)->nullable();
            $table->date('game_date')->index();
            $table->enum('type', ['jodi', 'haruf', 'cross']);
            $table->string('number', 20);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'win', 'loss'])->default('pending');
            $table->decimal('winning_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'game_date']);
            $table->index(['game_id', 'game_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
