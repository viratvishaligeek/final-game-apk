<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->command?->warn('UserSeeder is restricted to local/testing environments.');
            return;
        }

        $users = [
            [
                'name'     => 'Demo Member 01',
                'phone'    => '0000000001',
                'password' => 'password',
                'city'     => 'Agra',
                'gender'   => 'Male',
            ],
            [
                'name'     => 'Demo Member 02',
                'phone'    => '0000000002',
                'password' => 'password',
                'city'     => 'Delhi',
                'gender'   => 'Male',
            ],
            [
                'name'     => 'Demo Member 03',
                'phone'    => '0000000003',
                'password' => 'password',
                'city'     => 'Noida',
                'gender'   => 'Male',
            ],
            [
                'name'     => 'Demo Member 04',
                'phone'    => '0000000004',
                'password' => 'password',
                'city'     => 'Lucknow',
                'gender'   => 'Female',
            ],
            [
                'name'     => 'Demo Member 05',
                'phone'    => '0000000005',
                'password' => 'password',
                'city'     => 'Kanpur',
                'gender'   => 'Female',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['phone' => $user['phone']], [
                'name'     => $user['name'],
                'password' => Hash::make($user['password']),
                'city'     => $user['city'],
                'gender'   => $user['gender'],
                'balance'  => 0,
                'status'   => 'active',
            ]);
        }
    }
}
