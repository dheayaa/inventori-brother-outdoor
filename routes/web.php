<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RentalReportController;
use App\Http\Controllers\UserController;
use App\Models\Alat;
use App\Models\DetailRental;
use App\Models\Rental;
use App\Services\InventoryAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function (Request $request) {
    $user = session('user');
    if (! $user) {
        return redirect()->route('login');
    }

    $today = now()->toDateString();

    $allRental = Rental::with('detailRental.alat')->get();
    $totalAlat = Alat::sum('stok_total');
    $alatUntukKetersediaan = Alat::all();
    $alatTersedia = array_sum(app(InventoryAvailability::class)->availableQuantities(
        $alatUntukKetersediaan,
        $today,
        $today
    ));
    $sedangDirental = $allRental
        ->filter(fn ($r) => $r->tgl_rental <= $today
            && in_array($r->effective_status, ['dipinjam', 'terlambat'], true))
        ->sum(fn ($r) => $r->detailRental->sum('jumlah'));
    $awalBulan = now()->startOfMonth()->toDateString();
    $akhirBulan = now()->endOfMonth()->toDateString();
    $pendapatanBulanIni = Rental::whereBetween('tgl_rental', [$awalBulan, $akhirBulan])
        ->whereYear('tgl_rental', now()->year)
        ->sum('total_bayar')
        + Rental::whereBetween('tgl_kembali_aktual', [$awalBulan, $akhirBulan])
            ->sum('denda');
    $penjualanBulanIni = $allRental
        ->filter(fn (Rental $item) => $item->tgl_rental >= $awalBulan && $item->tgl_rental <= $akhirBulan)
        ->sum(fn (Rental $item) => $item->detailRental
            ->where('jenis_item', 'jual')
            ->sum('subtotal'));
    $jumlahRentalTerlambat = $allRental
        ->filter(fn ($r) => $r->effective_status === 'terlambat')
        ->count();
    $totalPelanggan = Rental::whereDate('tgl_rental', $today)
        ->distinct('pelanggan_id')
        ->count('pelanggan_id');
    $rentalTerbaru = Rental::with('pelanggan', 'detailRental')->latest()->take(5)->get();
    $stokAlat = Alat::with('kategori')->latest()->take(5)->get();

    if ($user->role === 'owner') {
        $validated = $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);
        $bulanDipilih = $validated['bulan'] ?? now()->format('Y-m');
        $awalPeriode = Carbon::createFromFormat('Y-m-d', $bulanDipilih.'-01')->startOfMonth();
        $akhirPeriode = $awalPeriode->copy()->endOfMonth();
        $awalTanggal = $awalPeriode->toDateString();
        $akhirTanggal = $akhirPeriode->toDateString();

        $transaksiPeriode = $allRental->filter(
            fn (Rental $item) => $item->tgl_rental >= $awalTanggal && $item->tgl_rental <= $akhirTanggal
        );
        $rentalPeriode = $transaksiPeriode->filter(
            fn (Rental $item) => $item->detailRental->isEmpty()
                || $item->detailRental->contains(fn (DetailRental $detail) => $detail->jenis_item !== 'jual')
        );

        $pemasukanSewa = 0;
        $pemasukanPenjualan = 0;
        foreach ($transaksiPeriode as $transaksi) {
            if ($transaksi->detailRental->isEmpty()) {
                $pemasukanSewa += $transaksi->total_bayar;
                continue;
            }

            foreach ($transaksi->detailRental as $detail) {
                if ($detail->jenis_item === 'jual') {
                    $pemasukanPenjualan += $detail->subtotal;
                } else {
                    $pemasukanSewa += $detail->subtotal;
                }
            }
        }

        $pemasukanDenda = $allRental
            ->filter(fn (Rental $item) => $item->tgl_kembali_aktual >= $awalTanggal
                && $item->tgl_kembali_aktual <= $akhirTanggal)
            ->sum('denda');
        $pemasukanHarian = [];
        for ($tanggal = $awalPeriode->copy(); $tanggal->lte($akhirPeriode); $tanggal->addDay()) {
            $pemasukanHarian[$tanggal->toDateString()] = 0;
        }
        foreach ($transaksiPeriode as $transaksi) {
            $pemasukanHarian[$transaksi->tgl_rental] += $transaksi->total_bayar;
        }
        foreach ($allRental as $transaksi) {
            if ($transaksi->denda > 0 && $transaksi->tgl_kembali_aktual
                && $transaksi->tgl_kembali_aktual >= $awalTanggal
                && $transaksi->tgl_kembali_aktual <= $akhirTanggal) {
                $pemasukanHarian[$transaksi->tgl_kembali_aktual] += $transaksi->denda;
            }
        }

        $alatTerlaris = $transaksiPeriode
            ->flatMap(fn (Rental $transaksi) => $transaksi->detailRental
                ->filter(fn (DetailRental $detail) => $detail->jenis_item !== 'jual')
                ->map(fn (DetailRental $detail) => [
                    'nama' => $detail->alat->nama_alat ?? 'Alat tidak tersedia',
                    'ukuran' => $detail->ukuran ?: ($detail->alat->ukuran ?? ''),
                    'jumlah' => $detail->jumlah,
                ]))
            ->groupBy(fn (array $detail) => $detail['nama'].'|'.$detail['ukuran'])
            ->map(fn ($items) => [
                'nama' => $items->first()['nama'],
                'ukuran' => $items->first()['ukuran'],
                'jumlah' => $items->sum('jumlah'),
            ])
            ->sortByDesc('jumlah')
            ->take(5)
            ->values();

        $alatSewa = $alatUntukKetersediaan->reject(fn (Alat $item) => $item->jenis_alat === 'jual');
        $jumlahAlatTersediaOwner = array_sum(app(InventoryAvailability::class)->availableQuantities(
            $alatSewa,
            $today,
            $today
        ));
        $jumlahPencucianOwner = (int) $alatSewa->sum('stok_pencucian');
        $rentalBerjalanOwner = $allRental->filter(fn (Rental $item) => $item->tgl_rental <= $today
            && in_array($item->effective_status, ['dipinjam', 'terlambat'], true));

        return view('dashboard', [
            'isOwnerDashboard' => true,
            'bulanDipilih' => $bulanDipilih,
            'namaBulanDipilih' => $awalPeriode->format('m/Y'),
            'pemasukanSewaOwner' => $pemasukanSewa,
            'pemasukanPenjualanOwner' => $pemasukanPenjualan,
            'pemasukanDendaOwner' => $pemasukanDenda,
            'totalPemasukanOwner' => $pemasukanSewa + $pemasukanPenjualan + $pemasukanDenda,
            'jumlahRentalOwner' => $rentalPeriode->count(),
            'jumlahPelangganOwner' => $rentalPeriode->pluck('pelanggan_id')->filter()->unique()->count(),
            'jumlahRentalBerjalanOwner' => $rentalBerjalanOwner->count(),
            'jumlahUnitDirentalOwner' => $rentalBerjalanOwner->sum(
                fn (Rental $item) => $item->detailRental->where('jenis_item', '!=', 'jual')->sum('jumlah')
            ),
            'jumlahTerlambatOwner' => $allRental->filter(fn (Rental $item) => $item->effective_status === 'terlambat')->count(),
            'jumlahAlatTersediaOwner' => $jumlahAlatTersediaOwner,
            'jumlahPencucianOwner' => $jumlahPencucianOwner,
            'alatTerlarisOwner' => $alatTerlaris,
            'pemasukanHarianOwner' => $pemasukanHarian,
        ]);
    }

    return view('dashboard', compact(
        'totalAlat',
        'alatTersedia',
        'sedangDirental',
        'pendapatanBulanIni',
        'penjualanBulanIni',
        'jumlahRentalTerlambat',
        'totalPelanggan',
        'rentalTerbaru',
        'stokAlat'
    ));
})->name('dashboard');

Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

Route::get('/alat', [AlatController::class, 'index'])->name('alat.index');
Route::post('/alat', [AlatController::class, 'store'])->name('alat.store');
Route::put('/alat/{id}', [AlatController::class, 'update'])->name('alat.update');
Route::put('/alat/{id}/pencucian', [AlatController::class, 'markClean'])->name('alat.pencucian');
Route::delete('/alat/{id}', [AlatController::class, 'destroy'])->name('alat.destroy');

Route::get('/user', [UserController::class, 'index'])->name('user.index');
Route::post('/user', [UserController::class, 'store'])->name('user.store');
Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])->name('pelanggan.update');
Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

Route::get('/rental', [RentalController::class, 'index'])->name('rental.index');
Route::get('/rental/ketersediaan', [RentalController::class, 'availability'])->name('rental.availability');
Route::post('/rental', [RentalController::class, 'store'])->name('rental.store');
Route::put('/rental/{id}/identitas', [RentalController::class, 'updateIdentityDeposit'])->name('rental.identitas.update');
Route::delete('/rental/{id}', [RentalController::class, 'destroy'])->name('rental.destroy');
Route::get('/laporan/rental', [RentalReportController::class, 'index'])->name('laporan.rental');
Route::get('/laporan/rental/export/pdf', [RentalReportController::class, 'exportPdf'])->name('laporan.rental.export.pdf');
Route::get('/laporan/rental/export/excel', [RentalReportController::class, 'exportExcel'])->name('laporan.rental.export.excel');

Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
Route::post('/pengembalian/{id}', [PengembalianController::class, 'proses'])->name('pengembalian.proses');
Route::put('/pengembalian/{id}/identitas', [PengembalianController::class, 'updateIdentitas'])->name('pengembalian.identitas.update');
