<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json(['success' => true, 'message' => 'User registered successfully.', 'user' => $user, 'token' => $token], 201);
        } catch (Exception $error) {
            return response()->json(['success' => false, 'message' => 'Internal server error.', 'errors' => $error->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 400);
            }
    
            // Cari user berdasarkan email
            $user = User::where('email', $request->email)->first();
    
            // Jika user tidak ditemukan
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Invalid email or .'], 401);
            }
    
            // Bandingkan password dengan hash di database
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
            }
    
            // Generate token JWT secara manual
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'success' => true,
                'message' => 'Logged in successfully.',
                'token' => $token,
                'user' => $user,
            ]);
        } catch (Exception $error) {
            return response()->json(['success' => false, 'message' => 'Internal server error.', 'errors' => $error->getMessage()], 500);
        }
    }
    

    public function me()
    {
        return response()->json(['success' => true, 'message' => 'User retrieved successfully.', 'user' => Auth::user()]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    public function destroy()
    {
        $user = Auth::user();
        $user->delete();
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }

    public function update_pass(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'new_password' => 'required|string|min:6|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            // Cari user berdasarkan email
            $user = User::where('email', $request->email)->first();
    
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }
    
            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();
    
           
    
            return response()->json([
                'success' => true,
                'message' => 'Password successfully updated. Please log in again.',
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
    

    public function refresh()
    {
        return response()->json(['success' => true, 'message' => 'Token refreshed successfully.', 'token' => JWTAuth::refresh(JWTAuth::getToken())]);
    }
}
