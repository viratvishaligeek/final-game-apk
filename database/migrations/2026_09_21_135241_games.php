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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->time('result_time');
            $table->time('play_start');
            $table->time('play_end');
            $table->string('last_result');
            $table->enum('status', ['active',  'inactive'])->default('active');
            $table->unsignedInteger('serial')->default(0)->index();
            $table->decimal('reward', 10, 2)->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
