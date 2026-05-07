<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftTransaksi extends Model
{
    use HasFactory;

    protected $table = 'draft_transaksi';
    protected $primaryKey = 'id_draft';

    protected $fillable = [
        'id_user',
        'nama_pelanggan',
        'no_telp',
        'alamat',
        'id_barang',
        'ukuran_dipilih',
        'tgl_sewa',
        'tgl_jatuh_tempo',
        'total_biaya',
        'metode_bayar',
        'jumlah_dp',
        'catatan',
    ];

    protected $casts = [
        'tgl_sewa'          => 'datetime',
        'tgl_jatuh_tempo'   => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_user', 'id_user');
    }
}