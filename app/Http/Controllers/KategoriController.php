<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Exception;

class KategoriController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $query = $request->input('search');
            $cacheKey = 'kategori.all' . ($query ? ".search.{$query}" : '');
    
            // Cek cache terlebih dahulu
            $kategori = Cache::remember($cacheKey, 60, function () use ($query) {
                $kategoriQuery = Kategori::query();
                
                // Jika ada parameter pencarian, tambahkan filter
                if ($query) {
                    $kategoriQuery->where('id', 'LIKE', "%{$query}%")
                                  ->orWhere('kategori_nama', 'LIKE', "%{$query}%");
                }
    
                return $kategoriQuery->get();
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved kategori data.',
                'data' => $kategori,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error in the internal server.',
                'data' => null,
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
    


    public function show($id)
    {
        try {
            $kategori = Cache::remember('kategori.' . $id, 60, function () use ($id) {
                return Kategori::getKategoriById($id);
            });
    
            if (!$kategori) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori not found.',
                    'data' => null,
                ], 404);
            }
    
            $response = [
                'success' => true,
                'message' => 'Successfully get kategori data.',
                'data' => $kategori,
            ];
            return response()->json($response, 200);
        } catch (Exception $error) {
            $response = [
                'success' => false,
                'message' => 'Sorry, there was an error in the internal server.',
                'data' => null,
                'errors' => $error->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kategori_nama' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to create kategori data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $kategori = Kategori::createKategori($validator->validated());
            Cache::forget('kategeori.all');
            $response = [
                'success' => true,
                'message' => 'Successfully created kategori data.',
                'data' => $kategori,
            ];
            return response()->json($response, 201);
        } catch (Exception $error) {
            $response = [
                'success' => false,
                'message' => 'Sorry, there was an error in the internal server.',
                'data' => null,
                'errors' => $error->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kategori_nama' => 'required|string|max:100', // Pastikan validasi ini sesuai
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update kategori data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $kategori = Kategori::findOrFail($id);
            $kategori->kategori_nama = $request->input('kategori_nama');
            $kategori->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated kategori data.',
                'data' => $kategori,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error in the internal server.',
                'data' => null,
                'errors' => $error->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kategori = Kategori::deleteKategori($id);
            Cache::forget('kategeori.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted kategori data.',
                'data' => $kategori,
            ];
            return response()->json($response, 200);
        } catch (Exception $error) {
            $response = [
                'success' => false,
                'message' => 'Sorry, there was an error in the internal server.',
                'data' => null,
                'errors' => $error->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
}