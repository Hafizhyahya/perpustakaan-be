<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Book::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'author'    => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year'      => 'nullable|integer|min:1000|max:' . date('Y'),
            'stock'     => 'required|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // ✅ Handle upload gambar
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }

        $book = Book::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil ditambahkan',
            'data'    => $book
        ], 201);
    }

    public function show($id)
    {
        $book = Book::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $book
        ]);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title'     => 'sometimes|required|string|max:255',
            'author'    => 'sometimes|required|string|max:255',
            'publisher' => 'sometimes|nullable|string|max:255',
            'year'      => 'sometimes|nullable|integer|min:1000|max:' . date('Y'),
            'stock'     => 'sometimes|required|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // ✅ Handle upload gambar baru
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            // Hapus gambar lama jika ada
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }
            // Upload gambar baru
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        } 
        // ✅ Handle hapus gambar (jika kirim empty string)
        elseif ($request->has('cover_image') && $request->cover_image === '') {
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = null;
        }
        // Jika tidak ada cover_image di request → pertahankan gambar lama

        $book->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil diperbarui',
            'data'    => $book
        ]);
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // ✅ Hapus gambar cover jika ada
        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dihapus'
        ]);
    }
}