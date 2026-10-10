<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('menu_visible')->default(true)->after('is_editable');
            $table->unsignedInteger('menu_order')->default(0)->after('menu_visible');
            $table->string('meta_title')->nullable()->after('menu_order');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->boolean('noindex')->default(false)->after('meta_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'menu_visible',
                'menu_order',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'noindex',
            ]);
        });
    }
};
