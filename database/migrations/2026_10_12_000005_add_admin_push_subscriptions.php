<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('push_subscriptions') || Schema::hasColumn('push_subscriptions', 'admin_id')) {
            return;
        }

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->foreignId('admin_id')->nullable()->after('user_id')->constrained('admins')->cascadeOnDelete();
            $table->index(['admin_id', 'platform']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('push_subscriptions') || !Schema::hasColumn('push_subscriptions', 'admin_id')) {
            return;
        }

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['admin_id', 'platform']);
            $table->dropConstrainedForeignId('admin_id');
        });
    }
};
