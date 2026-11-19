<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/** P-01 Login dan Autentikasi (UC-01, ALGO-01). */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi username dan password tidak kosong
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $kunci = 'login:'.mb_strtolower($data['username']);
        $maks = (int) config('beasiswa.login_maks_gagal');

        // 3b. Akun dikunci setelah 5 kali gagal berturut-turut
        if (RateLimiter::tooManyAttempts($kunci, $maks)) {
            $menit = (int) ceil(RateLimiter::availableIn($kunci) / 60);

            return back()->withErrors([
                'username' => "Akun terkunci karena terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit.",
            ])->onlyInput('username');
        }

        // 2. Cari username pada t_pengguna
        $pengguna = Pengguna::where('username', $data['username'])->first();

        // 3. Tidak ditemukan ATAU hash tidak cocok -> catat percobaan gagal
        if (! $pengguna || ! Hash::check($data['password'], $pengguna->password)) {
            RateLimiter::hit($kunci, (int) config('beasiswa.login_kunci_detik'));
            LogAktivitas::catat('login_gagal', 'autentikasi', 'Username: '.$data['username'], $pengguna?->id_pengguna);

            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        // 3. status_aktif = 0
        if (! $pengguna->status_aktif) {
            LogAktivitas::catat('login_ditolak', 'autentikasi', 'Akun nonaktif', $pengguna->id_pengguna);

            return back()->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi admin.'])->onlyInput('username');
        }

        // 4. Perbarui last_login, buat sesi, catat log, arahkan sesuai role
        RateLimiter::clear($kunci);
        Auth::login($pengguna);
        $request->session()->regenerate();
        $pengguna->forceFill(['last_login' => now()])->save();
        LogAktivitas::catat('login', 'autentikasi', 'Login berhasil', $pengguna->id_pengguna);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        LogAktivitas::catat('logout', 'autentikasi');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
