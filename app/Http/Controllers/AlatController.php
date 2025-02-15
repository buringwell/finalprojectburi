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
    public function index()
    {
        try {
            $cacheKey = 'alat.all';
            $alat = Cache::remember($cacheKey, 60, function () {
                return Alat::all();
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
                    'message' => 'Failed to update alat data. Please check your input.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Jika ada gambar baru, hapus gambar lama dan simpan gambar baru
            if ($request->hasFile('alat_gambar')) {
                if ($alat->alat_gambar) {
                    Storage::disk('public')->delete($alat->alat_gambar);
                }

                $alat->alat_gambar = $request->file('alat_gambar')->store('uploads/alat', 'public');
            }

            $alat->update($validator->validated());

            Cache::forget('alat.all');
            Cache::forget('alat.' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated alat data.',
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
