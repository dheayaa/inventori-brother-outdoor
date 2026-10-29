<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        if ($redirect = $this->redirectIfNotAdmin()) {
            return $redirect;
        }

        $users = User::latest()->get();
        return view('user.index', compact('users'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->redirectIfNotAdmin()) {
            return $redirect;
        }

        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:users',
            'no_hp' => ['nullable', 'string', 'regex:/^[0-9]{10,13}$/'],
            'password' => 'required|min:6',
            'role' => 'required',
        ], [
            'no_hp.regex' => 'Nomor HP harus terdiri dari 10 sampai 13 angka.',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->redirectIfNotAdmin()) {
            return $redirect;
        }

        $request->validate([
            'nama' => 'required',
            'no_hp' => ['nullable', 'string', 'regex:/^[0-9]{10,13}$/'],
            'role' => 'required',
        ], [
            'no_hp.regex' => 'Nomor HP harus terdiri dari 10 sampai 13 angka.',
        ]);

        $user = User::findOrFail($id);
        $data = [
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'role' => $request->role,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('user.index')->with('success', 'User berhasil diupdate!');
    }

    public function destroy($id)
    {
        if ($redirect = $this->redirectIfNotAdmin()) {
            return $redirect;
        }

        $user = User::findOrFail($id);
        if ($user->id === session('user')->id) {
            return redirect()->route('user.index')->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        if ($user->role === 'owner' && User::where('role', 'owner')->count() === 1) {
            return redirect()->route('user.index')->with('error', 'Tidak bisa menghapus satu-satunya akun owner!');
        }

        $user->delete();
        return redirect()->route('user.index')->with('success', 'User berhasil dihapus!');
    }

    private function redirectIfNotAdmin(): ?RedirectResponse
    {
        $user = session('user');
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard');
        }

        return null;
    }
}