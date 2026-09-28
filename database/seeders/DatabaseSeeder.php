<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Admin::create([
            'name' => 'SUper Admin',
            'email' => 'admin@gmail.com',
            'password' => 'qwerty',
            'status' => 'active'
        ]);
         $this->call([
            GameSeeder::class,
            UserSeeder::class,
        ]);
    }
}
