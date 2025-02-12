<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pelanggan_nama',
        'pelanggan_alamat',
        'pelanggan_notelp',
        'pelanggan_email',
    ];

    // Relasi one-to-many dengan tabel pelanggan_data
    public function pelangganData()
    {
        return $this->hasMany(PelangganData::class, 'pelanggan_id');
    }

    // Relasi one-to-many dengan tabel penyewaan
    public function penyewaan()
    {
        return $this->hasMany(Penyewaan::class, 'pelanggan_id');
    }

        // Method untuk GET semua data Pelanggan
        public static function getAllPelanggan()
        {
            return self::all();
        }
    
        // Method untuk GET data Pelanggan by ID
        public static function getPelangganById($id)
        {
            return self::find($id);
        }
    
        // Method untuk POST (create) data Pelanggan
        public static function createPelanggan($data)
        {
            return self::create($data);
        }
    
        // Method untuk PATCH (update) data Pelanggan
        public static function updatePelanggan($id, $data)
        {
            $pelanggan = self::find($id);
            if ($pelanggan) {
                $pelanggan->update($data);
            }
            return $pelanggan;
        }
    
        // Method untuk DELETE (delete) data Pelanggan
        public static function deletePelanggan($id)
        {
            $pelanggan = self::find($id);
            if ($pelanggan) {
                $pelanggan->delete();
            }
            return $pelanggan;
        }
}
