<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'site_key')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_phone_unique');
                $table->dropUnique('users_referral_code_unique');
                $table->unique(['site_key', 'phone'], 'users_site_key_phone_unique');
                $table->unique(['site_key', 'referral_code'], 'users_site_key_referral_code_unique');
            });
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'site_key')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropUnique('settings_option_unique');
                // Keep the combined utf8mb4 index within older MySQL 5.7 limits.
                $table->string('option', 159)->change();
                $table->unique(['site_key', 'option'], 'settings_site_key_option_unique');
            });
        }

        if (Schema::hasTable('push_subscriptions') && Schema::hasColumn('push_subscriptions', 'site_key')) {
            Schema::table('push_subscriptions', function (Blueprint $table): void {
                $table->dropUnique('push_subscriptions_token_hash_unique');
                $table->unique(['site_key', 'token_hash'], 'push_subscriptions_site_key_token_hash_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('push_subscriptions') && Schema::hasColumn('push_subscriptions', 'site_key')) {
            Schema::table('push_subscriptions', function (Blueprint $table): void {
                $table->dropUnique('push_subscriptions_site_key_token_hash_unique');
                $table->unique('token_hash', 'push_subscriptions_token_hash_unique');
            });
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'site_key')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropUnique('settings_site_key_option_unique');
                $table->string('option', 255)->change();
                $table->unique('option', 'settings_option_unique');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'site_key')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_site_key_referral_code_unique');
                $table->dropUnique('users_site_key_phone_unique');
                $table->unique('phone', 'users_phone_unique');
                $table->unique('referral_code', 'users_referral_code_unique');
            });
        }
    }
};
