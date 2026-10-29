<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\DetailRental;
use Illuminate\Support\Collection;

class InventoryAvailability
{
    public function availableQuantities(Collection $alat, string $dari, string $sampai): array
    {
        $available = $alat->mapWithKeys(
            fn (Alat $item) => [$item->id => max(0, $item->stok_total - $item->stok_pencucian)]
        )->all();
        $today = now()->toDateString();

        $details = DetailRental::with('rental:id,tgl_rental,status')
            ->whereIn('alat_id', array_keys($available))
            ->get(['alat_id', 'rental_id', 'jumlah', 'jenis_item']);

        foreach ($details as $detail) {
            $rental = $detail->rental;
            if (! $rental || $rental->tgl_rental > $sampai) {
                continue;
            }

            if ($detail->jenis_item !== 'jual' && $rental->status === 'dikembalikan') {
                continue;
            }

            if ($rental->tgl_rental <= $sampai) {
                $available[$detail->alat_id] -= $detail->jumlah;
            }
        }

        return array_map(fn (int $quantity) => max(0, $quantity), $available);
    }
}
