<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RentalReportController extends Controller
{
    public function index(Request $request)
    {
        $user = session('user');
        if (! $user) {
            return redirect()->route('login');
        }
        if (! in_array($user->role, ['admin', 'owner'])) {
            return redirect()->route('dashboard');
        }

        $namaPenandatangan = $this->getSignerName($request, $user->nama);
        $filters = $this->validateFilters($request);
        $rental = $this->getFilteredRental($filters);
        $rentalSaja = $rental->filter(fn (Rental $item) => $this->hasRentalItem($item));
        $laporanPemasukan = $this->getFinancialSummary($filters);
        $totalBayar = $laporanPemasukan->sum('total_bayar');
        $totalPenjualan = $laporanPemasukan->sum('total_penjualan');
        $totalDenda = $laporanPemasukan->sum('total_denda');

        return view('laporan.rental', [
            'rental' => $rental,
            'totalRental' => $rentalSaja->count(),
            'totalPelanggan' => $rentalSaja->pluck('pelanggan_id')->filter()->unique()->count(),
            'totalBayar' => $totalBayar,
            'totalPenjualan' => $totalPenjualan,
            'totalDenda' => $totalDenda,
            'totalPemasukan' => $totalBayar + $totalDenda,
            'laporanPemasukan' => $laporanPemasukan,
            'periode' => $filters['periode'],
            'namaPenandatangan' => $namaPenandatangan,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = session('user');
        if (! $user || ! in_array($user->role, ['admin', 'owner'])) {
            return redirect()->route('login');
        }

        $namaPenandatangan = $this->getSignerName($request, $user->nama);
        $filters = $this->validateFilters($request);
        $rental = $this->getFilteredRental($filters);
        $rentalSaja = $rental->filter(fn (Rental $item) => $this->hasRentalItem($item));
        $laporanPemasukan = $this->getFinancialSummary($filters);
        $totalBayar = $laporanPemasukan->sum('total_bayar');
        $totalPenjualan = $laporanPemasukan->sum('total_penjualan');
        $totalDenda = $laporanPemasukan->sum('total_denda');

        $pdf = Pdf::loadView('laporan.rental-export', [
            'rental' => $rental,
            'totalRental' => $rentalSaja->count(),
            'totalPelanggan' => $rentalSaja->pluck('pelanggan_id')->filter()->unique()->count(),
            'totalBayar' => $totalBayar,
            'totalPenjualan' => $totalPenjualan,
            'totalDenda' => $totalDenda,
            'totalPemasukan' => $totalBayar + $totalDenda,
            'laporanPemasukan' => $laporanPemasukan,
            'dari' => $filters['dari'] ?? null,
            'sampai' => $filters['sampai'] ?? null,
            'status' => $filters['status'] ?? null,
            'periode' => $filters['periode'],
            'printedBy' => $namaPenandatangan,
            'printedByRole' => ucfirst($user->role),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-rental-'.now()->format('YmdHis').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = session('user');
        if (! $user || ! in_array($user->role, ['admin', 'owner'])) {
            return redirect()->route('login');
        }

        $filters = $this->validateFilters($request);
        $rental = $this->getFilteredRental($filters);
        $laporanPemasukan = $this->getFinancialSummary($filters);
        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, ['Ringkasan Pemasukan ('.ucfirst($filters['periode']).')']);
        fputcsv($csv, ['Periode', 'Jumlah Rental', 'Pembayaran Sewa & Penjualan', 'Total Penjualan', 'Denda Masuk', 'Total Pemasukan']);
        foreach ($laporanPemasukan as $row) {
            fputcsv($csv, [
                $row['label'],
                $row['jumlah_rental'],
                $row['total_bayar'],
                $row['total_penjualan'],
                $row['total_denda'],
                $row['total_pemasukan'],
            ]);
        }
        fputcsv($csv, []);
        fputcsv($csv, ['No', 'Pelanggan', 'Kode Rental', 'Jenis Transaksi', 'Tanggal Rental', 'Tanggal Kembali', 'Tanggal Kembali Aktual', 'Status Identitas', 'Rincian Alat/Barang (Jenis, Jumlah, Harga/Unit, Subtotal)', 'Total Bayar', 'Denda', 'Total Tagihan', 'Status', 'Petugas']);

        foreach ($rental as $index => $item) {
            $statusIdentitas = ! $item->identitas_dititipkan
                ? 'Tidak dititipkan'
                : ($item->identitas_dikembalikan ? 'Sudah dikembalikan' : 'Masih dititipkan');

            $rincianAlat = $item->detailRental->map(function ($detail) {
                return sprintf(
                    '%s%s: %s unit x Rp %s = Rp %s',
                    $detail->alat->nama_alat ?? 'Alat tidak tersedia',
                    ' (Ukuran '.($detail->ukuran ?: ($detail->alat->ukuran ?? 'belum diisi')).')',
                    $detail->jumlah,
                    number_format($detail->harga_sewa, 0, ',', '.'),
                    number_format($detail->subtotal, 0, ',', '.')
                ).' ['.($detail->jenis_item === 'jual' ? 'Dijual' : ($detail->jenis_item === 'gratis' ? 'Gratis' : 'Sewa')).']';
            })->implode("\n");

            fputcsv($csv, [
                $index + 1,
                $item->pelanggan->nama_pelanggan ?? '-',
                $item->kode_rental,
                $item->jenis_transaksi === 'booking' ? 'Booking' : 'Rental Langsung',
                $item->tgl_rental,
                $item->tgl_kembali,
                $item->tgl_kembali_aktual ?? '-',
                $statusIdentitas,
                $rincianAlat ?: '-',
                $item->total_bayar,
                $item->denda,
                $item->total_bayar + $item->denda,
                $item->detailRental->isNotEmpty() && $item->detailRental->every(fn ($detail) => $detail->jenis_item === 'jual')
                    ? 'Penjualan selesai'
                    : $item->effective_status,
                $item->user->nama ?? '-',
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="laporan-rental-'.now()->format('YmdHis').'.csv"');
    }

    private function validateFilters(Request $request): array
    {
        $filters = $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date|after_or_equal:dari',
            'status' => 'nullable|in:dipinjam,dikembalikan,terlambat',
            'periode' => 'nullable|in:harian,bulanan,tahunan',
        ]);

        $filters['periode'] = $filters['periode'] ?? 'harian';

        return $filters;
    }

    private function getSignerName(Request $request, string $default): string
    {
        $data = $request->validate([
            'nama_penandatangan' => ['nullable', 'string', 'max:100'],
        ]);

        return trim($data['nama_penandatangan'] ?? '') ?: $default;
    }

    private function getFilteredRental(array $filters)
    {
        $rental = Rental::with('pelanggan', 'user', 'detailRental.alat')
            ->when($filters['dari'] ?? null, fn ($query, $date) => $query->whereDate('tgl_rental', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($query, $date) => $query->whereDate('tgl_rental', '<=', $date))
            ->latest()
            ->get();

        return $rental->filter(function ($item) use ($filters) {
            if (! empty($filters['dari']) && $item->tgl_rental < $filters['dari']) {
                return false;
            }

            if (! empty($filters['sampai']) && $item->tgl_rental > $filters['sampai']) {
                return false;
            }

            if (! empty($filters['status']) && $item->effective_status !== $filters['status']) {
                return false;
            }

            return true;
        })->values();
    }

    private function getFinancialSummary(array $filters)
    {
        $periode = $filters['periode'] ?? 'harian';
        $buckets = [];
        $rentals = Rental::with('detailRental:id,rental_id,jenis_item,subtotal')->get([
            'id',
            'tgl_rental',
            'tgl_kembali_aktual',
            'total_bayar',
            'denda',
            'status',
            'tgl_kembali',
        ]);

        foreach ($rentals as $rental) {
            if (! empty($filters['status']) && $rental->effective_status !== $filters['status']) {
                continue;
            }

            if ($this->dateMatchesFilters($rental->tgl_rental, $filters)) {
                $key = $this->getPeriodKey($rental->tgl_rental, $periode);
                $buckets[$key]['total_bayar'] = ($buckets[$key]['total_bayar'] ?? 0) + $rental->total_bayar;
                $buckets[$key]['total_penjualan'] = ($buckets[$key]['total_penjualan'] ?? 0)
                    + $rental->detailRental
                        ->where('jenis_item', 'jual')
                        ->sum('subtotal');
                if ($this->hasRentalItem($rental)) {
                    $buckets[$key]['jumlah_rental'] = ($buckets[$key]['jumlah_rental'] ?? 0) + 1;
                }
            }

            if ($rental->denda > 0 && $rental->tgl_kembali_aktual
                && $this->dateMatchesFilters($rental->tgl_kembali_aktual, $filters)) {
                $key = $this->getPeriodKey($rental->tgl_kembali_aktual, $periode);
                $buckets[$key]['total_denda'] = ($buckets[$key]['total_denda'] ?? 0) + $rental->denda;
            }
        }

        krsort($buckets);

        return collect($buckets)->map(function (array $amounts, string $key) use ($periode) {
            $totalBayar = $amounts['total_bayar'] ?? 0;
            $totalPenjualan = $amounts['total_penjualan'] ?? 0;
            $totalDenda = $amounts['total_denda'] ?? 0;
            $jumlahRental = $amounts['jumlah_rental'] ?? 0;

            return [
                'key' => $key,
                'label' => match ($periode) {
                    'bulanan' => substr($key, 5, 2).'/'.substr($key, 0, 4),
                    'tahunan' => $key,
                    default => $key,
                },
                'jumlah_rental' => $jumlahRental,
                'total_bayar' => $totalBayar,
                'total_penjualan' => $totalPenjualan,
                'total_denda' => $totalDenda,
                'total_pemasukan' => $totalBayar + $totalDenda,
            ];
        })->values();
    }

    private function hasRentalItem(Rental $rental): bool
    {
        return $rental->detailRental->isEmpty()
            || $rental->detailRental->contains(fn ($detail) => $detail->jenis_item !== 'jual');
    }

    private function getPeriodKey(string $date, string $periode): string
    {
        return match ($periode) {
            'bulanan' => substr($date, 0, 7),
            'tahunan' => substr($date, 0, 4),
            default => substr($date, 0, 10),
        };
    }

    private function dateMatchesFilters(string $date, array $filters): bool
    {
        $date = substr($date, 0, 10);

        return (! isset($filters['dari']) || $date >= $filters['dari'])
            && (! isset($filters['sampai']) || $date <= $filters['sampai']);
    }
}
