<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';
    protected $primaryKey = 'id';
    protected $fillable = [
        'kategori_nama',
    ];

    // Relasi one-to-many dengan tabel alat
    public function alat()
    {
        return $this->hasMany(Alat::class, 'kategori_id');
    }

      // Method untuk GET semua data Kategori
    public static function getAllKategori()
    {
        return self::all();
    }

    // Method untuk GET data Kategori by ID
    public static function getKategoriById($id)
    {
        return self::find($id);
    }

    // Method untuk POST (create) data Kategori
    public static function createKategori($data)
    {
        return self::create($data);
    }

    // Method untuk PATCH (update) data Kategori
    public static function updateKategori($id, $data)
    {
        $kategori = self::find($id);
        if ($kategori) {
            $kategori->update($data);
        }
        return $kategori;
    }

    // Method untuk DELETE (delete) data Kategori
    public static function deleteKategori($id)
    {
        $kategori = self::find($id);
        if ($kategori) {
            $kategori->delete();
        }
        return $kategori;
    }
}