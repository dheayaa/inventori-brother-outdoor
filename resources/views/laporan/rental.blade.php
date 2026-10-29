@extends('layouts.app')

@section('title', 'Laporan Rental')
@section('page-title', 'Laporan Rental')

@section('styles')
    .rental-detail { min-width: 180px; }
    .rental-detail summary { cursor: pointer; color: #8a4b08; font-weight: 600; }
    .rental-detail table { margin-top: 10px; min-width: 500px; }
    .rental-detail th, .rental-detail td { padding: 7px; }
    .rental-detail-total { text-align: right; margin-top: 10px; line-height: 1.8; }
    .report-print-footer { display:none; }
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        html, body { width: 100% !important; min-width: 0 !important; background: #fff !important; }
        body { display: block !important; font-size: 8px !important; }
        .rental-detail summary { display: none; }
        .sidebar, .topbar, .report-filters, .report-print { display: none !important; }
        .main { display: block !important; margin: 0 !important; width: 100% !important; min-height: 0 !important; overflow: visible !important; }
        .content { padding: 0 !important; width: 100% !important; }
        .section, .card { box-shadow: none !important; border-radius: 0 !important; }
        .section { padding: 8px 0 !important; margin: 0 0 8px !important; break-inside: auto; }
        .section h2 { font-size: 11px !important; margin-bottom: 6px !important; }
        .cards { grid-template-columns: repeat(5, 1fr) !important; gap: 6px !important; margin-bottom: 8px !important; }
        .card { padding: 7px !important; border-left-width: 2px !important; }
        .card h3 { font-size: 8px !important; margin-bottom: 3px !important; }
        .card .number { font-size: 14px !important; }
        .card .icon { display: none !important; }
        .report-table-wrap { width: 100% !important; overflow: visible !important; }
        .report-table-wrap > table { width: 100% !important; table-layout: fixed !important; font-size: 6.5px !important; }
        .report-table-wrap th, .report-table-wrap td { padding: 3px 2px !important; overflow-wrap: anywhere !important; word-break: break-word !important; }
        .report-table-wrap tr { break-inside: avoid; }
        .rental-detail { min-width: 0 !important; }
        .rental-detail:not([open]) > *:not(summary) { display: block !important; }
        .rental-detail table { width: 100% !important; min-width: 0 !important; table-layout: fixed !important; font-size: 6px !important; margin-top: 2px !important; }
        .rental-detail th, .rental-detail td { padding: 2px 1px !important; }
        .rental-detail-total { margin-top: 3px !important; line-height: 1.3 !important; }
        .report-print-footer { display:block !important; margin-top:24px; width:100%; text-align:center; line-height:1.6; }
        .report-print-footer strong { display:block; margin-top:36px; }
        .badge { padding: 1px 3px !important; font-size: 6px !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
@endsection

@section('content')
<div class="section report-filters">
    <h2>🔎 Filter Laporan</h2>
    <form id="reportFilterForm" method="GET" action="{{ route('laporan.rental') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="dari">Tanggal Laporan Dari</label>
                <input id="dari" type="date" name="dari" value="{{ request('dari') }}">
            </div>
            <div class="form-group">
                <label for="sampai">Sampai Tanggal</label>
                <input id="sampai" type="date" name="sampai" value="{{ request('sampai') }}">
            </div>
            <div class="form-group">
                <label for="periode">Kelompok Laporan Pemasukan</label>
                <select id="periode" name="periode">
                    <option value="harian" @selected($periode === 'harian')>Harian</option>
                    <option value="bulanan" @selected($periode === 'bulanan')>Bulanan</option>
                    <option value="tahunan" @selected($periode === 'tahunan')>Tahunan</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" @selected(request('status') === 'dipinjam')>Dipinjam</option>
                    <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
                    <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
                </select>
            </div>
            <div class="form-group">
                <label for="namaPenandatanganInput">Nama Penandatangan (bisa diubah)</label>
                <input id="namaPenandatanganInput" type="text" name="nama_penandatangan" value="{{ $namaPenandatangan }}" maxlength="100" required>
            </div>
        </div>
        <br>
        <p style="color:#666; margin:0 0 15px;">Pembayaran sewa dicatat pada tanggal rental, sedangkan denda dicatat pada tanggal pengembalian. Total pemasukan sudah mencakup keduanya.</p>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <a href="{{ route('laporan.rental') }}" class="btn btn-info" style="display:inline-block; text-decoration:none;">Reset</a>
        <a id="exportPdfLink" href="{{ route('laporan.rental.export.pdf', request()->query()) }}" class="btn btn-danger" style="display:inline-block; text-decoration:none; margin-left:8px;">Export PDF</a>
        <a id="exportExcelLink" href="{{ route('laporan.rental.export.excel', request()->query()) }}" class="btn btn-success" style="display:inline-block; text-decoration:none; margin-left:8px;">Export Excel</a>
    </form>
</div>

<div class="cards">
    <div class="card">
        <h3>Jumlah Rental</h3>
        <div class="number">{{ $totalRental }}</div>
        <div class="icon">🤝</div>
    </div>
    <div class="card">
        <h3>Total Pelanggan</h3>
        <div class="number">{{ $totalPelanggan }}</div>
        <div class="icon">👥</div>
    </div>
    <div class="card">
        <h3>Total Pembayaran Sewa + Penjualan</h3>
        <div class="number" style="font-size:22px;">Rp {{ number_format($totalBayar, 0, ',', '.') }}</div>
        <div class="icon">💰</div>
    </div>
    <div class="card">
        <h3>Total Penjualan</h3>
        <div class="number" style="font-size:22px;">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
        <small style="color:#777;">Sudah termasuk dalam total pembayaran</small>
        <div class="icon">🛍️</div>
    </div>
    <div class="card">
        <h3>Denda Masuk</h3>
        <div class="number" style="font-size:22px;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div>
        <div class="icon">🧾</div>
    </div>
    <div class="card">
        <h3>Total Pemasukan</h3>
        <div class="number" style="font-size:22px;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
        <div class="icon">💵</div>
    </div>
</div>

<div class="section">
    <h2>📈 Riwayat Pemasukan {{ ucfirst($periode) }}</h2>
    <p style="color:#666; font-size:12px; margin:-5px 0 12px;">Jumlah rental dan pembayaran dihitung dari tanggal mulai rental sesuai kelompok periode. Total penjualan adalah bagian dari total pembayaran. Denda dicatat terpisah pada tanggal pengembalian.</p>
    <div class="report-table-wrap" style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Jumlah Rental</th>
                    <th>Pembayaran Sewa & Penjualan</th>
                    <th>Total Penjualan</th>
                    <th>Denda Masuk</th>
                    <th>Total Pemasukan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporanPemasukan as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['jumlah_rental'] }} rental</td>
                    <td>Rp {{ number_format($row['total_bayar'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['total_penjualan'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['total_denda'], 0, ',', '.') }}</td>
                    <td><strong>Rp {{ number_format($row['total_pemasukan'], 0, ',', '.') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#888;">Belum ada riwayat pemasukan untuk filter ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="section">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:15px;">
        <h2 style="margin-bottom:0;">📊 Rincian Rental</h2>
        <button type="button" class="btn btn-primary report-print" onclick="window.print()">Cetak</button>
    </div>
    <div class="report-table-wrap" style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Kode Rental</th>
                    <th>Jenis Transaksi</th>
                    <th>Rincian Alat</th>
                    <th>Tgl Rental</th>
                    <th>Rencana Kembali</th>
                    <th>Kembali Aktual</th>
                    <th>Identitas</th>
                    <th>Total Bayar</th>
                    <th>Denda</th>
                    <th>Total Tagihan</th>
                    <th>Status</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rental as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->pelanggan->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $r->kode_rental }}</td>
                    <td>{{ $r->jenis_transaksi === 'booking' ? 'Booking' : 'Rental Langsung' }}</td>
                    <td>
                        <details class="rental-detail">
                            <summary>Lihat {{ $r->detailRental->count() }} jenis alat</summary>
                            @if($r->detailRental->isNotEmpty())
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Nama Alat</th>
                                            <th>Jumlah</th>
                                            <th>Jenis Item</th>
                                            <th>Harga per Unit</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($r->detailRental as $detail)
                                        <tr>
                                            <td>
                                                {{ $detail->alat->nama_alat ?? 'Alat tidak tersedia' }} —
                                                Ukuran {{ $detail->ukuran ?: ($detail->alat->ukuran ?? 'belum diisi') }}
                                            </td>
                                            <td>{{ $detail->jumlah }}</td>
                                            <td>
                                                {{ $detail->jenis_item === 'jual' ? 'Dijual' : ($detail->jenis_item === 'gratis' ? 'Gratis' : 'Sewa') }}
                                            </td>
                                            <td>Rp {{ number_format($detail->harga_sewa, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>Tidak ada rincian alat.</p>
                            @endif
                            <div class="rental-detail-total">
                                <div>Total sewa + penjualan: <strong>Rp {{ number_format($r->total_bayar, 0, ',', '.') }}</strong></div>
                                <div>Denda: <strong>Rp {{ number_format($r->denda, 0, ',', '.') }}</strong></div>
                                <div>Total tagihan: <strong>Rp {{ number_format($r->total_bayar + $r->denda, 0, ',', '.') }}</strong></div>
                            </div>
                        </details>
                    </td>
                    <td>{{ $r->tgl_rental }}</td>
                    <td>{{ $r->tgl_kembali }}</td>
                    <td>{{ $r->tgl_kembali_aktual ?? '-' }}</td>
                    <td>
                        @if(!$r->identitas_dititipkan)
                            Tidak dititipkan
                        @elseif($r->identitas_dikembalikan)
                            Sudah dikembalikan
                        @else
                            Masih dititipkan
                        @endif
                    </td>
                    <td>Rp {{ number_format($r->total_bayar, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($r->denda, 0, ',', '.') }}</td>
                    <td><strong>Rp {{ number_format($r->total_bayar + $r->denda, 0, ',', '.') }}</strong></td>
                    @php($hanyaPenjualan = $r->detailRental->isNotEmpty() && $r->detailRental->every(fn ($detail) => $detail->jenis_item === 'jual'))
                    <td><span class="badge badge-{{ $hanyaPenjualan ? 'dikembalikan' : $r->effective_status }}">{{ $hanyaPenjualan ? 'Penjualan selesai' : ucfirst($r->effective_status) }}</span></td>
                    <td>{{ $r->user->nama ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="14" style="text-align:center; color:#888;">Tidak ada data rental untuk filter ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="report-print-footer">
    <div>Wonosobo, {{ now()->locale('id')->translatedFormat('j F Y') }}</div>
    <div>{{ ucfirst(session('user')->role) }},</div>
    <strong id="reportSignerName">{{ $namaPenandatangan }}</strong>
</div>
@endsection

@section('scripts')
<script>
    const reportForm = document.getElementById('reportFilterForm');
    const signerInput = document.getElementById('namaPenandatanganInput');
    const signerOutput = document.getElementById('reportSignerName');

    function updateReportExports() {
        signerOutput.textContent = signerInput.value.trim() || @json(session('user')->nama);
        const query = new URLSearchParams(new FormData(reportForm)).toString();

        document.getElementById('exportPdfLink').href = @json(route('laporan.rental.export.pdf')).concat('?', query);
        document.getElementById('exportExcelLink').href = @json(route('laporan.rental.export.excel')).concat('?', query);
    }

    reportForm.addEventListener('input', updateReportExports);
    reportForm.addEventListener('change', updateReportExports);
    updateReportExports();
</script>
@endsection