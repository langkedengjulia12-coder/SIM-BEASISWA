<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\SkemaBeasiswa;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** P-05 Kelola Skema Beasiswa (UC-05, ALGO-05). */
class SkemaController extends Controller
{
    public function index()
    {
        $skema = SkemaBeasiswa::withCount(['pendaftaran', 'penerima'])->orderByDesc('id_skema')->get();

        return view('skema.index', [
            'skema' => $skema,
            'jenis' => SkemaBeasiswa::JENIS,
            'aturan' => SkemaBeasiswa::ATURAN,
        ]);
    }

    private function aturan(): array
    {
        return [
            'nama_skema' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(array_keys(SkemaBeasiswa::JENIS))],
            'kuota' => ['required', 'integer', 'min:1', 'max:32000'],
            'nominal_per_penerima' => ['required', 'numeric', 'min:0'],
            'ipk_minimum' => ['required', 'numeric', 'between:0,4'],
            'bobot_akademik' => ['required', 'integer', 'between:0,100'],
            'bobot_nonakademik' => ['required', 'integer', 'between:0,100'],
            'bobot_kebutuhan' => ['required', 'integer', 'between:0,100'],
            'tgl_buka' => ['required', 'date'],
            'tgl_tutup' => ['required', 'date', 'after_or_equal:tgl_buka'],
            'aturan_penyaluran' => ['required', Rule::in(array_keys(SkemaBeasiswa::ATURAN))],
        ];
    }

    private function pesan(): array
    {
        return ['tgl_tutup.after_or_equal' => 'Tanggal tutup harus sama dengan atau setelah tanggal buka.'];
    }

    private function atribut(array $d): array
    {
        return [
            'nama_skema' => $d['nama_skema'],
            'jenis' => $d['jenis'],
            'kuota' => $d['kuota'],
            'nominal_per_penerima' => $d['nominal_per_penerima'],
            'ipk_minimum' => $d['ipk_minimum'],
            'kriteria_seleksi' => json_encode([
                'akademik' => (int) $d['bobot_akademik'],
                'nonakademik' => (int) $d['bobot_nonakademik'],
                'kebutuhan' => (int) $d['bobot_kebutuhan'],
            ]),
            'tgl_buka' => $d['tgl_buka'],
            'tgl_tutup' => $d['tgl_tutup'],
            'aturan_penyaluran' => $d['aturan_penyaluran'],
        ];
    }

    /** Langkah 1 ALGO-05: total bobot kriteria harus 100%. */
    private function bobotValid(array $d): bool
    {
        return ((int) $d['bobot_akademik'] + (int) $d['bobot_nonakademik'] + (int) $d['bobot_kebutuhan']) === 100;
    }

    /** Langkah 2-3 ALGO-05: kebutuhan dana tidak boleh melebihi sisa komitmen terkait. */
    private function danaKurang(SkemaBeasiswa $s): bool
    {
        return $s->kebutuhan_dana > $s->sisaKomitmen();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        if (! $this->bobotValid($data)) {
            return back()->withInput()->with('error', 'Total bobot kriteria harus 100%.');
        }

        $skema = DB::transaction(fn () => SkemaBeasiswa::create($this->atribut($data) + [
            'id_skema' => Kode::berikutnya('t_skema_beasiswa', 'id_skema', 'SB', 3),
            'status' => 'draf',
        ]));

        LogAktivitas::catat('tambah', 'skema', "Skema {$skema->id_skema} - {$skema->nama_skema}");

        return back()->with('success', 'Skema berhasil disimpan.');
    }

    public function update(Request $request, SkemaBeasiswa $skema)
    {
        if ($skema->sudahDitetapkan()) {
            return back()->with('error', 'Skema yang sudah memiliki penerima tidak dapat diubah.');
        }

        $data = $request->validate($this->aturan(), $this->pesan());

        if (! $this->bobotValid($data)) {
            return back()->withInput()->with('error', 'Total bobot kriteria harus 100%.');
        }

        $skema->fill($this->atribut($data));

        // Skema yang sudah punya komitmen terkait atau sudah dibuka wajib lolos cek dana
        if (($skema->komitmen()->exists() || $skema->status !== 'draf') && $this->danaKurang($skema)) {
            return back()->withInput()->with('error', 'Kuota x nominal melebihi komitmen dana yang tersedia.');
        }

        $skema->save();
        LogAktivitas::catat('ubah', 'skema', "Skema {$skema->id_skema}");

        return back()->with('success', 'Skema berhasil disimpan.');
    }

    /** ALGO-05b Buka Pendaftaran. */
    public function buka(SkemaBeasiswa $skema)
    {
        if ($skema->status === 'dibuka') {
            return back()->with('error', 'Skema sudah dibuka.');
        }
        if ($skema->sudahDitetapkan()) {
            return back()->with('error', 'Skema yang sudah memiliki penerima tidak dapat dibuka kembali.');
        }
        if ($skema->tgl_tutup->lt(now()->startOfDay())) {
            return back()->with('error', 'Periode pendaftaran sudah lewat. Ubah tanggal tutup terlebih dahulu.');
        }
        if ($this->danaKurang($skema)) {
            return back()->with('error', 'Kuota x nominal melebihi komitmen dana yang tersedia.');
        }

        $skema->update(['status' => 'dibuka']);
        LogAktivitas::catat('ubah', 'skema', "Skema {$skema->id_skema} dibuka");

        return back()->with('success', 'Skema berhasil dibuka dan kini terlihat oleh mahasiswa.');
    }

    public function tutup(SkemaBeasiswa $skema)
    {
        if ($skema->status !== 'dibuka') {
            return back()->with('error', 'Hanya skema berstatus dibuka yang dapat ditutup.');
        }
        $skema->update(['status' => 'ditutup']);
        LogAktivitas::catat('ubah', 'skema', "Skema {$skema->id_skema} ditutup");

        return back()->with('success', 'Pendaftaran skema ditutup.');
    }

    public function selesai(SkemaBeasiswa $skema)
    {
        if ($skema->status !== 'ditutup') {
            return back()->with('error', 'Tutup pendaftaran terlebih dahulu.');
        }
        $skema->update(['status' => 'selesai']);
        LogAktivitas::catat('ubah', 'skema', "Skema {$skema->id_skema} selesai");

        return back()->with('success', 'Skema ditandai selesai.');
    }
}
