<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('live_status', 12)->default('inactive')->index();
            $table->timestamp('live_last_seen_at')->nullable();
        });

        Schema::create('user_presence_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('site_key', 64)->default('play-online-khaiwal')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 64)->unique();
            $table->string('platform', 20)->nullable();
            $table->string('app_state', 20)->default('foreground');
            $table->timestamp('last_seen_at')->index();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'ended_at', 'last_seen_at'], 'user_presence_active_sessions_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_presence_sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['live_status', 'live_last_seen_at']);
        });
    }
};
