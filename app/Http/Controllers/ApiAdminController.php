<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiAdminController extends Controller
{
    //

    public function apiLogin(Request $request)
    {
        try {
            // Validasi email dan password
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Gunakan guard 'admin' untuk autentikasi
            if (!Auth::guard('admin')->attempt($credentials)) {
                return response()->json(['message' => 'Credentials are not valid'], 401);
            }

            // Ambil admin yang sedang login
            $admin = Auth::guard('admin')->user();

            // Pastikan Anda menggunakan Sanctum untuk token API
            return response()->json([
                'message' => 'Login successful',
                'token' => $admin->createToken('admin_token')->plainTextToken,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('API Login Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'An error occurred during login'], 500);
        }
    }

    public function apiLogout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            Log::error('User not authenticated. Token: ' . $request->bearerToken());
            return response()->json([
                'message' => 'No authenticated user found.',
            ], 401);
        }

        $user->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }
    

}
