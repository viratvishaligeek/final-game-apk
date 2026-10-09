<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_admin_cannot_sign_in(): void
    {
        Admin::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => 'Password123',
            'status' => 'inactive',
        ]);

        $this->post('/try-login', [
            'email' => 'inactive@example.com',
            'password' => 'Password123',
        ])->assertRedirect();

        $this->assertGuest('admin');
    }

    public function test_admin_user_balance_changes_are_written_to_the_wallet_ledger(): void
    {
        $admin = Admin::create([
            'name' => 'Active Admin',
            'email' => 'active@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.users.store'), [
                'name' => 'New Member',
                'phone' => '9876543211',
                'password' => 'Password123',
                'balance' => '100.00',
                'status' => 'active',
                'bank' => 'Example Bank',
                'acc' => '1234567890',
                'ifsc' => 'ABCD0123456',
                'holdername' => 'New Member',
            ])
            ->assertRedirect(route('admin.users.index'));

        $created = User::query()->where('phone', '9876543211')->firstOrFail();

        $this->assertEquals(100.0, (float) $created->balance);
        $this->assertSame('Example Bank', $created->bank_name);
        $this->assertSame('1234567890', $created->account_number);
        $this->assertSame(1, Transaction::query()->where('user_id', $created->id)->where('type', 'credit')->count());

        $this->actingAs($admin, 'admin')
            ->put(route('admin.users.update', $created->id), [
                'name' => $created->name,
                'phone' => $created->phone,
                'balance' => '125.50',
                'status' => 'active',
                'bank' => 'Example Bank',
                'acc' => '1234567890',
                'ifsc' => 'ABCD0123456',
                'holdername' => 'New Member',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertEquals(125.5, (float) $created->fresh()->balance);
        $this->assertSame(2, Transaction::query()->where('user_id', $created->id)->where('type', 'credit')->count());
        $this->assertEquals(25.5, (float) Transaction::query()->where('user_id', $created->id)->where('type', 'credit')->orderByDesc('id')->value('amount'));
    }
}
