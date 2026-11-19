<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Penerima;
use App\Models\Penyaluran;
use App\Models\SkemaBeasiswa;
use App\Services\KeuanganApiService;
use App\Services\NotifikasiService;
use App\Support\Kode;
use Illuminate\Http\Request;

/** P-11 Verifikasi Penerima dan Salurkan Dana (UC-11), P-12 Konfirmasi Penerimaan (UC-12). */
class PenyaluranController extends Controller
{
    public function index(Request $request)
    {
        $skemaList = SkemaBeasiswa::whereHas('penerima')->orderByDesc('id_skema')->get();
        $skema = $skemaList->firstWhere('id_skema', $request->input('skema'));
        $termin = max(1, (int) $request->input('termin', 1));
        $baris = $skema ? $this->verifikasi($skema, $termin) : collect();

        return view('penyaluran.index', compact('skemaList', 'skema', 'termin', 'baris'));
    }

    /**
     * Verifikasi penerima (langkah 1-2 ALGO-11): kembalikan status kelayakan tiap penerima aktif.
     *
     * @return \Illuminate\Support\Collection<int,array{penerima:Penerima,layak:bool,alasan:?string,penyaluran:?Penyaluran}>
     */
    public function verifikasi(SkemaBeasiswa $skema, int $termin)
    {
        return Penerima::with('mahasiswa', 'statusAkademikTerakhir')
            ->where('id_skema', $skema->id_skema)->where('status_penerima', 'aktif')
            ->orderBy('id_penerima')->get()
            ->map(function ($p) use ($termin) {
                $peny = Penyaluran::where('id_penerima', $p->id_penerima)->where('termin_ke', $termin)->first();
                $alasan = null;

                if ($peny && $peny->status !== 'gagal') {
                    $alasan = 'Sudah diproses pada termin ini ('.$peny->status.').';
                } elseif (blank($p->mahasiswa->no_rekening)) {
                    $alasan = 'Data rekening penerima belum lengkap.';
                } elseif ($p->mahasiswa->status_akademik !== 'aktif') {
                    $alasan = 'Status mahasiswa '.$p->mahasiswa->status_akademik.' (bukan aktif).';
                } elseif (optional($p->statusAkademikTerakhir)->status === 'tidak_memenuhi') {
                    $alasan = 'Status akademik tidak memenuhi syarat skema.';
                }

                return ['penerima' => $p, 'layak' => $alasan === null, 'alasan' => $alasan, 'penyaluran' => $peny];
            });
    }

    /** ALGO-11 Salurkan. */
    public function salurkan(Request $request, KeuanganApiService $keu, NotifikasiService $notif)
    {
        $data = $request->validate([
            'id_skema' => ['required', 'exists:t_skema_beasiswa,id_skema'],
            'termin_ke' => ['required', 'integer', 'min:1'],
        ]);

        $skema = SkemaBeasiswa::findOrFail($data['id_skema']);
        $termin = (int) $data['termin_ke'];

        if ($termin > $skema->jumlah_termin) {
            return back()->with('error', "Skema ini hanya memiliki {$skema->jumlah_termin} termin penyaluran.");
        }

        $berhasil = $gagal = 0;
        $dilewati = [];

        foreach ($this->verifikasi($skema, $termin) as $b) {
            $p = $b['penerima'];

            // 2. Lewati penerima yang tidak layak dan catat alasannya
            if (! $b['layak']) {
                $dilewati[] = $p->mahasiswa->nama_lengkap.' ('.$p->npm.'): '.$b['alasan'];
                continue;
            }

            // 3. Buat record t_penyaluran (menunggu), kirim instruksi ke API sistem keuangan
            $peny = $b['penyaluran'] ?? new Penyaluran(['id_penyaluran' => Kode::berikutnya('t_penyaluran', 'id_penyaluran', 'PY', 5)]);
            $peny->fill([
                'id_penerima' => $p->id_penerima,
                'termin_ke' => $termin,
                'nominal' => $skema->nominal_per_penerima,
                'status' => 'menunggu',
                'id_petugas' => $request->user()->id_pengguna,
            ])->save();

            $res = $keu->kirimInstruksi($peny);

            // 4. Respons sukses -> simpan no_ref_keuangan; selain itu -> gagal
            if ($res['ok']) {
                $peny->update([
                    'no_ref_keuangan' => $res['ref'],
                    'status' => $res['status'],
                    'tgl_penyaluran' => now()->toDateString(),
                ]);
                $berhasil++;
                $notif->kirim($p->mahasiswa->pengguna, 'penyaluran', 'Dana beasiswa disalurkan',
                    'Dana beasiswa '.$skema->nama_skema.' termin '.$termin.' sebesar '.Kode::rupiah($peny->nominal)
                    .' telah disalurkan (ref: '.$res['ref'].'). Mohon konfirmasi penerimaan pada menu Konfirmasi Dana.');
            } else {
                $peny->update(['status' => 'gagal']);
                $gagal++;
                $dilewati[] = $p->mahasiswa->nama_lengkap.' ('.$p->npm.'): Sistem keuangan tidak merespons. Penyaluran ditandai gagal.';
                $notif->kirim($p->mahasiswa->pengguna, 'penyaluran', 'Penyaluran dana tertunda',
                    'Penyaluran dana beasiswa termin '.$termin.' belum berhasil diproses dan akan dicoba ulang oleh petugas.');
            }
        }

        LogAktivitas::catat('tambah', 'penyaluran', "Salurkan {$skema->id_skema} termin {$termin}: {$berhasil} berhasil, {$gagal} gagal, ".count($dilewati).' dilewati/gagal');

        $pesan = "Instruksi penyaluran berhasil dikirim untuk {$berhasil} penerima.";
        if ($gagal) {
            $pesan .= " {$gagal} penyaluran gagal diproses.";
        }

        return back()->with($berhasil > 0 || ! $gagal ? 'success' : 'error', $pesan)->with('dilewati', $dilewati);
    }

    // ---------- Sisi mahasiswa ----------

    public function saya(Request $request)
    {
        $mhs = $request->user()->mahasiswa;

        $daftar = $mhs
            ? Penyaluran::with('penerima.skema')
                ->whereHas('penerima', fn ($q) => $q->where('npm', $mhs->npm))
                ->orderByDesc('id_penyaluran')->get()
            : collect();

        return view('penyaluran.konfirmasi', ['daftar' => $daftar, 'mhs' => $mhs]);
    }

    /** ALGO-12 Konfirmasi Penerimaan Dana. */
    public function konfirmasi(Request $request, Penyaluran $penyaluran)
    {
        $mhs = $request->user()->mahasiswa;
        abort_unless($mhs && $penyaluran->penerima->npm === $mhs->npm, 403);

        if ($penyaluran->status !== 'tersalurkan') {
            return back()->with('error', 'Dana belum berstatus tersalurkan.');
        }

        $penyaluran->update(['status' => 'dikonfirmasi', 'tgl_konfirmasi' => now()->toDateString()]);
        LogAktivitas::catat('ubah', 'penyaluran', "Konfirmasi penerimaan {$penyaluran->id_penyaluran}");

        return back()->with('success', 'Terima kasih, konfirmasi Anda tersimpan.');
    }
}
