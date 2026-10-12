<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('faqs') && !Schema::hasColumn('faqs', 'platform')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->string('platform', 8)->default('both')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('faqs') && Schema::hasColumn('faqs', 'platform')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->dropIndex(['platform']);
                $table->dropColumn('platform');
            });
        }
    }
};
