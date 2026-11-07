<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('home_pages')) {
            return;
        }

        Schema::table('home_pages', function (Blueprint $table): void {
            if (!Schema::hasColumn('home_pages', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('home_pages', 'image_alt')) {
                $table->string('image_alt', 255)->nullable();
            }
            if (!Schema::hasColumn('home_pages', 'button_label')) {
                $table->string('button_label', 100)->nullable();
            }
            if (!Schema::hasColumn('home_pages', 'button_url')) {
                $table->string('button_url', 1000)->nullable();
            }
            if (!Schema::hasColumn('home_pages', 'button_style')) {
                $table->string('button_style', 30)->default('primary');
            }
            if (!Schema::hasColumn('home_pages', 'layout')) {
                $table->string('layout', 30)->default('standard');
            }
            if (!Schema::hasColumn('home_pages', 'display_order')) {
                $table->unsignedInteger('display_order')->default(0)->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('home_pages')) {
            return;
        }

        Schema::table('home_pages', function (Blueprint $table): void {
            foreach (['image', 'image_alt', 'button_label', 'button_url', 'button_style', 'layout', 'display_order'] as $column) {
                if (Schema::hasColumn('home_pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
