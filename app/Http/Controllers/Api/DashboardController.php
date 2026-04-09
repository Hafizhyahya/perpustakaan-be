<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Member;
use App\Models\Borrowing;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * ✅ Get stats untuk dashboard (Admin & Siswa)
     */
    public function stats()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            // Admin: lihat semua data perpustakaan
            return response()->json([
                'success' => true,
                'data' => [
                    'total_books' => Book::count(),
                    'total_members' => Member::where('role', 'siswa')->count(),
                    'active_borrowings' => Borrowing::where('status', 'borrowed')->count(),
                    'returned_today' => Borrowing::where('status', 'returned')
                        ->whereDate('updated_at', today())
                        ->count(),
                ]
            ]);
        } else {
            // Siswa: hanya data miliknya
            $userId = $user->id;
            return response()->json([
                'success' => true,
                'data' => [
                    'my_active_borrowings' => Borrowing::where('user_id', $userId)
                        ->where('status', 'borrowed')
                        ->count(),
                    'my_total_borrowed' => Borrowing::where('user_id', $userId)->count(),
                    'my_overdue' => Borrowing::where('user_id', $userId)
                        ->where('status', 'borrowed')
                        ->where('return_date', '<', today())
                        ->count(),
                ]
            ]);
        }
    }
}