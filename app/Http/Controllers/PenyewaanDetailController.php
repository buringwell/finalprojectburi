<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyewaanDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Exception;

class PenyewaanDetailController extends Controller
{
    public function index()
    {
        try {
            $penyewaanDetail = Cache::remember('penyewaandetail.all', 60 , function () {
                return PenyewaanDetail::getAllPenyewaanDetail();
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get penyewaan detail data.',
                'data' => $penyewaanDetail,
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
            $penyewaanDetail = PenyewaanDetail::getPenyewaanDetailById($id);
            $response = [
                'success' => true,
                'message' => 'Successfully get penyewaan detail data.',
                'data' => $penyewaanDetail,
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
                'penyewaan_id' => 'required|exists:penyewaan,id',
                'alat_id' => 'required|exists:alat,id',
                'penyewaan_detail_jumlah' => 'required|numeric',
                'penyewaan_detail_subharga' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to create penyewaan detail data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $penyewaanDetail = PenyewaanDetail::createPenyewaanDetail($validator->validated());
            Cache::forget('penyewaandetail.all');
            $response = [
                'success' => true,
                'message' => 'Successfully created penyewaan detail data.',
                'data' => $penyewaanDetail,
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
                'penyewaan_id' => 'required|exists:penyewaan,id',
                'alat_id' => 'required|exists:alat,id',
                'penyewaan_detail_jumlah' => 'required|numeric',
                'penyewaan_detail_subharga' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update penyewaan detail data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $penyewaanDetail = PenyewaanDetail::updatePenyewaanDetail($id, $validator->validated());
            Cache::forget('penyewaandetail.all');
            $response = [
                'success' => true,
                'message' => 'Successfully updated penyewaan detail data.',
                'data' => $penyewaanDetail,
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
            $penyewaanDetail = PenyewaanDetail::deletePenyewaanDetail($id);
            Cache::forget('penyewaandetail.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted penyewaan detail data.',
                'data' => $penyewaanDetail,
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