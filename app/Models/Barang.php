<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    protected $fillable = [
        'nama_barang',
        'ukuran',
        'harga_sewa',
        'stok',
        'status_barang',
        'foto'
    ];

    // Ambil stok per ukuran (dari JSON)
    public function getStokPerUkuranAttribute()
    {
        return json_decode($this->stok, true) ?: [];
    }

    // Set stok per ukuran (ke JSON)
    public function setStokPerUkuranAttribute($value)
    {
        $this->stok = json_encode($value);
    }

    public function detailTransaksis()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_barang', 'id_barang');
    }


    public function syncStatusFromStok(): void
    {
        // Jangan ubah status jika sedang dalam kondisi khusus
        if (in_array($this->status_barang, ['Laundry', 'Rusak'])) {
            return;
        }

        $stokArray = $this->getStokPerUkuranAttribute();
        $totalStok = array_sum($stokArray);

        // Status hanya mengikuti stok total. Kategori "Disewa" untuk UI
        // dihitung terpisah dari transaksi aktif supaya barang bisa tetap
        // tampil sebagai Tersedia dan Disewa bersamaan saat stoknya masih ada.
        $this->status_barang = $totalStok > 0 ? 'Tersedia' : 'Disewa';
        $this->save();
    }

    /**
     * Kurangi stok untuk ukuran tertentu, lalu sync status otomatis.
     * 
    * Status mengikuti transaksi aktif dan sisa stok total.
     */
    public function kurangiStok($ukuran, $jumlah = 1): bool
    {
        $stokArray = $this->getStokPerUkuranAttribute();

        if (!isset($stokArray[$ukuran]) || $stokArray[$ukuran] < $jumlah) {
            return false; // Stok tidak cukup
        }

        $stokArray[$ukuran] -= $jumlah;
        $this->stok = json_encode($stokArray);

        // Simpan perubahan stok dulu, lalu sync status
        $this->save();
        $this->syncStatusFromStok();

        return true;
    }

    /**
     * Kembalikan stok untuk ukuran tertentu saat barang dikembalikan pelanggan.
     * Setelah stok dikembalikan, status otomatis menjadi 'Tersedia'.
     */
    public function kembalikanStok($ukuran, $jumlah = 1): bool
    {
        $stokArray = $this->getStokPerUkuranAttribute();

        // Tambahkan kembali stok untuk ukuran yang dikembalikan
        $stokArray[$ukuran] = ($stokArray[$ukuran] ?? 0) + $jumlah;
        $this->stok = json_encode($stokArray);

        $this->save();
        $this->syncStatusFromStok(); // Total stok pasti > 0, jadi status → 'Tersedia'

        return true;
    }
}