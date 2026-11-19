<?php

namespace App\Services;

use App\Models\Penyaluran;
use App\Models\SkemaBeasiswa;
use App\Models\StatusAkademik;
use Illuminate\Support\Carbon;

/**
 * Query pelaporan (QRY_REALISASI dst.) untuk P-13 Laporan dan P-14 Ekspor.
 * Setiap dataset: ['judul','header','tipe','rows','kosong'].
 */
class LaporanService
{
    public const JENIS = [
        'realisasi' => 'Laporan Realisasi Dana',
        'mitra' => 'Laporan untuk Mitra / Donor',
        'akademik' => 'Monitoring Status Akademik Penerima',
        'komprehensif' => 'Laporan Komprehensif Penyaluran',
    ];

    public function dataset(string $jenis, Carbon $awal, Carbon $akhir, ?string $idMitra = null): array
    {
        return match ($jenis) {
            'akademik' => $this->akademik($awal, $akhir),
            'komprehensif' => $this->komprehensif($awal, $akhir, $idMitra),
            default => $this->realisasi($awal, $akhir, $idMitra),   // realisasi & mitra
        };
    }

    /** QRY_REALISASI: agregasi nominal tersalur per skema (dibatasi per mitra bila ada). */
    public function realisasi(Carbon $awal, Carbon $akhir, ?string $idMitra = null): array
    {
        $skemas = SkemaBeasiswa::with(['komitmen.kontrak.mitra'])->orderBy('id_skema')->get();

        if ($idMitra) {
            $skemas = $skemas->filter(fn ($s) => $s->komitmen->contains(fn ($k) => $k->kontrak?->id_mitra === $idMitra));
        }

        $rows = [];
        $adaPenyaluran = false;

        foreach ($skemas as $s) {
            $kom = $s->komitmen;
            if ($idMitra) {
                $kom = $kom->filter(fn ($k) => $k->kontrak?->id_mitra === $idMitra);
            }

            $peny = Penyaluran::whereIn('status', ['tersalurkan', 'dikonfirmasi'])
                ->whereBetween('tgl_penyaluran', [$awal->toDateString(), $akhir->toDateString()])
                ->whereHas('penerima', fn ($q) => $q->where('id_skema', $s->id_skema))
                ->get();

            $adaPenyaluran = $adaPenyaluran || $peny->isNotEmpty();

            $komitmen = (float) $kom->sum('nominal_komitmen');
            $tersalur = (float) $peny->sum('nominal');

            $rows[] = [
                $s->nama_skema,
                $s->label_jenis,
                $kom->map(fn ($k) => $k->kontrak?->mitra?->nama_mitra)->filter()->unique()->implode(', ') ?: '-',
                $komitmen,
                (float) $kom->sum('nominal_diterima'),
                $tersalur,
                $peny->pluck('id_penerima')->unique()->count(),
                $komitmen - $tersalur,
            ];
        }

        return [
            'judul' => $idMitra ? self::JENIS['mitra'] : self::JENIS['realisasi'],
            'header' => ['Skema', 'Jenis', 'Mitra / Donor', 'Komitmen (Rp)', 'Dana Diterima (Rp)', 'Tersalur (Rp)', 'Jml Penerima', 'Sisa Komitmen (Rp)'],
            'tipe' => ['teks', 'teks', 'teks', 'uang', 'uang', 'uang', 'angka', 'uang'],
            'rows' => $adaPenyaluran ? $rows : [],
            'kosong' => ! $adaPenyaluran,
        ];
    }

    public function akademik(Carbon $awal, Carbon $akhir): array
    {
        $data = StatusAkademik::with('penerima.mahasiswa', 'penerima.skema')
            ->whereBetween('tgl_pembaruan', [$awal->toDateString(), $akhir->toDateString()])
            ->orderBy('semester')->get();

        $rows = $data->map(fn ($d) => [
            $d->penerima->npm,
            $d->penerima->mahasiswa->nama_lengkap,
            $d->penerima->mahasiswa->program_studi,
            $d->penerima->skema->nama_skema,
            $d->semester,
            number_format($d->ips, 2),
            number_format($d->ipk, 2),
            number_format($d->penerima->skema->ipk_minimum, 2),
            $d->status,
        ])->all();

        return [
            'judul' => self::JENIS['akademik'],
            'header' => ['NPM', 'Nama', 'Program Studi', 'Skema', 'Semester', 'IPS', 'IPK', 'IPK Min.', 'Status'],
            'tipe' => ['teks', 'teks', 'teks', 'teks', 'teks', 'teks', 'teks', 'teks', 'teks'],
            'rows' => $rows,
            'kosong' => empty($rows),
        ];
    }

    public function komprehensif(Carbon $awal, Carbon $akhir, ?string $idMitra = null): array
    {
        $q = Penyaluran::with('penerima.mahasiswa', 'penerima.skema.komitmen.kontrak.mitra')
            ->whereBetween('tgl_penyaluran', [$awal->toDateString(), $akhir->toDateString()])
            ->orderBy('tgl_penyaluran');

        $data = $q->get();

        if ($idMitra) {
            $data = $data->filter(fn ($p) => $p->penerima->skema->komitmen
                ->contains(fn ($k) => $k->kontrak?->id_mitra === $idMitra));
        }

        $rows = $data->map(fn ($p) => [
            $p->id_penyaluran,
            $p->penerima->npm,
            $p->penerima->mahasiswa->nama_lengkap,
            $p->penerima->skema->nama_skema,
            $p->penerima->skema->komitmen->map(fn ($k) => $k->kontrak?->mitra?->nama_mitra)->filter()->unique()->implode(', ') ?: '-',
            $p->termin_ke,
            $p->nominal,
            $p->status,
            $p->no_ref_keuangan ?? '-',
            optional($p->tgl_penyaluran)->format('d-m-Y') ?? '-',
            optional($p->tgl_konfirmasi)->format('d-m-Y') ?? '-',
        ])->values()->all();

        return [
            'judul' => self::JENIS['komprehensif'],
            'header' => ['ID Penyaluran', 'NPM', 'Nama', 'Skema', 'Mitra', 'Termin', 'Nominal (Rp)', 'Status', 'No. Ref Keuangan', 'Tgl Salur', 'Tgl Konfirmasi'],
            'tipe' => ['teks', 'teks', 'teks', 'teks', 'teks', 'angka', 'uang', 'teks', 'teks', 'teks', 'teks'],
            'rows' => $rows,
            'kosong' => empty($rows),
        ];
    }
}
