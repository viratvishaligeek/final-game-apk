<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 32)->nullable()->unique();
            $table->foreignId('referrer_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('first_deposit_at')->nullable();
        });

        DB::table('users')->orderBy('id')->select('id')->chunk(200, function ($users) {
            foreach ($users as $user) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (DB::table('users')->where('referral_code', $code)->exists());

                DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
            }
        });

        // Existing depositors cannot add referral attribution retroactively.
        DB::table('users')->orderBy('id')->select('id')->chunk(200, function ($users) {
            foreach ($users as $user) {
                $firstApproved = DB::table('wallet_requests')
                    ->where('user_id', $user->id)
                    ->where('request_type', 'credit')
                    ->where('status', 'approved')
                    ->min('processed_at');

                if (!$firstApproved) {
                    $firstApproved = DB::table('transactions')
                        ->where('user_id', $user->id)
                        ->where('type', 'credit')
                        ->where('status', 'completed')
                        ->where(function ($query) {
                            $query->where('subject', 'like', '%deposit%')
                                ->orWhere('subject', 'like', '%top-up%')
                                ->orWhere('subject', 'like', '%add money%')
                                ->orWhere('subject', 'like', '%added money%');
                        })
                        ->min('created_at');
                }

                if ($firstApproved) {
                    DB::table('users')->where('id', $user->id)->update(['first_deposit_at' => $firstApproved]);
                }
            }
        });

        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->restrictOnDelete()->unique();
            $table->foreignId('first_wallet_request_id')->constrained('wallet_requests')->restrictOnDelete()->unique();
            $table->decimal('first_deposit_amount', 15, 2);
            $table->decimal('reward_amount', 15, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('status', 20)->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['referrer_user_id', 'created_at']);
        });

        DB::table('settings')->updateOrInsert(
            ['option' => 'referral_percentage'],
            ['value' => '2', 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['option' => 'referral_min_amount'],
            ['value' => '10', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_user_id']);
            $table->dropUnique(['referral_code']);
            $table->dropColumn(['referral_code', 'referrer_user_id', 'first_deposit_at']);
        });
        DB::table('settings')->whereIn('option', ['referral_percentage', 'referral_min_amount'])->delete();
    }
};
