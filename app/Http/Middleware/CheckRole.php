<?php

namespace App\Http\Middleware;

use App\Models\LogAktivitas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kontrol akses berbasis peran (SKPL 2.1 "Keamanan").
 * Pemakaian: ->middleware('role:staf_beasiswa,mitra')
 * Admin WR3 mewarisi seluruh hak Staf Unit Beasiswa (SKPL 2.2.1).
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        if (! $user->status_aktif) {
            auth()->logout();
            $request->session()->invalidate();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi admin.');
        }

        $diizinkan = collect($roles)
            ->flatMap(fn ($r) => preg_split('/[|,]/', (string) $r))
            ->map(fn ($r) => trim($r))
            ->filter()
            ->all();

        $ok = in_array($user->role, $diizinkan, true)
            || ($user->isAdmin() && in_array('staf_beasiswa', $diizinkan, true));

        if ($ok) {
            return $next($request);
        }

        LogAktivitas::catat('akses_ditolak', 'autentikasi', $request->method().' '.$request->path());

        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
