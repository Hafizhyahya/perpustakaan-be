<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        Book::create([
            'title' => 'Pemrograman Web',
            'author' => 'Admin',
            'publisher' => 'Perpustakaan',
            'year' => 2024,
            'stock' => 5,
        ]);
    }
}
// jalankan seeder php artisan db:seed
