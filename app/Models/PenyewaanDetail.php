<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyewaanDetail extends Model
{
    use HasFactory;

    protected $table = 'penyewaan_detail';
    protected $primaryKey = 'id';
    protected $fillable = [
        'penyewaan_id',
        'alat_id',
        'penyewaan_detail_jumlah',
        'penyewaan_detail_subharga',
    ];

    // Relasi many-to-one dengan tabel penyewaan
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'penyewaan_id');
    }

    // Relasi many-to-one dengan tabel alat
    public function alat()
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    // Method untuk GET semua data PenyewaanDetail
    public static function getAllPenyewaanDetail()
    {
        return self::all();
    }

    // Method untuk GET data PenyewaanDetail by ID
    public static function getPenyewaanDetailById($id)
    {
        return self::find($id);
    }

    // Method untuk POST (create) data PenyewaanDetail
    public static function createPenyewaanDetail($data)
    {
        return self::create($data);
    }

    // Method untuk PATCH (update) data PenyewaanDetail
    public static function updatePenyewaanDetail($id, $data)
    {
        $penyewaanDetail = self::find($id);
        if ($penyewaanDetail) {
            $penyewaanDetail->update($data);
        }
        return $penyewaanDetail;
    }

    // Method untuk DELETE (delete) data PenyewaanDetail
    public static function deletePenyewaanDetail($id)
    {
        $penyewaanDetail = self::find($id);
        if ($penyewaanDetail) {
            $penyewaanDetail->delete();
        }
        return $penyewaanDetail;
    }
}