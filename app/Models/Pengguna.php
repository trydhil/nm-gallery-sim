<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengguna extends Model
{
    use HasFactory;

    protected $table      = 'pengguna';
    protected $primaryKey = 'id_user';
    protected $fillable   = [
        'username', 'password', 'nama_lengkap', 'role', 'email', 'foto'
    ];

    protected $hidden = ['password'];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_user', 'id_user');
    }
}