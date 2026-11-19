<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pendaftaran;
use App\Models\Penerima;
use App\Models\SkemaBeasiswa;
use App\Services\NotifikasiService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** P-09 Tetapkan dan Umumkan Hasil Seleksi (UC-09, ALGO-09). */
class PenetapanController extends Controller
{
    private function cadangan(SkemaBeasiswa $s): int
    {
        return (int) ceil($s->kuota * config('beasiswa.cadangan_persen') / 100);
    }

    public function index(Request $request)
    {
        $skemaList = SkemaBeasiswa::where('status', '!=', 'draf')->orderByDesc('id_skema')->get();
        $skema = $skemaList->firstWhere('id_skema', $request->input('skema'));
        $peringkat = collect();
        $kendala = [];
        $cadangan = 0;

        if ($skema) {
            $cadangan = $this->cadangan($skema);
            $peringkat = Pendaftaran::with('mahasiswa', 'penilaian', 'penerima')
                ->where('id_skema', $skema->id_skema)
                ->whereIn('status', ['dinilai', 'diterima', 'cadangan', 'ditolak'])
                ->get()
                ->filter(fn ($p) => $p->penilaian)
                ->sortBy(fn ($p) => $p->penilaian->peringkat)->values();

            $kendala = $this->kendala($skema);
        }

        return view('seleksi.penetapan', compact('skemaList', 'skema', 'peringkat', 'kendala', 'cadangan'));
    }

    /** Syarat sebelum penetapan (Initial State ALGO-09). */
    private function kendala(SkemaBeasiswa $skema): array
    {
        $k = [];
        if ($skema->sudahDitetapkan()) {
            $k[] = 'Hasil seleksi skema ini sudah ditetapkan.';

            return $k;
        }
        if ($skema->status === 'dibuka') {
            $k[] = 'Tutup pendaftaran skema terlebih dahulu (menu Skema Beasiswa).';
        }
        $belum = Pendaftaran::where('id_skema', $skema->id_skema)->whereIn('status', ['diajukan', 'lolos_administrasi'])->count();
        if ($belum > 0) {
            $k[] = "Masih ada {$belum} pendaftar yang belum diverifikasi / dinilai. Seluruh pendaftar skema harus berstatus dinilai.";
        }
        if (Pendaftaran::where('id_skema', $skema->id_skema)->where('status', 'dinilai')->count() === 0) {
            $k[] = 'Belum ada pendaftar yang dinilai.';
        }

        return $k;
    }

    public function tetapkan(Request $request, SkemaBeasiswa $skema, NotifikasiService $notif)
    {
        $data = $request->validate([
            'no_sk' => ['required', 'string', 'max:50'],
            'tgl_penetapan' => ['required', 'date'],
        ]);

        if ($k = $this->kendala($skema)) {
            return back()->with('error', $k[0]);
        }

        $cadangan = $this->cadangan($skema);

        // 1. Ambil pendaftar skema terurut peringkat
        $pendaftar = Pendaftaran::with('mahasiswa.pengguna', 'penilaian')
            ->where('id_skema', $skema->id_skema)
            ->whereIn('status', ['dinilai', 'tidak_lengkap'])->get()
            ->sortBy(fn ($p) => $p->penilaian?->peringkat ?? 99999)->values();

        $pesan = [];

        DB::transaction(function () use ($pendaftar, $skema, $data, $cadangan, &$pesan) {
            foreach ($pendaftar as $p) {
                $rank = $p->penilaian?->peringkat;

                if ($rank && $rank <= $skema->kuota) {
                    // 2. Peringkat 1 s.d. kuota -> penerima aktif
                    Penerima::create([
                        'id_penerima' => Kode::berikutnya('t_penerima', 'id_penerima', 'PN', 5),
                        'id_pendaftaran' => $p->id_pendaftaran,
                        'id_skema' => $skema->id_skema,
                        'npm' => $p->npm,
                        'no_sk' => $data['no_sk'],
                        'tgl_penetapan' => $data['tgl_penetapan'],
                        'status_penerima' => 'aktif',
                    ]);
                    $p->update(['status' => 'diterima']);
                    $pesan[] = [$p, 'Selamat! Anda ditetapkan sebagai penerima beasiswa',
                        "Anda DITERIMA sebagai penerima {$skema->nama_skema} (peringkat {$rank}) berdasarkan SK {$data['no_sk']}. Pastikan data rekening Anda sudah lengkap pada menu Profil & Rekening."];
                } elseif ($rank && $rank <= $skema->kuota + $cadangan) {
                    // 3. Berikutnya sampai 20% kuota -> cadangan
                    $p->update(['status' => 'cadangan']);
                    $pesan[] = [$p, 'Hasil seleksi: daftar cadangan',
                        "Anda masuk daftar CADANGAN {$skema->nama_skema} (peringkat {$rank}). Kami akan menghubungi Anda bila ada kuota yang kosong."];
                } else {
                    // sisanya ditolak (termasuk berkas yang tetap tidak lengkap)
                    $p->update(['status' => 'ditolak']);
                    $pesan[] = [$p, 'Hasil seleksi beasiswa',
                        "Mohon maaf, Anda belum dapat ditetapkan sebagai penerima {$skema->nama_skema}. Terima kasih atas partisipasi Anda."];
                }
            }
        });

        // 4. Umumkan (UC-18) ke seluruh pendaftar
        $this->umumkan($pesan, $notif);

        LogAktivitas::catat('ubah', 'seleksi', "Penetapan skema {$skema->id_skema} (SK {$data['no_sk']})");

        return back()->with('success', 'Hasil seleksi berhasil ditetapkan.');
    }

    private function umumkan(array $pesan, NotifikasiService $notif): void
    {
        foreach ($pesan as [$p, $judul, $isi]) {
            $notif->kirim($p->mahasiswa->pengguna, 'seleksi', $judul, $isi);
        }
    }
}
