<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyewaanDetail;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Alat;

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
                'penyewaan_detail_jumlah' => 'required|numeric|min:1',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create penyewaan detail data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            // Ambil data alat berdasarkan ID
            $alat = Alat::findOrFail($request->alat_id);
    
            // Periksa apakah stok cukup
            if ($alat->alat_stok < $request->penyewaan_detail_jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock is not enough for this rental.',
                    'data' => null,
                    'errors' => ['stok' => 'Stok tidak mencukupi.'],
                ], 400);
            }
    
            // Ambil data penyewaan untuk mendapatkan tanggal sewa dan kembali
            $penyewaan = Penyewaan::findOrFail($request->penyewaan_id);
    
            // Hitung jumlah hari penyewaan
            $tglSewa = \Carbon\Carbon::parse($penyewaan->penyewaan_tglsewa);
            $tglKembali = \Carbon\Carbon::parse($penyewaan->penyewaan_tglkembali);
            $jumlahHari = $tglKembali->diffInDays($tglSewa); // Selisih hari
    
            // Hitung subharga: jumlah alat * harga per hari * jumlah hari
            $totalsubharga = $request->penyewaan_detail_jumlah * $alat->alat_hargaperhari * $jumlahHari;
    
            // Kurangi stok alat
            $alat->alat_stok -= $request->penyewaan_detail_jumlah;
            $alat->save();
    
            // Simpan detail penyewaan
            $penyewaanDetail = PenyewaanDetail::create([
                'penyewaan_id' => $request->penyewaan_id,
                'alat_id' => $request->alat_id,
                'penyewaan_detail_jumlah' => $request->penyewaan_detail_jumlah,
                'penyewaan_detail_subharga' => $totalsubharga,
            ]);
    
            // Update total harga penyewaan
            $totalHarga = PenyewaanDetail::where('penyewaan_id', $request->penyewaan_id)->sum('penyewaan_detail_subharga');
            Penyewaan::where('id', $request->penyewaan_id)->update(['penyewaan_totalharga' => $totalHarga]);
    
            // Hapus cache jika ada
            Cache::forget('penyewaandetail.all');
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully created penyewaan detail data.',
                'data' => $penyewaanDetail,
            ], 201);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error in the internal server.',
                'data' => null,
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
    
    
    

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'penyewaan_id' => 'required|exists:penyewaan,id',
                'alat_id' => 'required|exists:alat,id',
                'penyewaan_detail_jumlah' => 'required|numeric|min:1',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update penyewaan detail data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            // Ambil data penyewaan detail yang akan diupdate
            $penyewaanDetail = PenyewaanDetail::findOrFail($id);
    
            // Ambil data alat berdasarkan ID
            $alat = Alat::findOrFail($request->alat_id);
    
            // Hitung perubahan jumlah alat
            $perubahanJumlah = $request->penyewaan_detail_jumlah - $penyewaanDetail->penyewaan_detail_jumlah;
    
            // Periksa apakah stok cukup jika jumlah alat bertambah
            if ($perubahanJumlah > 0 && $alat->alat_stok < $perubahanJumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock is not enough for this update.',
                    'data' => null,
                    'errors' => ['stok' => 'Stok tidak mencukupi.'],
                ], 400);
            }
    
            // Update stok alat berdasarkan perubahan jumlah
            if ($perubahanJumlah > 0) {
                // Jika jumlah alat bertambah, kurangi stok
                $alat->alat_stok -= $perubahanJumlah;
            } else {
                // Jika jumlah alat berkurang, tambahkan stok
                $alat->alat_stok += abs($perubahanJumlah); // Gunakan abs() untuk memastikan nilai positif
            }
            $alat->save();
    
            // Ambil data penyewaan untuk mendapatkan tanggal sewa dan kembali
            $penyewaan = Penyewaan::findOrFail($request->penyewaan_id);
    
            // Hitung jumlah hari penyewaan
            $tglSewa = \Carbon\Carbon::parse($penyewaan->penyewaan_tglsewa);
            $tglKembali = \Carbon\Carbon::parse($penyewaan->penyewaan_tglkembali);
            $jumlahHari = $tglKembali->diffInDays($tglSewa); // Selisih hari
    
            // Hitung subharga baru: jumlah alat * harga per hari * jumlah hari
            $totalsubharga = $request->penyewaan_detail_jumlah * $alat->alat_hargaperhari * $jumlahHari;
    
            // Update detail penyewaan
            $penyewaanDetail->update([
                'penyewaan_detail_jumlah' => $request->penyewaan_detail_jumlah,
                'penyewaan_detail_subharga' => $totalsubharga,
            ]);
    
            // Update total harga penyewaan
            $totalHarga = PenyewaanDetail::where('penyewaan_id', $request->penyewaan_id)->sum('penyewaan_detail_subharga');
            Penyewaan::where('id', $request->penyewaan_id)->update(['penyewaan_totalharga' => $totalHarga]);
    
            // Hapus cache jika ada
            Cache::forget('penyewaandetail.all');
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated penyewaan detail data.',
                'data' => $penyewaanDetail,
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