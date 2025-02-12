<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    use HasFactory;

    protected $table = 'penyewaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pelanggan_id',
        'penyewaan_tglsewa',
        'penyewaan_tglkembali',
        'penyewaan_stspembayaran',
        'penyewaan_sttskembali',
        'penyewaan_totalharga',
    ];

    // Relasi many-to-one dengan tabel pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // Relasi many-to-many dengan tabel alat melalui tabel penyewaan_detail
    public function alat()
    {
        return $this->belongsToMany(Alat::class, 'penyewaan_detail', 'penyewaan_id', 'alat_id')
                    ->withPivot('penyewaan_detail_jumlah', 'penyewaan_detail_subharga');
    }
    
    // Method untuk GET semua data Penyewaan
    public static function getAllPenyewaan()
    {
        return self::all();
    }

    // Method untuk GET data Penyewaan by ID
    public static function getPenyewaanById($id)
    {
        return self::find($id);
    }

    // Method untuk POST (create) data Penyewaan
    public static function createPenyewaan($data)
    {
        return self::create($data);
    }

    // Method untuk PATCH (update) data Penyewaan
    public static function updatePenyewaan($id, $data)
    {
        $penyewaan = self::find($id);
        if ($penyewaan) {
            $penyewaan->update($data);
        }
        return $penyewaan;
    }

    // Method untuk DELETE (delete) data Penyewaan
    public static function deletePenyewaan($id)
    {
        $penyewaan = self::find($id);
        if ($penyewaan) {
            $penyewaan->delete();
        }
        return $penyewaan;
    }
}