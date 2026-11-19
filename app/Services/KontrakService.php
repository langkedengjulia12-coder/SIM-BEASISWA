<?php

namespace App\Services;

use App\Models\Kontrak;
use App\Models\Notifikasi;

/** Pemeriksaan masa berlaku kontrak (ALGO-03 langkah 4-5) -> memicu UC-18. */
class KontrakService
{
    public function __construct(private NotifikasiService $notif)
    {
    }

    /**
     * Kontrak aktif dengan sisa hari <= 30 -> kirim notifikasi ke staf & PIC mitra
     * (maksimal sekali per 7 hari per kontrak agar tidak membanjiri).
     */
    public function periksa(Kontrak $kontrak): bool
    {
        if (! in_array($kontrak->status, ['aktif', 'diperpanjang'], true)) {
            return false;
        }

        $sisa = $kontrak->sisa_hari;
        if ($sisa > (int) config('beasiswa.kontrak_peringatan_hari') || $sisa < 0) {
            return false;
        }

        $judul = "Kontrak {$kontrak->id_kontrak} akan berakhir";
        $sudah = Notifikasi::where('jenis', 'masa_berlaku')->where('judul', $judul)
            ->where('tgl_kirim', '>=', now()->subDays(7))->exists();
        if ($sudah) {
            return false;
        }

        $kontrak->loadMissing('mitra.pengguna');
        $isi = "Kontrak {$kontrak->no_dokumen} dengan {$kontrak->mitra->nama_mitra} akan berakhir pada "
            .$kontrak->tgl_berakhir->format('d-m-Y')." ({$sisa} hari lagi). Kontrak akan berakhir dalam 30 hari.";

        $this->notif->kirimKeStaf('masa_berlaku', $judul, $isi);

        if ($kontrak->mitra->pengguna) {
            $this->notif->kirim($kontrak->mitra->pengguna, 'masa_berlaku', $judul, $isi);
        } else {
            $this->notif->kirimEmail($kontrak->mitra->email_pic, $judul, $isi);
        }

        return true;
    }

    /** Kontrak yang melewati tgl_berakhir diubah menjadi berakhir. */
    public function tandaiBerakhir(): int
    {
        return Kontrak::berlaku()->whereDate('tgl_berakhir', '<', now()->toDateString())
            ->update(['status' => 'berakhir']);
    }
}
