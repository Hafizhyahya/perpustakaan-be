<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * ✅ LOGIN ADMIN
     * Hanya untuk user dengan role='admin'
     */
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user dengan role admin
        $admin = Member::where('email', $request->email)
            ->where('role', 'admin')
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah, atau akun bukan admin'
            ], 401);
        }

        // Generate token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login admin berhasil',
            'token' => $token,
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ]
        ]);
    }

    /**
     * ✅ LOGIN SISWA
     * Hanya untuk user dengan role='siswa' yang sudah terdaftar
     */
    public function loginSiswa(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cek apakah user terdaftar sebagai siswa
        $siswa = Member::where('email', $request->email)
            ->where('role', 'siswa')
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar sebagai anggota. Silakan daftar terlebih dahulu.'
            ], 404);
        }

        // Validasi password
        if (!Hash::check($request->password, $siswa->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah'
            ], 401);
        }

        // Generate token
        $token = $siswa->createToken('siswa-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login siswa berhasil',
            'token' => $token,
            'user' => [
                'id' => $siswa->id,
                'name' => $siswa->name,
                'email' => $siswa->email,
                'role' => $siswa->role,
            ]
        ]);
    }

    /**
     * ✅ LOGOUT (untuk semua role)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}