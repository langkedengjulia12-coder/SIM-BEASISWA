<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Email atau password salah', 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return $this->error('Akun Anda tidak aktif. Hubungi administrator.', 403);
        }

        // Revoke existing tokens
        $user->tokens()->delete();

        $token = $user->createToken('simk-token', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'user'  => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login berhasil');
    }

    /**
     * POST /api/v1/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'sometimes|in:admin,pengelola,penyewa',
            'no_hp'    => 'sometimes|string|max:20',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'pengelola',
            'no_hp'    => $request->no_hp,
        ]);

        $token = $user->createToken('simk-token')->plainTextToken;

        return $this->created([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ], 'Registrasi berhasil');
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil');
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('penyewa');

        return $this->success($this->formatUser($user, true));
    }

    /**
     * PUT /api/v1/auth/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return $this->error('Password lama tidak sesuai', 422);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);
        $user->tokens()->delete(); // Force re-login

        return $this->success(null, 'Password berhasil diubah. Silakan login kembali.');
    }

    private function formatUser(User $user, bool $full = false): array
    {
        $data = [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'no_hp'     => $user->no_hp,
            'is_active' => $user->is_active,
        ];

        if ($full && $user->penyewa) {
            $data['penyewa'] = [
                'id'           => $user->penyewa->id,
                'nama_lengkap' => $user->penyewa->nama_lengkap,
                'kamar_id'     => $user->penyewa->kamar_id,
                'status'       => $user->penyewa->status,
            ];
        }

        return $data;
    }
}