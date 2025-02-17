<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Exception;

class PelangganController extends Controller
{
    public function index()
    {
        try {
            $query = $request->input('query');
            $cacheKey = 'pelanggan.all' . ($query ? ".search.{$query}" : '');
    
            $pelanggan = Cache::remember($cacheKey, 60, function () use ($query) {
                $pelangganQuery = Pelanggan::query();
    
                if ($query) {
                    $pelangganQuery->where('pelanggan_nama', 'LIKE', "%{$query}%")
                                   ->orWhere('pelanggan_email', 'LIKE', "%{$query}%")
                                   ->orWhere('pelanggan_nohp', 'LIKE', "%{$query}%");
                }
    
                return $pelangganQuery->get();
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved pelanggan data.',
                'data' => $pelanggan,
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
            $cachekey = 'pelanggan' . $id;
            $pelanggan = Cache::remember( $cachekey, 60 , function () use ($id){
                return Pelanggan::getPelangganById($id);
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get pelanggan data.',
                'data' => $pelanggan,
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
                'pelanggan_nama' => 'required|string|max:150',
                'pelanggan_alamat' => 'required|string|max:200',
                'pelanggan_notelp' => 'required|string|max:13',
                'pelanggan_email' => 'required|email|max:100',
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

            $pelanggan = Pelanggan::createPelanggan($validator->validated());

            Cache::forget('pelanggan.all');

            $response = [
                'success' => true,
                'message' => 'Successfully created pelanggan data.',
                'data' => $pelanggan,
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
                'pelanggan_nama' => 'required|string|max:150',
                'pelanggan_alamat' => 'required|string|max:200',
                'pelanggan_notelp' => 'required|string|max:13',
                'pelanggan_email' => 'required|email|max:100',
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

            $pelanggan = Pelanggan::updatePelanggan($id, $validator->validated());

            Cache::forget('pelanggan.all');

            $response = [
                'success' => true,
                'message' => 'Successfully updated pelanggan data.',
                'data' => $pelanggan,
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
            $pelanggan = Pelanggan::deletePelanggan($id);
            Cache::forget('pelanggan.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted pelanggan data.',
                'data' => $pelanggan,
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