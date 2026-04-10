<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'year',
        'stock',
        'cover_image', // ✅ Tambahkan ini
    ];

    protected $casts = [
        'year'  => 'integer',
        'stock' => 'integer',
    ];

    // Relasi ke transaksi
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    // ✅ Helper: Get full URL cover image
    public function getCoverUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }
        // Jika cover_image sudah full URL, return langsung
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }
        // Jika hanya filename, prepend storage URL
        return asset('storage/' . $this->cover_image);
    }
}