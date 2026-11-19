<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pendaftaran;
use App\Models\SkemaBeasiswa;
use App\Services\NotifikasiService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** P-07 Verifikasi Berkas Pendaftar (UC-07, ALGO-07). */
class VerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'diajukan');

        $q = Pendaftaran::with('mahasiswa', 'skema')->withCount('dokumen')->orderBy('tgl_daftar');
        if ($status !== 'semua') {
            $q->where('status', $status);
        }
        if ($skema = $request->input('skema')) {
            $q->where('id_skema', $skema);
        }

        return view('seleksi.verifikasi', [
            'daftar' => $q->get(),
            'status' => $status,
            'skemaPilihan' => SkemaBeasiswa::orderBy('id_skema')->get(),
            'skemaDipilih' => $request->input('skema'),
        ]);
    }

    public function show(Pendaftaran $pendaftaran)
    {
        $pendaftaran->load('mahasiswa', 'skema', 'dokumen');

        return view('seleksi.verifikasi_detail', compact('pendaftaran'));
    }

    public function simpanHasil(Request $request, Pendaftaran $pendaftaran, NotifikasiService $notif)
    {
        if ($pendaftaran->status !== 'diajukan') {
            return back()->with('error', 'Hanya pendaftaran berstatus diajukan yang dapat diverifikasi.');
        }

        $request->validate([
            'hasil' => ['required', 'array'],
            'hasil.*' => ['in:valid,tidak_valid'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $dokumen = $pendaftaran->dokumen;
        $hasil = $request->input('hasil', []);

        // 1. Pastikan setiap dokumen sudah bernilai valid / tidak valid
        foreach ($dokumen as $d) {
            if (! isset($hasil[Kode::kunci($d->id_dokumen)])) {
                return back()->withInput()->with('error', 'Seluruh dokumen harus diperiksa sebelum menetapkan hasil.');
            }
        }

        // 2. IF ada dokumen tidak valid -> tidak_lengkap, catatan wajib
        $adaSalah = $dokumen->contains(fn ($d) => $hasil[Kode::kunci($d->id_dokumen)] === 'tidak_valid');
        if ($adaSalah && ! filled($request->input('catatan'))) {
            return back()->withInput()->with('error', 'Catatan wajib diisi apabila ada dokumen yang tidak valid.');
        }

        // 3-4. Simpan hasil dan beri tahu mahasiswa (UC-18)
        DB::transaction(function () use ($dokumen, $hasil, $adaSalah, $pendaftaran, $request) {
            foreach ($dokumen as $d) {
                $d->update(['status_verifikasi' => $hasil[Kode::kunci($d->id_dokumen)]]);
            }
            $pendaftaran->update([
                'status' => $adaSalah ? 'tidak_lengkap' : 'lolos_administrasi',
                'catatan_verifikasi' => $request->input('catatan'),
            ]);
        });

        $pendaftaran->load('mahasiswa.pengguna', 'skema');
        $notif->kirim($pendaftaran->mahasiswa->pengguna, 'seleksi',
            $adaSalah ? 'Berkas pendaftaran belum lengkap' : 'Berkas pendaftaran dinyatakan lengkap',
            $adaSalah
                ? "Pendaftaran {$pendaftaran->id_pendaftaran} ({$pendaftaran->skema->nama_skema}) belum lengkap. Catatan verifikator: {$request->input('catatan')}. Silakan unggah ulang berkas yang tidak valid."
                : "Berkas pendaftaran {$pendaftaran->id_pendaftaran} ({$pendaftaran->skema->nama_skema}) dinyatakan lolos administrasi dan akan masuk tahap penilaian.");

        LogAktivitas::catat('verifikasi', 'seleksi', "{$pendaftaran->id_pendaftaran} -> ".($adaSalah ? 'tidak_lengkap' : 'lolos_administrasi'));

        return redirect()->route('verifikasi.index')->with('success', 'Hasil verifikasi tersimpan dan mahasiswa diberi tahu.');
    }
}
