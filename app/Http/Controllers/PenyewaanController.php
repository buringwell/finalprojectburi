<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Alat;
use Exception;

class PenyewaanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = $request->input('search');
            $cacheKey = 'penyewaan.all' . ($query ? ".search.{$query}" : '');
    
            $penyewaan = Cache::remember($cacheKey, 60, function () use ($query) {
                $penyewaanQuery = Penyewaan::query();
    
                if ($query) {
                    $penyewaanQuery->where('penyewaan_tglsewa', 'LIKE', "%{$query}%")
                                   ->orWhere('penyewaan_tglkembali', 'LIKE', "%{$query}%")
                                   ->orWhere('penyewaan_stspembayaran', 'LIKE', "%{$query}%");
                }
    
                return $penyewaanQuery->get();
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved penyewaan data.',
                'data' => $penyewaan,
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

            $penyewaan = Penyewaan::create([
                'pelanggan_id' => $request->pelanggan_id,
                'penyewaan_tglsewa' => $request->penyewaan_tglsewa,
                'penyewaan_tglkembali' => $request->penyewaan_tglkembali,
                'penyewaan_stspembayaran' => $request->penyewaan_stspembayaran,
                'penyewaan_sttskembali' => $request->penyewaan_sttskembali,
                'penyewaan_totalharga' => 0, // sementara diisi 0, nanti akan diupdate
            ]);

            Cache::forget('penyewaan.all');

            return response()->json($penyewaan, 201);
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
            $validator = Validator::make($request->all(), 
                [
                    'pelanggan_id' => $request->pelanggan_id,
                    'penyewaan_tglsewa' => $request->penyewaan_tglsewa,
                    'penyewaan_tglkembali' => $request->penyewaan_tglkembali,
                    'penyewaan_stspembayaran' => $request->penyewaan_stspembayaran,
                    'penyewaan_sttskembali' => $request->penyewaan_sttskembali,
                    'penyewaan_totalharga' => 0,
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
    
            // Ambil data penyewaan yang akan diupdate
            $penyewaan = Penyewaan::findOrFail($id);
    
            // Jika status penyewaan diubah menjadi "Sudah Kembali" dan sebelumnya bukan "Sudah Kembali"
            if ($request->penyewaan_sttskembali === 'Sudah Kembali' && $penyewaan->penyewaan_sttskembali !== 'Sudah Kembali') {
                // Ambil semua detail penyewaan yang terkait
                $penyewaanDetails = PenyewaanDetail::where('penyewaan_id', $id)->get();
    
                // Kembalikan stok alat untuk setiap detail penyewaan
                foreach ($penyewaanDetails as $detail) {
                    $alat = Alat::find($detail->alat_id);
                    if ($alat) {
                        $alat->alat_stok += $detail->penyewaan_detail_jumlah; // Kembalikan stok
                        $alat->save();
                    }
                }
            }
    
            // Jika status penyewaan diubah dari "Sudah Kembali" menjadi "Belum Kembali"
            if ($request->penyewaan_sttskembali === 'Belum Kembali' && $penyewaan->penyewaan_sttskembali === 'Sudah Kembali') {
                // Ambil semua detail penyewaan yang terkait
                $penyewaanDetails = PenyewaanDetail::where('penyewaan_id', $id)->get();
    
                // Kurangi stok alat untuk setiap detail penyewaan
                foreach ($penyewaanDetails as $detail) {
                    $alat = Alat::find($detail->alat_id);
                    if ($alat) {
                        $alat->alat_stok -= $detail->penyewaan_detail_jumlah; // Kurangi stok
                        $alat->save();
                    }
                }
            }
    
            // Update data penyewaan
            $penyewaan->update($validator->validated());
    
            // Hapus cache jika ada
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