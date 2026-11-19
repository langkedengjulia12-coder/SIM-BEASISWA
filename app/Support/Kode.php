<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Pembangkit kode data sesuai Definisi Domain SKPL 2.3.1:
 * prefix.yy.nnnn  (yy = tahun, nomor urut kembali ke 1 tiap tahun).
 */
class Kode
{
    public static function berikutnya(string $tabel, string $kolom, string $prefix, int $digit): string
    {
        $awal = $prefix.'.'.now()->format('y').'.';

        $terakhir = DB::table($tabel)
            ->where($kolom, 'like', $awal.'%')
            ->orderByDesc($kolom)
            ->value($kolom);

        $urut = $terakhir ? ((int) substr(trim($terakhir), strlen($awal))) + 1 : 1;

        return $awal.str_pad((string) $urut, $digit, '0', STR_PAD_LEFT);
    }

    /** Kunci aman untuk nama field form (titik pada ID memecah notasi dot milik Laravel). */
    public static function kunci(string $id): string
    {
        return str_replace('.', '-', $id);
    }

    public static function rupiah(float|int|string|null $nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }
}
