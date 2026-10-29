<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailRental extends Model
{
    protected $table = 'detail_rental';
    protected $fillable = [
        'rental_id',
        'alat_id',
        'jumlah',
        'jenis_item',
        'ukuran',
        'harga_sewa',
        'subtotal'
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class);
    }
}