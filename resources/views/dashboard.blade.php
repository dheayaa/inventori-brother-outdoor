@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('styles')
    .owner-analytics-grid { display:grid; grid-template-columns:minmax(0, 1.4fr) minmax(320px, 1fr); gap:20px; align-items:stretch; }
    .owner-analytics-grid .section { min-width:0; }
    @media (max-width:1000px) { .owner-analytics-grid { grid-template-columns:1fr; } }
@endsection

@section('content')
@if(session('user')->role === 'owner')
<h2 style="font-size:16px; color:#2C1A00; margin:0 0 14px;">📊 Statistik Usaha — {{ $namaBulanDipilih }}</h2>
<div class="cards">
    <div class="card">
        <h3>Total Pemasukan</h3>
        <div class="number" style="font-size:23px;">Rp {{ number_format($totalPemasukanOwner, 0, ',', '.') }}</div>
        <small style="color:#777;">Sewa + penjualan + denda</small>
        <div class="icon">💰</div>
    </div>
    <div class="card">
        <h3>Pemasukan Sewa</h3>
        <div class="number" style="font-size:23px;">Rp {{ number_format($pemasukanSewaOwner, 0, ',', '.') }}</div>
        <div class="icon">🎒</div>
    </div>
    <div class="card">
        <h3>Pemasukan Penjualan</h3>
        <div class="number" style="font-size:23px;">Rp {{ number_format($pemasukanPenjualanOwner, 0, ',', '.') }}</div>
        <small style="color:#777;">Sudah termasuk dalam total pemasukan</small>
        <div class="icon">🛍️</div>
    </div>
    <div class="card">
        <h3>Pemasukan Denda</h3>
        <div class="number" style="font-size:23px;">Rp {{ number_format($pemasukanDendaOwner, 0, ',', '.') }}</div>
        <div class="icon">🧾</div>
    </div>
    <div class="card">
        <h3>Jumlah Rental</h3>
        <div class="number">{{ $jumlahRentalOwner }}</div>
        <div class="icon">📋</div>
    </div>
    <div class="card">
        <h3>Pelanggan Rental</h3>
        <div class="number">{{ $jumlahPelangganOwner }}</div>
        <div class="icon">👥</div>
    </div>
    <div class="card">
        <h3>Rental Berjalan</h3>
        <div class="number">{{ $jumlahRentalBerjalanOwner }} <small style="font-size:14px;">({{ $jumlahUnitDirentalOwner }} unit)</small></div>
        <div class="icon">⛺</div>
    </div>
    <div class="card">
        <h3>Rental Terlambat</h3>
        <div class="number">{{ $jumlahTerlambatOwner }}</div>
        <div class="icon">⏰</div>
    </div>
    <div class="card">
        <h3>Siap Disewa Hari Ini</h3>
        <div class="number">{{ $jumlahAlatTersediaOwner }}</div>
        <div class="icon">✅</div>
    </div>
    <div class="card">
        <h3>Dalam Pencucian</h3>
        <div class="number">{{ $jumlahPencucianOwner }}</div>
        <div class="icon">🧼</div>
    </div>
</div>

<div class="section" style="padding:14px 18px;">
    <form method="GET" action="{{ route('dashboard') }}" style="display:flex; align-items:end; gap:12px; flex-wrap:wrap;">
        <div class="form-group" style="max-width:220px;">
            <label for="bulan">Ubah periode statistik</label>
            <input type="month" id="bulan" name="bulan" value="{{ $bulanDipilih }}" max="{{ now()->format('Y-m') }}">
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
</div>

<div class="owner-analytics-grid">
<div class="section">
    <h2>📈 Pemasukan Harian — {{ $namaBulanDipilih }}</h2>
    <p style="color:#666; font-size:12px; margin:-5px 0 14px;">Grafik menggabungkan pembayaran sewa/penjualan berdasarkan tanggal transaksi dan denda berdasarkan tanggal pengembalian.</p>
    @php($nilaiGrafikMaksimum = max($pemasukanHarianOwner ?: [0]))
    <div style="display:flex; align-items:flex-end; gap:5px; min-height:220px; overflow-x:auto; padding:12px 4px 0; border-bottom:1px solid #ddd;">
        @foreach($pemasukanHarianOwner as $tanggal => $pemasukan)
            @php($tinggiGrafik = $nilaiGrafikMaksimum > 0 ? max(4, (int) round(($pemasukan / $nilaiGrafikMaksimum) * 160)) : 4)
            <div title="{{ $tanggal }}: Rp {{ number_format($pemasukan, 0, ',', '.') }}" style="flex:1; min-width:19px; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; height:195px;">
                <div style="width:100%; max-width:30px; height:{{ $tinggiGrafik }}px; background:#F5A623; border-radius:4px 4px 0 0;"></div>
                <small style="font-size:9px; color:#666; margin-top:4px;">{{ substr($tanggal, -2) }}</small>
            </div>
        @endforeach
    </div>
    <small style="display:block; text-align:center; color:#777; margin-top:7px;">Tanggal dalam bulan</small>
</div>

<div class="section">
    <h2>🏆 Alat Paling Sering Disewa — {{ $namaBulanDipilih }}</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Nama Alat</th><th>Ukuran</th><th>Jumlah Disewa</th></tr>
        </thead>
        <tbody>
            @forelse($alatTerlarisOwner as $alatTerlaris)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $alatTerlaris['nama'] }}</td>
                <td>{{ $alatTerlaris['ukuran'] ?: '-' }}</td>
                <td>{{ $alatTerlaris['jumlah'] }} unit</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:#888;">Belum ada data penyewaan pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@else
<div class="cards">
    <div class="card">
        <h3>Total Alat</h3>
        <div class="number">{{ $totalAlat }}</div>
        <div class="icon">⛺</div>
    </div>
    <div class="card">
        <h3>Alat Tersedia</h3>
        <div class="number">{{ $alatTersedia }}</div>
        <div class="icon">✅</div>
    </div>
    <div class="card">
        <h3>Sedang Dirental</h3>
        <div class="number">{{ $sedangDirental }}</div>
        <div class="icon">🎒</div>
    </div>
    <div class="card">
        <h3>Total Pemasukan Bulan Ini</h3>
        <div class="number" style="font-size:24px;">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
        <small style="color:#777;">Sewa + penjualan + denda</small>
        <div class="icon">💰</div>
    </div>
    <div class="card">
        <h3>Total Penjualan Bulan Ini</h3>
        <div class="number" style="font-size:24px;">Rp {{ number_format($penjualanBulanIni, 0, ',', '.') }}</div>
        <small style="color:#777;">Sudah termasuk dalam total pemasukan</small>
        <div class="icon">🛍️</div>
    </div>
    <div class="card">
        <h3>Rental Terlambat</h3>
        <div class="number">{{ $jumlahRentalTerlambat }}</div>
        <div class="icon">⏰</div>
    </div>
    <div class="card">
        <h3>Total Pelanggan Hari Ini</h3>
        <div class="number">{{ $totalPelanggan }}</div>
        <div class="icon">👥</div>
    </div>
</div>

<div class="section">
    <h2>🤝 Rental Terbaru</h2>
    <table>
        <thead>
            <tr>
                <th>Kode Rental</th>
                <th>Pelanggan</th>
                <th>Tgl Rental</th>
                <th>Tgl Kembali</th>
                <th>Total Bayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rentalTerbaru as $rental)
            <tr>
                <td>{{ $rental->kode_rental }}</td>
                <td>{{ $rental->pelanggan->nama_pelanggan }}</td>
                <td>{{ $rental->tgl_rental }}</td>
                <td>{{ $rental->tgl_kembali }}</td>
                <td>Rp {{ number_format($rental->total_bayar, 0, ',', '.') }}</td>
                @php($hanyaPenjualan = $rental->detailRental->isNotEmpty() && $rental->detailRental->every(fn ($detail) => $detail->jenis_item === 'jual'))
                <td><span class="badge badge-{{ $hanyaPenjualan ? 'dikembalikan' : $rental->effective_status }}">{{ $hanyaPenjualan ? 'Penjualan selesai' : ucfirst($rental->effective_status) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:#888;">Belum ada data rental</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h2>📦 Stok Alat</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Alat</th>
                <th>Kategori</th>
                <th>Stok Total</th>
                <th>Stok Tersedia</th>
                <th>Harga Sewa / Jual</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stokAlat as $alat)
            <tr>
                <td>{{ $alat->nama_alat }}</td>
                <td>{{ $alat->kategori->nama_kategori }}</td>
                <td>{{ $alat->stok_total }}</td>
                <td>{{ $alat->stok_tersedia }}</td>
                <td>Rp {{ number_format($alat->jenis_alat === 'jual' ? ($alat->harga_jual ?? 0) : $alat->harga_sewa, 0, ',', '.') }}</td>
                <td>{{ ucfirst($alat->kondisi) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:#888;">Belum ada data alat</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif
@endsection