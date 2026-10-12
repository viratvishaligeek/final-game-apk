<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        'users',
        'notifications',
        'games',
        'results',
        'pages',
        'banners',
        'home_pages',
        'faqs',
        'winners',
        'bids',
        'transactions',
        'wallet_requests',
        'push_subscriptions',
        'settings',
        'support_conversations',
        'support_messages',
        'referral_rewards',
        'community_posts',
        'community_replies',
    ];

    public function up(): void
    {
        $defaultSite = (string) config('sites.default', 'play-online-khaiwal');

        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'site_key')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($defaultSite): void {
                $table->string('site_key', 32)->default($defaultSite)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'site_key')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropIndex(['site_key']);
                $table->dropColumn('site_key');
            });
        }
    }
};
