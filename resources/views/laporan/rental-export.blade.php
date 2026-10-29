<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rental</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        h2 { margin-bottom: 12px; }
        .meta { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #2C1A00; color: white; }
        .summary { margin-bottom: 10px; }
        .print-footer { width: 100%; margin-top: 24px; text-align: center; line-height: 1.6; }
        .print-footer table { width: 100%; table-layout: fixed; }
        .print-footer td { width: 33.33%; border: 0; text-align: center; vertical-align: top; }
        .print-footer strong { display: block; margin-top: 36px; }
    </style>
</head>
<body>
    <h2>Laporan Rental</h2>

    <div class="meta">
        @if($dari || $sampai || $status)
            <strong>Filter:</strong>
            @if($dari) Dari: {{ $dari }} @endif
            @if($sampai) Sampai: {{ $sampai }} @endif
            @if($status) Status: {{ ucfirst($status) }} @endif
        @else
            <strong>Filter:</strong> Semua data
        @endif
    </div>

    <div class="summary">
        <strong>Jumlah Rental:</strong> {{ $totalRental }} |
        <strong>Total Pelanggan:</strong> {{ $totalPelanggan }} |
        <strong>Pembayaran Sewa & Penjualan:</strong> Rp {{ number_format($totalBayar, 0, ',', '.') }} |
        <strong>Total Penjualan (termasuk dalam pembayaran):</strong> Rp {{ number_format($totalPenjualan, 0, ',', '.') }} |
        <strong>Denda Masuk:</strong> Rp {{ number_format($totalDenda, 0, ',', '.') }} |
        <strong>Total Pemasukan:</strong> Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
    </div>

    <h3>Riwayat Pemasukan {{ ucfirst($periode) }}</h3>
    <p>Jumlah rental dihitung dari tanggal mulai rental sesuai kelompok periode; denda dicatat pada tanggal pengembalian.</p>
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
                    <td>{{ $row['jumlah_rental'] }}</td>
                    <td>Rp {{ number_format($row['total_bayar'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['total_penjualan'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['total_denda'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['total_pemasukan'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">Belum ada riwayat pemasukan</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Rincian Rental</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pelanggan</th>
                <th>Kode</th>
                <th>Jenis Transaksi</th>
                <th>Rincian Alat (Jumlah, Harga Sewa, Subtotal)</th>
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
            @forelse($rental as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->pelanggan->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $item->kode_rental }}</td>
                    <td>{{ $item->jenis_transaksi === 'booking' ? 'Booking' : 'Rental Langsung' }}</td>
                    <td>
                        @foreach($item->detailRental as $detail)
                            {{ $detail->jenis_item === 'jual' ? 'Dibeli: ' : ($detail->jenis_item === 'gratis' ? 'Gratis: ' : 'Disewa: ') }}{{ $detail->alat->nama_alat ?? 'Alat tidak tersedia' }} — Ukuran {{ $detail->ukuran ?: ($detail->alat->ukuran ?? 'belum diisi') }} — {{ $detail->jumlah }} unit × Rp {{ number_format($detail->harga_sewa, 0, ',', '.') }} = Rp {{ number_format($detail->subtotal, 0, ',', '.') }}@if(!$loop->last)<br>@endif
                        @endforeach
                    </td>
                    <td>{{ $item->tgl_rental }}</td>
                    <td>{{ $item->tgl_kembali }}</td>
                    <td>{{ $item->tgl_kembali_aktual ?? '-' }}</td>
                    <td>
                        @if(!$item->identitas_dititipkan)
                            Tidak dititipkan
                        @elseif($item->identitas_dikembalikan)
                            Sudah dikembalikan
                        @else
                            Masih dititipkan
                        @endif
                    </td>
                    <td>Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->denda, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_bayar + $item->denda, 0, ',', '.') }}</td>
                    @php($hanyaPenjualan = $item->detailRental->isNotEmpty() && $item->detailRental->every(fn ($detail) => $detail->jenis_item === 'jual'))
                    <td>{{ $hanyaPenjualan ? 'Penjualan selesai' : ucfirst($item->effective_status) }}</td>
                    <td>{{ $item->user->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align:center;">Tidak ada data rental</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="print-footer">
        <div>Wonosobo, {{ now()->locale('id')->translatedFormat('j F Y') }}</div>
        <div>{{ $printedByRole }},</div>
        <strong>{{ $printedBy }}</strong>
    </div>
</body>
</html>
