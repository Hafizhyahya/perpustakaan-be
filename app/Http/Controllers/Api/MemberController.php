<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:members,email',
            'password' => 'required|min:6',
            'nis'      => 'nullable|string|unique:members,nis',
            'class'    => 'nullable|string|max:50',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'sometimes|in:admin,siswa',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = $validated['role'] ?? 'siswa';

        $member = Member::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil ditambahkan',
            'data'    => $member
        ], 201);
    }

    public function show($id)
    {
        $member = Member::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $member
        ]);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:members,email,' . $id,
            'password' => 'nullable|min:6',
            'nis'      => 'sometimes|nullable|string|unique:members,nis,' . $id,
            'class'    => 'sometimes|nullable|string|max:50',
            'phone'    => 'sometimes|nullable|string|max:20',
            'role'     => 'sometimes|in:admin,siswa',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $member->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil diperbarui',
            'data'    => $member
        ]);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil dihapus'
        ]);
    }
}
