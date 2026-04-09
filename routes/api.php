<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\DashboardController; // ✅ Tambahkan ini

// 🔓 PUBLIC ROUTES (Tanpa Token)
Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
Route::post('/login-siswa', [AuthController::class, 'loginSiswa']);

// 🔒 PROTECTED ROUTES (Butuh Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // ✅ Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // ✅ Dashboard Stats (Real-time data)
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // Books CRUD
    Route::apiResource('books', BookController::class);
    
    // Members CRUD
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::get('/members/{id}', [MemberController::class, 'show']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    
    // Borrowings / Transaksi
    Route::get('/borrowings', [BorrowingController::class, 'index']);
    Route::post('/borrowings', [BorrowingController::class, 'store']);
    Route::post('/borrowings/for-member', [BorrowingController::class, 'storeForMember']);
    Route::put('/borrowings/{id}/return', [BorrowingController::class, 'returnBook']);
});