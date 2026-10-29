<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    protected $table = 'alat';
    protected $fillable = [
        'kategori_id',
        'nama_alat',
        'ukuran',
        'jenis_alat',
        'kode_alat',
        'stok_total',
        'stok_tersedia',
        'stok_pencucian',
        'harga_sewa',
        'harga_jual',
        'kondisi',
        'keterangan'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function detailRental()
    {
        return $this->hasMany(DetailRental::class);
    }
}