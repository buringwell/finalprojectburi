<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = $request->input('search');
            $cacheKey = 'alat.all' . ($query ? ".search.{$query}" : '');
    
            $alat = Cache::remember($cacheKey, 60, function () use ($query) {
                $alatQuery = Alat::query();
                
                if ($query) {
                    $alatQuery->where('alat_nama', 'LIKE', "%{$query}%")
                              ->orWhere('kategori_id', 'LIKE', "%{$query}%");
                }
    
                return $alatQuery->get();
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved alat data.',
                'data' => $alat,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
                'data' => null,
                'errors' => $error->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $cacheKey = 'alat.' . $id;
            $alat = Cache::remember($cacheKey, 60, function () use ($id) {
                return Alat::findOrFail($id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved alat data.',
                'data' => $alat,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
                'data' => null,
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
    public function updateImage(Request $request, $id)
    {
        
        try {
            // Validasi input
            $request->validate([
                'alat_gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
            ]);
            
            // Temukan model Alat berdasarkan ID
            $alat = Alat::findOrFail($id);
    
            // Simpan gambar baru
            $path = $request->file('alat_gambar')->store('images', 'public'); // Simpan di 'public/images'
            $filename = basename($path); // Ambil nama file dari path
    
            // Hapus gambar lama jika ada
            if ($alat->alat_gambar) {
                // Hapus gambar lama dari storage
                $oldImagePath = 'public/images/' . $alat->alat_gambar;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
            }
            \Log::info('Old image path: ' . $oldImagePath);
            // Perbarui database
            $alat->alat_gambar = $filename;
            $alat->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully.',
                'image_path' => asset('storage/images/' . $filename)
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating image.',
                'errors' => $error->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kategori_id' => 'required|exists:kategori,id',
                'alat_nama' => 'required|string|max:150',
                'alat_deskripsi' => 'required|string|max:255',
                'alat_hargaperhari' => 'required|numeric',
                'alat_stok' => 'required|numeric',
                'alat_gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create alat data. Please check your input.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Simpan gambar jika ada
            $gambarPath = null;
            if ($request->hasFile('alat_gambar')) {
                $image = $request->file('alat_gambar');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                
                // Resize & Crop gambar menjadi 600x600 px (1:1)
                $resizedImage = Image::make($image)
                    ->fit(600, 600); // Crop otomatis ke 1:1
        
                // Simpan gambar ke storage
                Storage::disk('public')->put("uploads/alat/{$filename}", (string) $resizedImage->encode());
        
                $gambarPath = "uploads/alat/{$filename}";
            }

            $alat = Alat::create([
                'kategori_id' => $request->kategori_id,
                'alat_nama' => $request->alat_nama,
                'alat_deskripsi' => $request->alat_deskripsi,
                'alat_hargaperhari' => $request->alat_hargaperhari,
                'alat_stok' => $request->alat_stok,
                'alat_gambar' => $gambarPath,
            ]);

            Cache::forget('alat.all');

            return response()->json([
                'success' => true,
                'message' => 'Successfully created alat data.',
                'data' => $alat,
            ], 201);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
                'data' => null,
                'errors' => $error->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $alat = Alat::findOrFail($id);
    
            // Validasi data
            $validator = Validator::make($request->all(), [
                'kategori_id' => 'required|exists:kategori,id',
                'alat_nama' => 'required|string|max:150',
                'alat_deskripsi' => 'required|string|max:255',
                'alat_hargaperhari' => 'required|numeric',
                'alat_stok' => 'required|numeric',
                'alat_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            // Handle file upload
            if ($request->hasFile('alat_gambar')) {
                // Hapus gambar lama jika ada
                if ($alat->alat_gambar) {
                    Storage::disk('public')->delete($alat->alat_gambar);
                }
    
                // Simpan gambar baru
                $path = $request->file('alat_gambar')->store('uploads/alat', 'public');
                $alat->alat_gambar = $path;
            }
    
            // Update data
            $alat->update([
                'kategori_id' => $request->kategori_id,
                'alat_nama' => $request->alat_nama,
                'alat_deskripsi' => $request->alat_deskripsi,
                'alat_hargaperhari' => $request->alat_hargaperhari,
                'alat_stok' => $request->alat_stok,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully',
                'data' => $alat,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $alat = Alat::findOrFail($id);

            // Hapus gambar jika ada
            if ($alat->alat_gambar) {
                Storage::disk('public')->delete($alat->alat_gambar);
            }

            $alat->delete();

            Cache::forget('alat.all');
            Cache::forget('alat.' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Successfully deleted alat data.',
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
}
