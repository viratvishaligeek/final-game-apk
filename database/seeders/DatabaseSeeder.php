<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Never create a known default administrator password in a deployed
        // database. Configure these values explicitly for a fresh installation.
        $adminEmail = 'admin@gmail.com';
        $adminPassword = 'qwerty';

        if (is_string($adminEmail) && $adminEmail !== '' && is_string($adminPassword) && $adminPassword !== '') {
            Admin::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('SEED_ADMIN_NAME', 'Site Administrator'),
                    'password' => $adminPassword,
                    'status' => 'active',
                ]
            );
        }

        $this->call([
            PlayOnlineKhaiwalSettingsSeeder::class,
            GameSeeder::class,
            PageSeeder::class,
            UserSeeder::class,
            PlayOnlineKhaiwalFaqSeeder::class,
        ]);
    }
}
