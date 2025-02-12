<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'kategori_id',
        'alat_nama',
        'alat_deskripsi',
        'alat_hargaperhari',
        'alat_stok',
    ];

    // Relasi many-to-one dengan tabel kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    // Relasi many-to-many dengan tabel penyewaan melalui tabel penyewaan_detail
    public function penyewaan()
    {
        return $this->belongsToMany(Penyewaan::class, 'penyewaan_detail', 'alat_id', 'penyewaan_id')
                    ->withPivot('penyewaan_detail_jumlah', 'penyewaan_detail_subharga');
    }

     // Method untuk GET semua data Alat
    public static function getAllAlat()
    {
        return self::all();
    }

    // Method untuk GET data Alat by ID
    public static function getAlatById($id)
    {
        return self::find($id);
    }

    // Method untuk POST (create) data Alat
    public static function createAlat($data)
    {
        return self::create($data);
    }

    // Method untuk PATCH (update) data Alat
    public static function updateAlat($id, $data)
    {
        $alat = self::find($id);
        if ($alat) {
            $alat->update($data);
        }
        return $alat;
    }

    // Method untuk DELETE (delete) data Alat
    public static function deleteAlat($id)
    {
        $alat = self::find($id);
        if ($alat) {
            $alat->delete();
        }
        return $alat;
    }
}
