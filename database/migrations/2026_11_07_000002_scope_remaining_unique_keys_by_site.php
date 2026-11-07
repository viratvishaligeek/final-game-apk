<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('games') && Schema::hasColumn('games', 'site_key')) {
            Schema::table('games', function (Blueprint $table): void {
                $table->dropUnique('games_name_unique');
                $table->dropUnique('games_slug_unique');
                $table->unique(['site_key', 'name'], 'games_site_key_name_unique');
                $table->unique(['site_key', 'slug'], 'games_site_key_slug_unique');
            });
        }

        if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'site_key')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropUnique('pages_name_unique');
                $table->unique(['site_key', 'name'], 'pages_site_key_name_unique');
            });
        }

        if (Schema::hasTable('admin_payment_alerts') && Schema::hasColumn('admin_payment_alerts', 'site_key')) {
            Schema::table('admin_payment_alerts', function (Blueprint $table): void {
                $table->dropUnique('admin_payment_alerts_attempt_id_unique');
                $table->unique(['site_key', 'attempt_id'], 'admin_payment_alerts_site_attempt_unique');
            });
        }

        if (Schema::hasTable('user_presence_sessions') && Schema::hasColumn('user_presence_sessions', 'site_key')) {
            Schema::table('user_presence_sessions', function (Blueprint $table): void {
                $table->dropUnique('user_presence_sessions_session_id_unique');
                $table->unique(['site_key', 'session_id'], 'user_presence_sessions_site_session_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_presence_sessions') && Schema::hasColumn('user_presence_sessions', 'site_key')) {
            Schema::table('user_presence_sessions', function (Blueprint $table): void {
                $table->dropUnique('user_presence_sessions_site_session_unique');
                $table->unique('session_id', 'user_presence_sessions_session_id_unique');
            });
        }

        if (Schema::hasTable('admin_payment_alerts') && Schema::hasColumn('admin_payment_alerts', 'site_key')) {
            Schema::table('admin_payment_alerts', function (Blueprint $table): void {
                $table->dropUnique('admin_payment_alerts_site_attempt_unique');
                $table->unique('attempt_id', 'admin_payment_alerts_attempt_id_unique');
            });
        }

        if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'site_key')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropUnique('pages_site_key_name_unique');
                $table->unique('name', 'pages_name_unique');
            });
        }

        if (Schema::hasTable('games') && Schema::hasColumn('games', 'site_key')) {
            Schema::table('games', function (Blueprint $table): void {
                $table->dropUnique('games_site_key_slug_unique');
                $table->dropUnique('games_site_key_name_unique');
                $table->unique('name', 'games_name_unique');
                $table->unique('slug', 'games_slug_unique');
            });
        }
    }
};
