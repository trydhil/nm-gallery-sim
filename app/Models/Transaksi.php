<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_pelanggan',
        'id_user',
        'tgl_sewa',
        'tgl_jatuh_tempo',
        'tgl_kembali',
        'total_biaya',
        'total_denda',
        'status_transaksi',
        // === FIELD BARU ===
        'metode_bayar',   // 'Lunas' atau 'DP'
        'jumlah_dp',      // jumlah yang dibayar di muka
        'sisa_tagihan',   // sisa yang harus dibayar saat kembali
    ];

    protected $casts = [
        'tgl_sewa'          => 'date',
        'tgl_jatuh_tempo'   => 'date',
        'tgl_kembali'       => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_user', 'id_user');
    }

    public function detailTransaksis()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_transaksi', 'id_transaksi');
    }
}