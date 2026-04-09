<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;   // ← tambahkan ini
use Database\Seeders\BookSeeder;   // ← tambahkan ini

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BookSeeder::class,
        ]);
    }
}
