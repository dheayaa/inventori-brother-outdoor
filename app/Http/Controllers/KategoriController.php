<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        if (!session('user')) return redirect()->route('login');
        $kategori = Kategori::with(['alat' => function ($query) {
            $query->orderBy('nama_alat')->orderBy('ukuran');
        }])->orderBy('nama_kategori')->get();
        return view('kategori.index', compact('kategori'));
    }

    public function store(Request $request)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'nama_kategori' => 'required',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'nama_kategori' => 'required',
        ]);

        $kategori = Kategori::findOrFail($id);
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diupdate!');
    }

    public function destroy($id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        Kategori::findOrFail($id)->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}