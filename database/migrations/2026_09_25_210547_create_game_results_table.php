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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('number', 10)->default('Wait');
            $table->date('number_date');
            $table->enum('type', ['ah', 'bh', 'jodi']);
            $table->timestamps();
            $table->index(['game_id', 'number_date', 'type']);
            $table->unique(['game_id', 'number_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
