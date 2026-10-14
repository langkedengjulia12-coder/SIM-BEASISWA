<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Penyewa;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginRegisterController extends Controller
{
    /**
     * Show login form
     */
    public function login_form()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Route 'simk.dashboard' sudah dihapus (cleanup SIMK -> SIM-BEASISWA),
            // semua role diarahkan ke 'dashboard'.
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    /**
     * Show register form
     */
    public function register_form()
    {
        return view('auth.register');
    }

    /**
     * Handle register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nik' => 'required|string|max:30|unique:penyewa,nik',
            'no_hp' => 'required|string|max:20|unique:penyewa,no_hp',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'penyewa',
            'is_active' => true,
            'no_hp' => $validated['no_hp'],
        ]);

        // Create a corresponding Penyewa record so admin can manage the tenant
        if ($user) {
            $today = now()->toDateString();
            Penyewa::create([
                'user_id' => $user->id,
                'nama_lengkap' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'no_hp' => $validated['no_hp'],
                'jenis_kelamin' => 'L',
                'tanggal_masuk' => $today,
                'kontrak_mulai' => $today,
                'kontrak_selesai' => $today,
                'status' => 'aktif',
            ]);

            // Notify first admin user (non-blocking)
            try {
                Notifikasi::kirimKeAdmin([
                    'penyewa_id' => null,
                    'judul' => 'Registrasi Penyewa Baru',
                    'pesan' => "Penyewa baru: {$validated['name']} ({$validated['email']}).",
                    'tipe' => 'registrasi',
                    'prioritas' => 'tinggi',
                ]);
            } catch (\Throwable $e) {
                // Do not break registration if notification fails
            }
        }

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
