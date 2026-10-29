<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Rental extends Model
{
    protected $table = 'rental';

    protected $casts = [
        'identitas_dititipkan' => 'boolean',
        'identitas_dikembalikan' => 'boolean',
    ];

    protected $fillable = [
        'kode_rental',
        'pelanggan_id',
        'user_id',
        'tgl_rental',
        'tgl_kembali',
        'tgl_kembali_aktual',
        'total_bayar',
        'denda',
        'identitas_dititipkan',
        'identitas_dikembalikan',
        'status',
        'jenis_transaksi',
        'keterangan',
    ];

    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'dikembalikan') {
            return 'dikembalikan';
        }

        if ($this->tgl_kembali_aktual === null
            && $this->batas_waktu_kembali
            && now()->greaterThanOrEqualTo($this->batas_waktu_kembali)) {
            return 'terlambat';
        }

        if ($this->status === 'terlambat') {
            return 'terlambat';
        }

        return $this->status ?? 'dipinjam';
    }

    public function getBatasWaktuKembaliAttribute(): ?Carbon
    {
        if (! $this->tgl_kembali) {
            return null;
        }

        $batasWaktu = Carbon::parse($this->tgl_kembali, config('app.timezone'))->startOfDay();
        if (in_array($batasWaktu->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true)) {
            return $batasWaktu->addDay();
        }

        return $batasWaktu->setTime(23, 0);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detailRental()
    {
        return $this->hasMany(DetailRental::class);
    }
}
