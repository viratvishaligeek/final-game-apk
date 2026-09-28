<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Rahul Kumar',
                'phone'    => '9876543210',
                'password' => 'password',
                'city'     => 'Agra',
                'gender'   => 'Male',
            ],
            [
                'name'     => 'Amit Sharma',
                'phone'    => '9876543211',
                'password' => 'password',
                'city'     => 'Delhi',
                'gender'   => 'Male',
            ],
            [
                'name'     => 'Vikas Singh',
                'phone'    => '9876543212',
                'password' => 'password',
                'city'     => 'Noida',
                'gender'   => 'Male',
            ],
            [
                'name'     => 'Priya Verma',
                'phone'    => '9876543213',
                'password' => 'password',
                'city'     => 'Lucknow',
                'gender'   => 'Female',
            ],
            [
                'name'     => 'Neha Gupta',
                'phone'    => '9876543214',
                'password' => 'password',
                'city'     => 'Kanpur',
                'gender'   => 'Female',
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'name'     => $user['name'],
                'phone'    => $user['phone'],
                'password' => Hash::make($user['password']),
                'city'     => $user['city'],
                'gender'   => $user['gender'],
                'balance'  => 0,
                'status'   => 'active',
            ]);
        }
    }
}
