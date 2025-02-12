<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Exception;

class PenyewaanController extends Controller
{
    public function index()
    {
        try {
            $penyewaan = Cache::remember('penyewaan.all', 60, function () {
                return Penyewaan::getAllPenyewaan();
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get penyewaan data.',
                'data' => $penyewaan,
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
            $penyewaan = Penyewaan::getPenyewaanById($id);
            $response = [
                'success' => true,
                'message' => 'Successfully get penyewaan data.',
                'data' => $penyewaan,
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
                'penyewaan_tglsewa' => 'required|date',
                'penyewaan_tglkembali' => 'required|date',
                'penyewaan_stspembayaran' => 'required|in:Lunas,Belum Dibayar,DP',
                'penyewaan_sttskembali' => 'required|in:Sudah Kembali,Belum Kembali',
                'penyewaan_totalharga' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to create penyewaan data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $penyewaan = Penyewaan::createPenyewaan($validator->validated());
            Cache::forget('penyewaan.all');
            $response = [
                'success' => true,
                'message' => 'Successfully created penyewaan data.',
                'data' => $penyewaan,
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
                'penyewaan_tglsewa' => 'required|date',
                'penyewaan_tglkembali' => 'required|date',
                'penyewaan_stspembayaran' => 'required|in:Lunas,Belum Dibayar,DP',
                'penyewaan_sttskembali' => 'required|in:Sudah Kembali,Belum Kembali',
                'penyewaan_totalharga' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update penyewaan data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $penyewaan = Penyewaan::updatePenyewaan($id, $validator->validated());
            Cache::forget('penyewaan.all');
            $response = [
                'success' => true,
                'message' => 'Successfully updated penyewaan data.',
                'data' => $penyewaan,
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
            $penyewaan = Penyewaan::deletePenyewaan($id);
            Cache::forget('penyewaan.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted penyewaan data.',
                'data' => $penyewaan,
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