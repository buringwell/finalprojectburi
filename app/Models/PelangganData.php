<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelangganData extends Model
{
    use HasFactory;

    protected $table = 'pelanggan_data';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pelanggan_id',
        'pelanggan_data_jenis',
        'pelanggan_data_file',
    ];

    // Relasi many-to-one dengan tabel pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }
 
    // Method untuk GET semua data PelangganData
    public static function getAllPelangganData()
    {
        return self::all();
    }

    // Method untuk GET data PelangganData by ID
    public static function getPelangganDataById($id)
    {
        return self::find($id);
    }

    // Method untuk POST (create) data PelangganData
    public static function createPelangganData($data)
    {
        return self::create($data);
    }

    // Method untuk PATCH (update) data PelangganData
    public static function updatePelangganData($id, $data)
    {
        $pelangganData = self::find($id);
        if ($pelangganData) {
            $pelangganData->update($data);
        }
        return $pelangganData;
    }

    // Method untuk DELETE (delete) data PelangganData
    public static function deletePelangganData($id)
    {
        $pelangganData = self::find($id);
        if ($pelangganData) {
            $pelangganData->delete();
        }
        return $pelangganData;
    }
    public static function validatePhoto($file)
    {
        return $file->isValid() && in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']);
    }

    /**
     * Simpan file foto ke storage.
     */
    public static function savePhoto($file)
    {
        $fileName = time() . '_' . $file->getClientOriginalName(); // Nama file unik
        $filePath = $file->storeAs('photos', $fileName, 'public'); // Simpan di folder 'photos' di storage/public
        return $filePath;
    }
}