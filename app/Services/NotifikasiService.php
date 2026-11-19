<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * UC-18 / ALGO-18 Kirim Notifikasi.
 * 1. Terima jenis kejadian, penerima, dan isi pesan.
 * 2. Simpan record t_notifikasi (dibaca = 0).
 * 3. Kirim email ke alamat pengguna.
 * 4. Jika email gagal, coba ulang maksimal 3 kali lalu catat di log.
 */
class NotifikasiService
{
    /** Bila SMTP mati, lewati email untuk sisa request agar tidak lambat. */
    private static bool $emailMati = false;

    public function kirim(Pengguna|string $penerima, string $jenis, string $judul, string $isi): ?Notifikasi
    {
        $pengguna = $penerima instanceof Pengguna ? $penerima : Pengguna::find($penerima);
        if (! $pengguna) {
            return null;
        }

        $notif = Notifikasi::create([
            'id_pengguna' => $pengguna->id_pengguna,
            'jenis' => $jenis,
            'judul' => mb_substr($judul, 0, 100),
            'isi' => $isi,
            'dibaca' => false,
            'tgl_kirim' => now(),
        ]);

        $this->kirimEmail($pengguna->email, $judul, $isi);

        return $notif;
    }

    /** Kirim ke seluruh Staf Unit Beasiswa dan Admin WR3 yang aktif. */
    public function kirimKeStaf(string $jenis, string $judul, string $isi): int
    {
        $staf = Pengguna::whereIn('role', [Pengguna::ROLE_STAF, Pengguna::ROLE_ADMIN])
            ->where('status_aktif', true)->get();

        foreach ($staf as $s) {
            $this->kirim($s, $jenis, $judul, $isi);
        }

        return $staf->count();
    }

    /** Email langsung (mis. kredensial akun mitra) tanpa menyimpan isi pesan. */
    public function kirimEmail(?string $alamat, string $subjek, string $isi): bool
    {
        if (! $alamat || self::$emailMati) {
            return false;
        }

        $maks = (int) config('beasiswa.email_maks_percobaan', 3);

        for ($i = 1; $i <= $maks; $i++) {
            try {
                Mail::raw($isi, fn ($m) => $m->to($alamat)->subject('[SIM-BEASISWA UKRI] '.$subjek));

                return true;
            } catch (Throwable $e) {
                Log::warning("Email ke {$alamat} gagal (percobaan {$i}/{$maks}): ".$e->getMessage());
            }
        }

        self::$emailMati = true;
        LogAktivitas::catat('email_gagal', 'notifikasi', "Email ke {$alamat} gagal setelah {$maks} percobaan: {$subjek}");

        return false;
    }

    public static function resetStatusEmail(): void
    {
        self::$emailMati = false;
    }
}
