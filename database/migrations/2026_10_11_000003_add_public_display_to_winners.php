<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->string('public_display_name', 60)->nullable()->after('winning_amount');
            $table->boolean('is_public')->default(false)->after('public_display_name');
            $table->index(['is_public', 'game_date', 'id'], 'winners_public_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropIndex('winners_public_date_index');
            $table->dropColumn(['public_display_name', 'is_public']);
        });
    }
};
