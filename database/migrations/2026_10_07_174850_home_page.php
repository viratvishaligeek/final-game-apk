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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('short_desc')->nullable();
            $table->longText('content')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('location')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('phone')->nullable();
            $table->string('telegram')->nullable();
            $table->string('background')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
