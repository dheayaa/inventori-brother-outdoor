<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Pelanggan;
use App\Models\Rental;
use App\Services\InventoryAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalController extends Controller
{
    public function index(InventoryAvailability $availability)
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        $rental = Rental::with('pelanggan', 'user', 'detailRental.alat')->latest()->get();
        $pelanggan = Pelanggan::all();
        $rentalTerakhirPerPelanggan = $rental
            ->groupBy('pelanggan_id')
            ->map(fn ($items) => $items->max('tgl_rental'));
        $pelangganUntukPencarian = $pelanggan->map(function (Pelanggan $item) use ($rentalTerakhirPerPelanggan) {
            return [
                'id' => $item->id,
                'name' => $item->nama_pelanggan,
                'phone' => $item->no_hp,
                'nikSuffix' => substr($item->nik, -4),
                'lastRental' => $rentalTerakhirPerPelanggan->get($item->id),
            ];
        })->values();
        $alat = Alat::with('kategori')->orderBy('nama_alat')->orderBy('ukuran')->get();
        $alatUntukPengecekan = $alat->map(fn (Alat $item) => [
            'id' => $item->id,
            'name' => $item->nama_alat,
            'size' => $item->ukuran,
            'type' => $item->jenis_alat,
            'total' => $item->stok_total,
            'cleaning' => $item->stok_pencucian,
            'harga_jual' => 'nullable',
        ])->values();
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $availableQuantities = $availability->availableQuantities($alat, $today, $tomorrow);
        $alat->each(fn (Alat $item) => $item->setAttribute('tersedia_periode', $availableQuantities[$item->id] ?? 0));
        $kategoriAlat = $alat->pluck('kategori')->filter()->unique('id')->sortBy('nama_kategori')->values();
        $bookingMendatang = $rental
            ->filter(fn (Rental $item) => $item->jenis_transaksi === 'booking' && $item->effective_status !== 'dikembalikan')
            ->sortBy('tgl_rental')
            ->values();

        return view('rental.index', compact('rental', 'pelanggan', 'pelangganUntukPencarian', 'alat', 'alatUntukPengecekan', 'today', 'tomorrow', 'bookingMendatang', 'kategoriAlat'));
    }

    public function availability(Request $request, InventoryAvailability $availability)
    {
        if (! session('user')) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $dates = $request->validate([
            'tgl_rental' => 'required|date|after_or_equal:today',
            'tgl_kembali' => 'required|date|after:tgl_rental',
        ]);

        $alat = Alat::all();

        return response()->json([
            'availability' => $availability->availableQuantities($alat, $dates['tgl_rental'], $dates['tgl_kembali']),
        ]);
    }

    public function store(Request $request, InventoryAvailability $availability)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'jenis_transaksi' => 'nullable|in:langsung,booking',
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'tgl_rental' => 'required|date|after_or_equal:today',
            'tgl_kembali' => 'required|date|after:tgl_rental',
            'alat' => 'required|array|min:1',
            'alat.*' => 'required|integer|min:0',
            'identitas_dititipkan' => 'nullable|boolean',
        ]);

        $jenisTransaksi = $request->input('jenis_transaksi', 'langsung');
        $today = now()->toDateString();
        if ($jenisTransaksi === 'langsung' && $request->tgl_rental !== $today) {
            throw ValidationException::withMessages([
                'tgl_rental' => 'Rental langsung harus menggunakan tanggal hari ini. Pilih Booking untuk tanggal mendatang.',
            ]);
        }
        if ($jenisTransaksi === 'booking' && $request->tgl_rental < $today) {
            throw ValidationException::withMessages([
                'tgl_rental' => 'Tanggal booking tidak boleh sebelum hari ini.',
            ]);
        }

        $items = collect($request->input('alat'))->filter(fn ($quantity) => (int) $quantity > 0);
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['alat' => 'Pilih minimal satu alat untuk dibooking.']);
        }

        DB::transaction(function () use ($request, $items, $availability, $jenisTransaksi) {
            $alat = Alat::whereIn('id', $items->keys())->orderBy('id')->lockForUpdate()->get();
            if ($alat->count() !== $items->count()) {
                throw ValidationException::withMessages(['alat' => 'Salah satu alat yang dipilih tidak ditemukan.']);
            }

            $available = $availability->availableQuantities($alat, $request->tgl_rental, $request->tgl_kembali);
            foreach ($items as $alatId => $quantity) {
                if (! isset($available[$alatId]) || (int) $quantity > $available[$alatId]) {
                    $namaAlat = $alat->firstWhere('id', $alatId)?->nama_alat ?? 'Alat';
                    $availableQuantity = $available[$alatId] ?? 0;
                    throw ValidationException::withMessages([
                        'alat' => "Stok {$namaAlat} untuk rentang tanggal tersebut tidak mencukupi. Tersedia {$availableQuantity} unit.",
                    ]);
                }
            }

            $alatById = $alat->keyBy('id');
            $adaSewaAlatUtama = $items->contains(
                fn ($quantity, $id) => $alatById[$id]->jenis_alat === 'sewa'
            );
            $rincianItem = $items->map(function ($quantity, $id) use ($alatById, $adaSewaAlatUtama) {
                $alatItem = $alatById[$id];
                if ($alatItem->jenis_alat === 'jual') {
                    if ($alatItem->harga_jual === null) {
                        throw ValidationException::withMessages([
                            'alat' => "Harga jual {$alatItem->nama_alat} belum diatur di Data Alat.",
                        ]);
                    }
                    $jenisItem = 'jual';
                    $hargaSatuan = $alatItem->harga_jual;
                } elseif ($alatItem->jenis_alat === 'aksesoris' && $adaSewaAlatUtama) {
                    $jenisItem = 'gratis';
                    $hargaSatuan = 0;
                } else {
                    $jenisItem = 'sewa';
                    $hargaSatuan = $alatItem->harga_sewa;
                }

                return [
                    'alat' => $alatItem,
                    'jumlah' => (int) $quantity,
                    'jenis_item' => $jenisItem,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $hargaSatuan * (int) $quantity,
                ];
            });
            $adaItemDisewakan = $rincianItem->contains(fn ($item) => $item['jenis_item'] !== 'jual');
            if ($jenisTransaksi === 'booking' && $request->tgl_rental > now()->toDateString() && ! $adaItemDisewakan) {
                throw ValidationException::withMessages([
                    'alat' => 'Barang jual saja tidak dapat dibuat sebagai booking mendatang. Catat penjualannya saat transaksi langsung.',
                ]);
            }
            $totalBayar = $rincianItem->sum('subtotal');
            $rental = Rental::create([
                'kode_rental' => 'RNT-'.now()->format('Ymd').'-'.random_int(100, 999),
                'pelanggan_id' => $request->pelanggan_id,
                'user_id' => session('user')->id,
                'tgl_rental' => $request->tgl_rental,
                'tgl_kembali' => $request->tgl_kembali,
                'total_bayar' => $totalBayar,
                'identitas_dititipkan' => $request->boolean('identitas_dititipkan'),
                'identitas_dikembalikan' => false,
                'status' => $adaItemDisewakan ? 'dipinjam' : 'dikembalikan',
                'jenis_transaksi' => $jenisTransaksi,
                'keterangan' => $request->keterangan,
            ]);

            foreach ($rincianItem as $rincian) {
                $alatItem = $rincian['alat'];
                $rental->detailRental()->create([
                    'alat_id' => $alatItem->id,
                    'jumlah' => $rincian['jumlah'],
                    'jenis_item' => $rincian['jenis_item'],
                    'ukuran' => $alatItem->ukuran,
                    'harga_sewa' => $rincian['harga_satuan'],
                    'subtotal' => $rincian['subtotal'],
                ]);
            }
        });

        $message = $jenisTransaksi === 'booking'
            ? 'Booking berhasil dicatat!'
            : 'Transaksi rental dan penjualan berhasil dicatat!';

        return redirect()->route('rental.index')->with('success', $message);
    }

    public function updateIdentityDeposit(Request $request, $id)
    {
        if (! session('user')) {
            return redirect()->route('login');
        }
        if (session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $data = $request->validate([
            'identitas_dititipkan' => 'required|boolean',
        ]);

        $rental = Rental::findOrFail($id);
        if ($rental->jenis_transaksi !== 'booking' || $rental->status === 'dikembalikan') {
            abort(422, 'Status identitas hanya dapat diubah untuk booking yang belum dikembalikan.');
        }

        $identitasDititipkan = (bool) $data['identitas_dititipkan'];
        $rental->update([
            'identitas_dititipkan' => $identitasDititipkan,
            'identitas_dikembalikan' => $identitasDititipkan && $rental->identitas_dikembalikan,
        ]);

        $message = $identitasDititipkan
            ? 'Status identitas booking diperbarui: kartu identitas sudah dititipkan.'
            : 'Status identitas booking diperbarui: kartu identitas belum dititipkan.';

        return redirect()->route('rental.index')->with('success', $message);
    }

    public function destroy($id)
    {
        if (session('user') && session('user')->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        Rental::findOrFail($id)->delete();

        return redirect()->route('rental.index')->with('success', 'Rental berhasil dihapus!');
    }
}
