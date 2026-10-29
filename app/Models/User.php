<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    
    protected $fillable = [
        'nama',
        'username', 
        'no_hp',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];
}