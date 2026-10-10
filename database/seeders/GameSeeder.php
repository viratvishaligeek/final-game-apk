<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use Illuminate\Support\Str;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            'Gali',
            'Disawar',
            'Gaziabad',
            'Faridabad',
            'Mohali',
            'Shree Ganesh',
            'Maa Kali',
            'Laxmi',
        ];

        foreach ($games as $index => $name) {
            $slug = Str::slug($name);
            $alreadyExists = Game::query()
                ->where(fn ($query) => $query->where('slug', $slug)->orWhere('name', $name))
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            Game::create([
                'name'        => $name,
                'slug'        => $slug,
                'result_time' => '23:00:00',
                'play_start'  => '18:00:00',
                'play_end'    => '20:00:00',
                'last_result' => null,
                'status'      => 'active',
                'reward'      => 98,
                'serial'      => $index + 1,
            ]);
        }
    }
}
