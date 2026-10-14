<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penyewa;
use App\Models\Transaksi;
use Illuminate\View\View;

class SimkDashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(): View
    {
        // Statistik Kamar
        $totalKamar = Kamar::count();
        $kamarTersedia = Kamar::where('status', 'tersedia')->count();
        $kamarTerisi = Kamar::where('status', 'terisi')->count();
        $kamarMaintenance = Kamar::where('status', 'maintenance')->count();
        
        // Statistik Penyewa
        $totalPenyewa = Penyewa::count();
        $penyewaAktif = Penyewa::where('status', 'aktif')->count();
        $penyewaKeluar = Penyewa::where('status', 'keluar')->count();
        
        // Statistik Transaksi Bulan Ini
        $bulanIni = now()->month;
        $tahunIni = now()->year;
        
        $totalTagihan = Transaksi::where('periode_bulan', $bulanIni)
            ->where('periode_tahun', $tahunIni)
            ->sum('jumlah');
        
        $totalTerbayar = Transaksi::where('periode_bulan', $bulanIni)
            ->where('periode_tahun', $tahunIni)
            ->where('status', 'lunas')
            ->sum('jumlah');
        
        $totalTunggakan = Transaksi::where('periode_bulan', $bulanIni)
            ->where('periode_tahun', $tahunIni)
            ->whereIn('status', ['belum_bayar', 'terlambat'])
            ->sum('jumlah');
        
        // Data Penyewa Terbaru
        $penyewaTerbaru = Penyewa::with('user')
            ->latest('created_at')
            ->take(5)
            ->get();
        
        // Transaksi Terbaru
        $transaksiTerbaru = Transaksi::with('penyewa', 'kamar')
            ->latest('created_at')
            ->take(5)
            ->get();
        
        return view('admin.simk.dashboard', compact(
            'totalKamar', 'kamarTersedia', 'kamarTerisi', 'kamarMaintenance',
            'totalPenyewa', 'penyewaAktif', 'penyewaKeluar',
            'totalTagihan', 'totalTerbayar', 'totalTunggakan',
            'penyewaTerbaru', 'transaksiTerbaru'
        ));
    }
}
