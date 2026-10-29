<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $fillable = [
        'nama_pelanggan',
        'no_hp',
        'nik',
        'alamat'
    ];

    public function rental()
    {
        return $this->hasMany(Rental::class);
    }
}