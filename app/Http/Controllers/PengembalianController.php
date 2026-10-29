<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengembalianController extends Controller
{
    public function index()
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $today = now()->toDateString();
        $rental = Rental::with('pelanggan', 'detailRental.alat')
            ->latest()->get()
            ->filter(fn ($item) => $item->tgl_rental <= $today
                && (
                    in_array($item->effective_status, ['dipinjam', 'terlambat'], true)
                    || ($item->tgl_kembali_aktual
                        && $item->detailRental->contains(fn ($detail) => $detail->jenis_item !== 'jual'))
                ))
            ->values();
        $rentalPerluPerhatian = $rental
            ->filter(fn ($item) => $item->tgl_kembali_aktual === null && $item->tgl_kembali <= $today)
            ->values();

        return view('pengembalian.index', compact('rental', 'rentalPerluPerhatian', 'today'));
    }

    public function proses(Request $request, $id)
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $rental = Rental::with('detailRental')->findOrFail($id);
        if ($rental->tgl_rental > now()->toDateString() || $rental->status === 'dikembalikan') {
            abort(422, 'Booking ini belum dapat diproses sebagai pengembalian.');
        }

        $request->validate([
            'tgl_kembali_aktual' => ['required', 'date', 'after_or_equal:'.$rental->tgl_rental, 'before_or_equal:today'],
            'denda' => 'nullable|integer|min:0',
            'identitas_dikembalikan' => 'nullable|boolean',
        ]);

        $tglKembaliAktual = $request->tgl_kembali_aktual;
        $denda = (int) ($request->denda ?? 0);

        DB::transaction(function () use ($id, $tglKembaliAktual, $denda, $request) {
            $rental = Rental::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($rental->tgl_rental > now()->toDateString() || $rental->status === 'dikembalikan') {
                throw ValidationException::withMessages([
                    'rental' => 'Rental ini sudah diproses atau belum dapat dikembalikan.',
                ]);
            }

            $rental->update([
                'tgl_kembali_aktual' => $tglKembaliAktual,
                'denda' => $denda,
                'identitas_dikembalikan' => $rental->identitas_dititipkan
                    && $request->boolean('identitas_dikembalikan'),
                'status' => 'dikembalikan',
            ]);

            foreach ($rental->detailRental as $detail) {
                if ($detail->jenis_item === 'jual') {
                    continue;
                }

                $alat = $detail->alat()->lockForUpdate()->firstOrFail();
                $alat->increment('stok_pencucian', $detail->jumlah);
            }
        });

        $tanggalKembaliAktual = Carbon::parse($tglKembaliAktual)->locale('id')->translatedFormat('j F Y');
        $message = 'Pengembalian berhasil diproses! Tanggal kembali aktual: '.$tanggalKembaliAktual.'. Barang masuk pencucian. ';
        $message .= $denda > 0
            ? 'Denda yang dicatat admin: Rp '.number_format($denda, 0, ',', '.')
            : 'Tidak ada denda yang dicatat.';

        return redirect()->route('pengembalian.index')->with('success', $message);
    }

    public function updateIdentitas(Request $request, $id)
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $data = $request->validate([
            'identitas_dikembalikan' => ['required', 'boolean'],
        ]);

        $rental = Rental::findOrFail($id);
        if (! $rental->tgl_kembali_aktual || $rental->status !== 'dikembalikan') {
            abort(422, 'Status identitas hanya dapat diubah setelah pengembalian diproses.');
        }
        if (! $rental->identitas_dititipkan && (bool) $data['identitas_dikembalikan']) {
            throw ValidationException::withMessages([
                'identitas_dikembalikan' => 'Rental ini tidak memiliki identitas yang dititipkan.',
            ]);
        }

        $rental->update([
            'identitas_dikembalikan' => (bool) $data['identitas_dikembalikan'],
        ]);

        $message = $rental->identitas_dikembalikan
            ? 'Status identitas diperbarui: kartu sudah dikembalikan ke pelanggan.'
            : 'Status identitas diperbarui: kartu masih dititipkan.';

        return redirect()->route('pengembalian.index')->with('success', $message);
    }
}
