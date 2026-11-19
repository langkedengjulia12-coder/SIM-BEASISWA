<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use App\Models\SkemaBeasiswa;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** P-08 Penilaian dan Ranking Seleksi (UC-08, ALGO-08). */
class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $skemaList = SkemaBeasiswa::where('status', '!=', 'draf')->orderByDesc('id_skema')->get();
        $skema = $skemaList->firstWhere('id_skema', $request->input('skema'));
        $baris = collect();

        if ($skema) {
            $baris = Pendaftaran::with('mahasiswa', 'penilaian')
                ->where('id_skema', $skema->id_skema)
                ->whereIn('status', ['lolos_administrasi', 'dinilai'])
                ->get()
                ->sortBy(fn ($p) => [$p->penilaian?->peringkat ?? 9999, -$p->ipk_saat_daftar])
                ->values();
        }

        return view('seleksi.penilaian', compact('skemaList', 'skema', 'baris'));
    }

    public function hitung(Request $request, SkemaBeasiswa $skema)
    {
        if ($skema->sudahDitetapkan()) {
            return back()->with('error', 'Hasil seleksi skema ini sudah ditetapkan.');
        }

        $pendaftar = Pendaftaran::with('penilaian')
            ->where('id_skema', $skema->id_skema)
            ->whereIn('status', ['lolos_administrasi', 'dinilai'])->get();

        if ($pendaftar->isEmpty()) {
            return back()->with('error', 'Belum ada pendaftar yang lolos administrasi.');
        }

        // 1. Validasi skor tiap pendaftar 0 - 100
        $request->validate([
            'skor' => ['required', 'array'],
            'skor.*.akademik' => ['required', 'numeric', 'between:0,100'],
            'skor.*.nonakademik' => ['required', 'numeric', 'between:0,100'],
            'skor.*.kebutuhan' => ['required', 'numeric', 'between:0,100'],
        ], [
            'skor.*.*.between' => 'Skor harus antara 0 dan 100.',
            'skor.*.*.numeric' => 'Skor harus antara 0 dan 100.',
            'skor.*.*.required' => 'Seluruh skor pendaftar wajib diisi.',
        ]);

        $skor = $request->input('skor');
        foreach ($pendaftar as $p) {
            if (! isset($skor[Kode::kunci($p->id_pendaftaran)])) {
                return back()->withInput()->with('error', 'Seluruh skor pendaftar wajib diisi.');
            }
        }

        // 2. nilai_total = jumlah (skor kriteria x bobot kriteria)
        $b = $skema->bobot;
        $hasil = $pendaftar->map(function ($p) use ($skor, $b) {
            $s = $skor[Kode::kunci($p->id_pendaftaran)];
            $total = ($s['akademik'] * $b['akademik'] + $s['nonakademik'] * $b['nonakademik'] + $s['kebutuhan'] * $b['kebutuhan']) / 100;

            return ['p' => $p, 's' => $s, 'total' => round($total, 2)];
        })
            // 3. Urut menurun menurut nilai_total; bila sama utamakan IPK lebih tinggi
            ->sort(function ($x, $y) {
                return [$y['total'], $y['p']->ipk_saat_daftar, $x['p']->tgl_daftar->timestamp]
                    <=> [$x['total'], $x['p']->ipk_saat_daftar, $y['p']->tgl_daftar->timestamp];
            })->values();

        // 4. Simpan peringkat dan ubah status menjadi dinilai
        DB::transaction(function () use ($hasil, $request) {
            foreach ($hasil as $i => $h) {
                $p = $h['p'];
                $nilai = [
                    'nilai_akademik' => $h['s']['akademik'],
                    'nilai_nonakademik' => $h['s']['nonakademik'],
                    'nilai_kebutuhan' => $h['s']['kebutuhan'],
                    'nilai_total' => $h['total'],
                    'peringkat' => $i + 1,
                    'id_penilai' => $request->user()->id_pengguna,
                    'tgl_penilaian' => now(),
                ];
                if ($p->penilaian) {
                    $p->penilaian->update($nilai);
                } else {
                    Penilaian::create($nilai + [
                        'id_penilaian' => Kode::berikutnya('t_penilaian', 'id_penilaian', 'NL', 5),
                        'id_pendaftaran' => $p->id_pendaftaran,
                    ]);
                }
                $p->update(['status' => 'dinilai']);
            }
        });

        LogAktivitas::catat('ubah', 'seleksi', "Ranking skema {$skema->id_skema}: {$hasil->count()} pendaftar");

        return back()->with('success', 'Peringkat berhasil dihitung.');
    }
}
