<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = [
        'admin_username',
        'admin_password',
    ];

 // Method untuk GET semua data Admin
    public static function getAllAdmin()
    {
        return self::all();
    }

    // Method untuk GET data Admin by ID
    public static function getAdminById($id)
    {
        return self::find($id);
    }

    // Method untuk POST (create) data Admin
    public static function createAdmin($data)
    {
        return self::create($data);
    }

    // Method untuk PATCH (update) data Admin
    public static function updateAdmin($id, $data)
    {
        $admin = self::find($id);
        if ($admin) {
            $admin->update($data);
        }
        return $admin;
    }

    // Method untuk DELETE (delete) data Admin
    public static function deleteAdmin($id)
    {
        $admin = self::find($id);
        if ($admin) {
            $admin->delete();
        }
        return $admin;
    }
}
