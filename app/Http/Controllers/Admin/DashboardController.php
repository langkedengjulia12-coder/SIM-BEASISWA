<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kamar;
use App\Models\Penyewa;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Utilitas;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard
     * Ringkasan utama dashboard
     */
    public function index(): JsonResponse
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // ---- Kamar ----
        $kamar = Kamar::selectRaw("
            COUNT(*) as total,
            SUM(status = 'tersedia')    as tersedia,
            SUM(status = 'terisi')      as terisi,
            SUM(status = 'maintenance') as maintenance
        ")->first();

        // ---- Penyewa ----
        $penyewa = Penyewa::selectRaw("
            COUNT(*) as total,
            SUM(status = 'aktif')  as aktif,
            SUM(status = 'keluar') as keluar
        ")->first();

        // ---- Keuangan bulan ini ----
        $keuangan = Transaksi::where('periode_bulan', $bulanIni)
            ->where('periode_tahun', $tahunIni)
            ->selectRaw("
                SUM(jumlah) as total_tagihan,
                SUM(CASE WHEN status = 'lunas' THEN jumlah ELSE 0 END) as total_terbayar,
                SUM(CASE WHEN status IN ('belum_bayar','terlambat') THEN jumlah ELSE 0 END) as total_tunggakan,
                COUNT(CASE WHEN status = 'lunas' THEN 1 END) as jumlah_lunas,
                COUNT(CASE WHEN status IN ('belum_bayar','terlambat') THEN 1 END) as jumlah_belum_bayar
            ")->first();

        // ---- Kontrak akan habis (30 hari ke depan) ----
        $kontrakHampirHabis = Penyewa::where('status', 'aktif')
            ->whereBetween('kontrak_selesai', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->with('kamar:id,nomor_kamar')
            ->get(['id', 'nama_lengkap', 'kamar_id', 'kontrak_selesai', 'no_hp']);

        // ---- Transaksi terlambat ----
        $terlambat = Transaksi::where('status', 'terlambat')
            ->with(['penyewa:id,nama_lengkap', 'kamar:id,nomor_kamar'])
            ->orderBy('jatuh_tempo')
            ->limit(5)
            ->get();

        // ---- Transaksi terbaru ----
        $transaksiTerbaru = Transaksi::with(['penyewa:id,nama_lengkap', 'kamar:id,nomor_kamar'])
            ->latest()
            ->limit(5)
            ->get();

        // ---- Tingkat hunian ----
        $tingkatHunian = $kamar->total > 0
            ? round(($kamar->terisi / $kamar->total) * 100, 1)
            : 0;

        return $this->success([
            'kamar'                => $kamar,
            'penyewa'              => $penyewa,
            'keuangan_bulan_ini'   => $keuangan,
            'tingkat_hunian'       => $tingkatHunian,
            'kontrak_hampir_habis' => $kontrakHampirHabis,
            'transaksi_terlambat'  => $terlambat,
            'transaksi_terbaru'    => $transaksiTerbaru,
            'periode'              => ['bulan' => $bulanIni, 'tahun' => $tahunIni],
        ], 'Dashboard berhasil dimuat');
    }

    /**
     * GET /api/v1/dashboard/statistik
     * Statistik lebih lengkap
     */
    public function statistik(): JsonResponse
    {
        // Distribusi tipe kamar
        $distribusiTipe = Kamar::selectRaw('tipe, COUNT(*) as total, SUM(status="terisi") as terisi')
            ->groupBy('tipe')
            ->get();

        // Distribusi jenis kelamin penyewa aktif
        $distribusiGender = Penyewa::where('status', 'aktif')
            ->selectRaw('jenis_kelamin, COUNT(*) as total')
            ->groupBy('jenis_kelamin')
            ->get();

        // Distribusi pekerjaan penyewa aktif
        $distribusiPekerjaan = Penyewa::where('status', 'aktif')
            ->selectRaw('pekerjaan, COUNT(*) as total')
            ->groupBy('pekerjaan')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Total pendapatan 6 bulan terakhir
        $enamBulan = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            $bulan = $date->month;
            $tahun = $date->year;
            $total = Transaksi::where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->where('status', 'lunas')
                ->sum('jumlah');
            return [
                'label'    => $date->translatedFormat('M Y'),
                'bulan'    => $bulan,
                'tahun'    => $tahun,
                'pendapatan' => (float) $total,
            ];
        });

        // Utilitas bulan ini
        $utilitasBulanIni = Utilitas::where('periode_bulan', now()->month)
            ->where('periode_tahun', now()->year)
            ->selectRaw("
                jenis,
                COUNT(*) as jumlah_kamar,
                SUM(pemakaian) as total_pemakaian,
                SUM(total_tagihan) as total_tagihan
            ")
            ->groupBy('jenis')
            ->get();

        return $this->success([
            'distribusi_tipe_kamar'   => $distribusiTipe,
            'distribusi_gender'       => $distribusiGender,
            'distribusi_pekerjaan'    => $distribusiPekerjaan,
            'pendapatan_6_bulan'      => $enamBulan,
            'utilitas_bulan_ini'      => $utilitasBulanIni,
        ]);
    }

    /**
     * GET /api/v1/dashboard/pendapatan-bulanan
     */
    public function pendapatanBulanan(Request $request): JsonResponse
    {
        $request->validate(['tahun' => 'nullable|integer|min:2020']);
        $tahun = $request->tahun ?? now()->year;

        $data = collect(range(1, 12))->map(function ($bulan) use ($tahun) {
            $result = Transaksi::where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->selectRaw("
                    SUM(CASE WHEN status='lunas' THEN jumlah ELSE 0 END) as pendapatan,
                    SUM(CASE WHEN status IN ('belum_bayar','terlambat') THEN jumlah ELSE 0 END) as tunggakan,
                    COUNT(CASE WHEN jenis='sewa' AND status='lunas' THEN 1 END) as sewa_lunas,
                    COUNT(CASE WHEN jenis='utilitas' AND status='lunas' THEN 1 END) as utilitas_lunas
                ")->first();

            return [
                'bulan'          => $bulan,
                'label'          => now()->setMonth($bulan)->translatedFormat('M'),
                'pendapatan'     => (float) $result->pendapatan,
                'tunggakan'      => (float) $result->tunggakan,
                'sewa_lunas'     => (int) $result->sewa_lunas,
                'utilitas_lunas' => (int) $result->utilitas_lunas,
            ];
        });

        return $this->success([
            'tahun'          => $tahun,
            'data'           => $data,
            'total_tahunan'  => $data->sum('pendapatan'),
        ]);
    }
}