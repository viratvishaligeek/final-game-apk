<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('password_reset_otps') || Schema::hasColumn('password_reset_otps', 'site_key')) {
            return;
        }

        $defaultSite = (string) config('sites.default', 'play-online-khaiwal');

        Schema::table('password_reset_otps', function (Blueprint $table) use ($defaultSite): void {
            // Existing OTP rows belong to the original default site.
            $table->string('site_key', 32)->default($defaultSite);
            $table->index(['site_key', 'phone'], 'password_reset_otps_site_phone_index');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('password_reset_otps') || !Schema::hasColumn('password_reset_otps', 'site_key')) {
            return;
        }

        Schema::table('password_reset_otps', function (Blueprint $table): void {
            $table->dropIndex('password_reset_otps_site_phone_index');
            $table->dropColumn('site_key');
        });
    }
};
