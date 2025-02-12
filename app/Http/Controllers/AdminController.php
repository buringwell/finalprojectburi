<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Exception;

class AdminController extends Controller
{
    public function index()
    {
        try {
            $cachekey = 'admin.all';
            $admin = Cache::remember($cachekey, 60 * 24 , function () {
                return Admin::getAllAdmin();
            });
            $response = [
                'success' => true,
                'message' => 'Successfully get admin data.',
                'data' => $admin,
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
            
            $admin = Admin::getAdminById($id);
            $response = [
                'success' => true,
                'message' => 'Successfully get admin data.',
                'data' => $admin,
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
                'admin_username' => 'required|string|max:50',
                'admin_password' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to create admin data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $admin = Admin::createAdmin($validator->validated());
            Cache::forget('admin.all');
            $response = [
                'success' => true,
                'message' => 'Successfully created admin data.',
                'data' => $admin,
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
                'admin_username' => 'required|string|max:50',
                'admin_password' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update admin data. Please check your data.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ];
                return response()->json($response, 400);
            }

            $admin = Admin::updateAdmin($id, $validator->validated());
            Cache::forget('admin.all');
            $response = [
                'success' => true,
                'message' => 'Successfully updated admin data.',
                'data' => $admin,
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

    public function update_pass(Request $request, $id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'admin_username' => 'required|string|max:50',
                'admin_password' => 'required|string|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update admin data. Please check your data.',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $validatedData = $validator->validated();
    
            // Cek apakah username ada di database
            $admin = Admin::where('admin_username', $validatedData['admin_username'])->first();
    
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username not found.',
                ], 404);
            }
    
            // Update password dengan hashing
            $admin->admin_password = Hash::make($validatedData['admin_password']);
            $admin->save();
    
            // Hapus cache terkait admin
            Cache::forget('admin.all');
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated admin password.',
                'data' => $admin,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an internal server error.',
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
    

    public function destroy($id)
    {
        try {
            $admin = Admin::deleteAdmin($id);
            Cache::forget('admin.all');
            $response = [
                'success' => true,
                'message' => 'Successfully deleted admin data.',
                'data' => $admin,
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