<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PelangganData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;

class PelangganDataController extends Controller
{
    public function index()
    {
        try {
            $pelangganData = Cache::remember('pelanggandata.all', 60, function () {
                return PelangganData::getAllPelangganData();
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get pelanggan data.',
                'data' => $pelangganData,
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
            $pelangganData = Cache::remember('pelanggandata.' . $id, 60, function () use ($id) {
                return PelangganData::getPelangganDataById($id);
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get pelanggan data.',
                'data' => $pelangganData,
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
                'pelanggan_id' => 'required|exists:pelanggan,id',
                'pelanggan_data_jenis' => 'required|in:KTP,SIM',
                'pelanggan_data_file' => 'required|image|mimes:jpg,jpeg,png|max:5000', // Validasi file foto
            ]);
    
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to create pelanggan data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }
    
            // Simpan file foto
            $file = $request->file('pelanggan_data_file');
            $filePath = PelangganData::savePhoto($file);
    
            // Simpan data ke database
            $pelangganData = PelangganData::create([
                'pelanggan_id' => $request->pelanggan_id,
                'pelanggan_data_jenis' => $request->pelanggan_data_jenis,
                'pelanggan_data_file' => $filePath, // Simpan path file foto
            ]);
    
            Cache::forget('pelanggandata.all');
            $response = [
                'success' => true,
                'message' => 'Successfully created pelanggan data.',
                'data' => $pelangganData,
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
                'pelanggan_id' => 'required|exists:pelanggan,id',
                'pelanggan_data_jenis' => 'required|in:KTP,SIM',
                'pelanggan_data_file' => 'nullable|image|mimes:jpg,jpeg,png|max:5000', // File opsional
            ]);
    
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update pelanggan data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }
    
            $pelangganData = PelangganData::findOrFail($id);
    
            // Jika ada file foto baru, simpan dan hapus file lama
            if ($request->hasFile('pelanggan_data_file')) {
                // Hapus file lama jika ada
                if ($pelangganData->pelanggan_data_file && Storage::disk('public')->exists($pelangganData->pelanggan_data_file)) {
                    Storage::disk('public')->delete($pelangganData->pelanggan_data_file);
                }
    
                // Simpan file baru
                $file = $request->file('pelanggan_data_file');
                $filePath = PelangganData::savePhoto($file);
                $pelangganData->pelanggan_data_file = $filePath;
            }
    
            // Update data
            $pelangganData->pelanggan_id = $request->pelanggan_id;
            $pelangganData->pelanggan_data_jenis = $request->pelanggan_data_jenis;
            $pelangganData->save();
    
            Cache::forget('pelanggandata.all');
            $response = [
                'success' => true,
                'message' => 'Successfully updated pelanggan data.',
                'data' => $pelangganData,
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
            $pelangganData = PelangganData::findOrFail($id);
    
            // Hapus file foto jika ada
            if ($pelangganData->pelanggan_data_file && Storage::disk('public')->exists($pelangganData->pelanggan_data_file)) {
                Storage::disk('public')->delete($pelangganData->pelanggan_data_file);
            }
    
            // Hapus data dari database
            $pelangganData->delete();
    
            Cache::forget('pelanggandata.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted pelanggan data.',
                'data' => $pelangganData,
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