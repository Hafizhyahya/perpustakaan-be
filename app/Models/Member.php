<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // ✅ TAMBAHKAN INI

class Member extends Authenticatable
{
    use HasApiTokens, HasFactory; // ✅ TAMBAHKAN HasApiTokens di sini

    protected $fillable = [
        'name', 'email', 'password', 'nis', 'class', 'phone', 'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 11+
    ];

    // Relasi ke transaksi peminjaman
    public function borrowings()
    {
        // hasMany yaitu 1 member → punya banyak peminjaman
        return $this->hasMany(Borrowing::class, 'user_id');
    }
}

//kenapa pake hasMany buat apa
