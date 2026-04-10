<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use App\Http\Controllers\Controller;
use App\Models\{Book, Borrowing};

class BorrowingController extends Controller
{
    /**
     * ✅ List semua transaksi peminjaman (dengan relasi buku & user)
     */
    public function index()
    {
        $borrowings = Borrowing::with(['book', 'user'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $borrowings
        ]);
    }

    /**
     * ✅ Siswa meminjam buku sendiri (menggunakan Auth::id())
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id'     => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:borrow_date',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        // Cek stok buku
        if ($book->stock < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Stok buku habis'
            ], 400);
        }

        // Cek apakah user sudah meminjam buku ini dan belum dikembalikan
        $exists = Borrowing::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah meminjam buku ini'
            ], 400);
        }

        // Gunakan transaction untuk keamanan data (atomik)
        DB::transaction(function () use ($book, $validated) {
            // Kurangi stok buku
            $book->decrement('stock');

            // Buat record transaksi peminjaman
            Borrowing::create([
                'user_id'     => Auth::id(),
                'book_id'     => $book->id,
                'borrow_date' => $validated['borrow_date'],
                'return_date' => $validated['return_date'],
                'status'      => 'borrowed',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dipinjam'
        ], 201);
    }

    /**
     * ✅ Admin meminjamkan buku untuk anggota lain (opsional - untuk fitur admin)
     */
    public function storeForMember(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:members,id',
            'book_id'     => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:borrow_date',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->stock < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Stok buku habis'
            ], 400);
        }

        DB::transaction(function () use ($book, $validated) {
            $book->decrement('stock');
            Borrowing::create([
                'user_id'     => $validated['user_id'],
                'book_id'     => $book->id,
                'borrow_date' => $validated['borrow_date'],
                'return_date' => $validated['return_date'],
                'status'      => 'borrowed',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil dicatat'
        ], 201);
    }

    /**
     * ✅ Kembalikan buku - DENGAN VALIDASI KEAMANAN
     * Hanya user yang meminjam atau admin yang boleh mengembalikan
     */
    public function returnBook(Request $request, $id)
    {
        $borrowing = Borrowing::findOrFail($id);

        // 🔐 VALIDASI KEAMANAN: Hanya pemilik atau admin yang bisa return
        if ($borrowing->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        // Cek apakah buku sudah dikembalikan sebelumnya
        if ($borrowing->status === 'returned') {
            return response()->json([
                'success' => false,
                'message' => 'Buku sudah dikembalikan'
            ], 400);
        }

        // Gunakan transaction untuk keamanan data
        DB::transaction(function () use ($borrowing) {
            // Update status transaksi
            $borrowing->update([
                'status'      => 'returned',
                'return_date' => now(), // Catat tanggal aktual pengembalian
            ]);

            // Tambah stok buku kembali ke inventory
            $borrowing->book->increment('stock');
        });

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dikembalikan'
        ]);
    }
}
