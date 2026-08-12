<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class AuthController extends Controller
{
    /**
     * Registrasi untuk User Baru & role selalu user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'          => ['required', 'string', 'max:255'],
                'email'         => ['required', 'string', 'email', 'unique:users'],
                'gender'        => ['required', 'in:pria,wanita'],
                'date_of_birth' => ['required', 'date'],
                'password'      => ['required', 'min:6', 'confirmed'],
                'phone'         => ['required', 'string', 'max:15'],
            ]);

            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => $validated['password'],
                'phone'     => $validated['phone'],
                'role'      => 'user'
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'    => 'success',
                'message'   => 'Registrasi berhasil',
                'data'      => [
                    'user'  => $user,
                    'token' => $token,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Registrasi Error: " . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan sistem saat registrasi.'
            ], 500);
        }
    }

    /**
     * Login -> kembalikan bearer token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'     => ['required', 'email'],
                'password'  => ['required'],
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Email atau password salah.'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'    => 'success',
                'message'   => 'Login berhasil',
                'data'      => [
                    'user'  => $user,
                    'token' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan sistem saat login.'
            ], 500);
        }
    }

    /**
     * Logout -> hapus token yang sedang dipakai
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil logout.'
        ]);
    }
}
