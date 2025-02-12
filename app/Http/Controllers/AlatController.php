<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Exception;

class AlatController extends Controller
{
    public function index()
    {
        try {
            $cachekey = 'alat.all';
            $alat = Cache::remember($cachekey, 60, function () {
                return Alat::getAllAlat();
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get alat data.',
                'data' => $alat,
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

    public function show($id)
    {
        try {
            $cachekeyy = 'alat' . $id;
            $alat = Alat::getAlatById($id);
                
            $response = [
                'success' => true,
                'message' => 'Successfully get alat data.',
                'data' => $alat,
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
                'kategori_id' => 'required|exists:kategori,id',
                'alat_nama' => 'required|string|max:150',
                'alat_deskripsi' => 'required|string|max:255',
                'alat_hargaperhari' => 'required|numeric',
                'alat_stok' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to create alat data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $alat = Alat::createAlat($validator->validated());
            Cache::forget('alat.all');
            $response = [
                'success' => true,
                'message' => 'Successfully created alat data.',
                'data' => $alat,
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
                'kategori_id' => 'required|exists:kategori,id',
                'alat_nama' => 'required|string|max:150',
                'alat_deskripsi' => 'required|string|max:255',
                'alat_hargaperhari' => 'required|numeric',
                'alat_stok' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update alat data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $alat = Alat::updateAlat($id, $validator->validated());
            cacehCache::forget('alat.all');
            $response = [
                'success' => true,
                'message' => 'Successfully updated alat data.',
                'data' => $alat,
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

    public function destroy($id)
    {
        try {
            $alat = Alat::deleteAlat($id);
            Cache::forget('alat.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted alat data.',
                'data' => $alat,
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