<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Penerima;
use App\Models\StatusAkademik;
use App\Services\NotifikasiService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** P-15 Monitoring Status Akademik Penerima (UC-15, ALGO-15). */
class MonitoringController extends Controller
{
    private function semesterSaatIni(): string
    {
        $n = now();

        // Ganjil: Agu-Des (tahun berjalan/1); Genap: Jan-Jul (tahun berjalan/2)
        return $n->month >= 8 ? $n->year.'/1' : $n->year.'/2';
    }

    public function index(Request $request)
    {
        $penerima = Penerima::with('mahasiswa', 'skema', 'statusAkademikTerakhir')
            ->where('status_penerima', 'aktif')->orderBy('id_penerima')->get();

        return view('monitoring.index', [
            'penerima' => $penerima,
            'semester' => $request->input('semester', $this->semesterSaatIni()),
        ]);
    }

    public function perbarui(Request $request, NotifikasiService $notif)
    {
        $request->validate([
            'semester' => ['required', 'regex:/^\d{4}\/[12]$/'],
            'data' => ['required', 'array'],
            'data.*.ips' => ['nullable', 'numeric', 'between:0,4'],
            'data.*.ipk' => ['nullable', 'numeric', 'between:0,4'],
        ], ['semester.regex' => 'Format semester harus seperti 2026/1.']);

        $semester = $request->input('semester');
        $diproses = 0;
        $peringatan = [];

        DB::transaction(function () use ($request, $semester, &$diproses, &$peringatan) {
            $peta = Penerima::where('status_penerima', 'aktif')->pluck('id_penerima', 'id_penerima')
                ->mapWithKeys(fn ($id) => [Kode::kunci($id) => $id]);

            foreach ($request->input('data') as $kunci => $v) {
                $idPenerima = $peta[$kunci] ?? null;
                if (! $idPenerima) {
                    continue;
                }

                if (! filled($v['ips'] ?? null) || ! filled($v['ipk'] ?? null)) {
                    continue;   // baris tidak diisi
                }

                $p = Penerima::with('mahasiswa', 'skema')->where('status_penerima', 'aktif')->find($idPenerima);
                if (! $p) {
                    continue;
                }

                $ipk = (float) $v['ipk'];

                // 1. IPS & IPK terbaru dari data akademik (t_mahasiswa)
                $p->mahasiswa->update(['ipk' => $ipk]);

                // 2-3. Evaluasi terhadap ipk_minimum skema
                if ($ipk >= $p->skema->ipk_minimum) {
                    $status = 'memenuhi';
                } else {
                    $sebelumnya = StatusAkademik::where('id_penerima', $p->id_penerima)
                        ->where('semester', '<', $semester)->orderByDesc('semester')->first();
                    $status = ($sebelumnya && in_array($sebelumnya->status, ['peringatan', 'tidak_memenuhi'], true))
                        ? 'tidak_memenuhi' : 'peringatan';
                }

                $baris = StatusAkademik::where('id_penerima', $p->id_penerima)->where('semester', $semester)->first();
                $isi = ['ips' => $v['ips'], 'ipk' => $ipk, 'status' => $status, 'tgl_pembaruan' => now()->toDateString()];
                $baris
                    ? $baris->update($isi)
                    : StatusAkademik::create($isi + [
                        'id_status' => Kode::berikutnya('t_status_akademik', 'id_status', 'SA', 5),
                        'id_penerima' => $p->id_penerima,
                        'semester' => $semester,
                    ]);

                $diproses++;
                if ($status !== 'memenuhi') {
                    $peringatan[] = "{$p->mahasiswa->nama_lengkap} ({$p->npm}) IPK ".number_format($ipk, 2)." < {$p->skema->ipk_minimum} - {$status}";
                }
            }
        });

        if ($diproses === 0) {
            return back()->withInput()->with('error', 'Isi IPS dan IPK minimal satu penerima.');
        }

        // 4. Simpan hasil dan kirim notifikasi ke staf
        if ($peringatan) {
            $notif->kirimKeStaf('sistem', 'Peringatan status akademik penerima',
                "IPK penerima di bawah batas minimum skema (semester {$semester}):\n".implode("\n", $peringatan));
        }
        LogAktivitas::catat('ubah', 'monitoring', "Status akademik semester {$semester}: {$diproses} penerima, ".count($peringatan).' peringatan');

        return back()->with('success', 'Status akademik diperbarui.'.($peringatan ? ' '.count($peringatan).' penerima di bawah batas minimum.' : ''));
    }
}
