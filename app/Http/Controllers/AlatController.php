<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Services\InventoryAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AlatController extends Controller
{
    public function index(InventoryAvailability $availability)
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        $alat = Alat::with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama_alat')
            ->orderBy('ukuran')
            ->get();
        $today = now()->toDateString();
        $availableToday = $availability->availableQuantities($alat, $today, $today);
        $alat->each(fn (Alat $item) => $item->setAttribute('stok_tersedia', $availableToday[$item->id] ?? 0));
        $kategori = Kategori::all();

        return view('alat.index', compact('alat', 'kategori'));
    }

    public function store(Request $request)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'kategori_id' => 'required',
            'nama_alat' => 'required',
            'ukuran' => 'nullable|string|max:50',
            'jenis_alat' => 'nullable|in:sewa,aksesoris,jual',
            'stok_total' => 'required|integer',
            'harga_sewa' => $request->input('jenis_alat', 'sewa') === 'jual'
                ? 'nullable|integer|min:0'
                : 'required|integer|min:0',
            'harga_jual' => 'nullable|required_if:jenis_alat,jual|integer|min:0',
        ]);

        Alat::create([
            'kategori_id' => $request->kategori_id,
            'nama_alat' => $request->nama_alat,
            'ukuran' => $request->ukuran,
            'jenis_alat' => $request->input('jenis_alat', 'sewa'),
            'kode_alat' => 'SYS-'.Str::uuid(),
            'stok_total' => $request->stok_total,
            'stok_tersedia' => $request->stok_total,
            'harga_sewa' => $request->input('jenis_alat', 'sewa') === 'jual' ? 0 : $request->harga_sewa,
            'harga_jual' => $request->input('jenis_alat', 'sewa') === 'jual' ? $request->harga_jual : null,
            'kondisi' => $request->kondisi,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('alat.index')->with('success', 'Alat berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $alat = Alat::findOrFail($id);

        $request->validate([
            'kategori_id' => 'required',
            'nama_alat' => 'required',
            'ukuran' => 'nullable|string|max:50',
            'jenis_alat' => 'nullable|in:sewa,aksesoris,jual',
            'stok_total' => 'required|integer',
            'harga_sewa' => $request->input('jenis_alat', $alat->jenis_alat) === 'jual'
                ? 'nullable|integer|min:0'
                : 'required|integer|min:0',
            'harga_jual' => 'nullable|required_if:jenis_alat,jual|integer|min:0',
        ]);

        $alat->update([
            'kategori_id' => $request->kategori_id,
            'nama_alat' => $request->nama_alat,
            'ukuran' => $request->ukuran,
            'jenis_alat' => $request->input('jenis_alat', $alat->jenis_alat),
            'stok_total' => $request->stok_total,
            'harga_sewa' => $request->input('jenis_alat', $alat->jenis_alat) === 'jual' ? 0 : $request->harga_sewa,
            'harga_jual' => $request->input('jenis_alat', $alat->jenis_alat) === 'jual' ? $request->harga_jual : null,
            'kondisi' => $request->kondisi,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('alat.index')->with('success', 'Alat berhasil diupdate!');
    }

    public function markClean(Request $request, $id)
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $data = $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($id, $data) {
            $alat = Alat::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($data['jumlah'] > $alat->stok_pencucian) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jumlah melebihi stok alat yang sedang dalam pencucian.',
                ]);
            }

            $alat->decrement('stok_pencucian', $data['jumlah']);
        });

        return redirect()->route('alat.index')->with('success', 'Jumlah alat yang sudah bersih berhasil ditambahkan ke stok siap disewa.');
    }

    public function destroy($id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        Alat::findOrFail($id)->delete();

        return redirect()->route('alat.index')->with('success', 'Alat berhasil dihapus!');
    }
}
