<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index()
    {
        if (!session('user')) return redirect()->route('login');
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }
        $pelanggan = Pelanggan::latest()->get();
        return view('pelanggan.index', compact('pelanggan'));
    }

    public function store(Request $request)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'nama_pelanggan' => 'required',
            'no_hp' => ['required', 'string', 'regex:/^[0-9]{10,13}$/'],
            'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/', 'unique:pelanggan,nik'],
        ], [
            'no_hp.regex' => 'Nomor HP harus terdiri dari 10 sampai 13 angka.',
            'nik.regex' => 'NIK harus terdiri tepat dari 16 angka.',
            'nik.unique' => 'NIK tersebut sudah terdaftar.',
        ]);

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp' => $request->no_hp,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $pelanggan = Pelanggan::findOrFail($id);

        $request->validate([
            'nama_pelanggan' => 'required',
            'no_hp' => ['required', 'string', 'regex:/^[0-9]{10,13}$/'],
            'nik' => [
                'required',
                'string',
                'regex:/^[0-9]{16}$/',
                Rule::unique('pelanggan', 'nik')->ignore($pelanggan->id),
            ],
        ], [
            'no_hp.regex' => 'Nomor HP harus terdiri dari 10 sampai 13 angka.',
            'nik.regex' => 'NIK harus terdiri tepat dari 16 angka.',
            'nik.unique' => 'NIK tersebut sudah terdaftar.',
        ]);

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp' => $request->no_hp,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function destroy($id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        Pelanggan::findOrFail($id)->delete();
        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }
}