<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\LogAktivitas;
use App\Models\Mitra;
use App\Services\DokumenService;
use App\Services\ExportService;
use App\Services\LaporanService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

/** P-14 Ekspor Data Periodik (UC-14, ALGO-14). */
class EksporController extends Controller
{
    public function index()
    {
        return view('ekspor.index', [
            'jenisList' => LaporanService::JENIS,
            'mitraList' => Mitra::where('status_verifikasi', 'terverifikasi')->orderBy('nama_mitra')->get(),
            'riwayat' => Laporan::with('mitra', 'pembuat')->orderByDesc('id_laporan')->limit(15)->get(),
        ]);
    }

    public function proses(Request $request, LaporanService $svc, ExportService $exp, DokumenService $dok)
    {
        // 1. Validasi pilihan
        $data = $request->validate([
            'jenis' => ['required', 'in:realisasi,mitra,akademik,komprehensif'],
            'id_mitra' => ['nullable', 'required_if:jenis,mitra', 'exists:t_mitra,id_mitra'],
            'periode_awal' => ['required', 'date'],
            'periode_akhir' => ['required', 'date', 'after_or_equal:periode_awal'],
            'format' => ['required', 'in:PDF,XLSX,CSV'],
        ], [
            'periode_akhir.after_or_equal' => 'Periode akhir harus setelah periode awal.',
            'id_mitra.required_if' => 'Pilih mitra untuk jenis laporan mitra.',
        ]);

        $awal = Carbon::parse($data['periode_awal']);
        $akhir = Carbon::parse($data['periode_akhir']);
        $idMitra = $data['jenis'] === 'mitra' ? $data['id_mitra'] : null;

        // 2. Bangkitkan berkas dari query sesuai jenis data
        try {
            $dataset = $svc->dataset($data['jenis'], $awal, $akhir, $idMitra);
            if ($dataset['kosong']) {
                return back()->withInput()->with('error', 'Tidak ada data pada periode terpilih.');
            }

            $file = $exp->buat($data['format'], $dataset, $awal, $akhir);
            $nama = 'ekspor_'.$data['jenis'].'_'.now()->format('Ymd_His').'.'.$file['ext'];

            // 3. Simpan jalur berkas pada t_laporan dan catat log ekspor
            $simpan = $dok->simpanKonten($file['isi'], $nama, 'laporan');
            Laporan::create([
                'id_laporan' => Kode::berikutnya('t_laporan', 'id_laporan', 'LP', 5),
                'jenis_laporan' => $data['jenis'],
                'id_mitra' => $idMitra,
                'periode_awal' => $awal->toDateString(),
                'periode_akhir' => $akhir->toDateString(),
                'format_file' => $data['format'],
                'path_file' => $simpan['path'].'|'.$simpan['nama'],
                'id_pembuat' => $request->user()->id_pengguna,
                'tgl_generate' => now(),
            ]);
            LogAktivitas::catat('ekspor', 'laporan', "{$data['jenis']} {$data['format']} {$data['periode_awal']} s.d. {$data['periode_akhir']}");
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Berkas gagal dibuat, coba lagi.');
        }

        // 4. Kirim berkas ke peramban pengguna
        session()->flash('success', 'Berkas berhasil dibuat dan diunduh.');

        return response($file['isi'], 200, [
            'Content-Type' => $file['mime'],
            'Content-Disposition' => 'attachment; filename="'.$nama.'"',
        ]);
    }
}
