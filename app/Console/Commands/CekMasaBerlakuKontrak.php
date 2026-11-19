<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use App\Services\KontrakService;
use Illuminate\Console\Command;

/** ALGO-03 langkah 4-5: penjadwal harian masa berlaku kontrak. */
class CekMasaBerlakuKontrak extends Command
{
    protected $signature = 'beasiswa:cek-kontrak';
    protected $description = 'Kirim notifikasi kontrak yang berakhir <= 30 hari dan tandai kontrak yang sudah berakhir';

    public function handle(KontrakService $svc): int
    {
        $berakhir = $svc->tandaiBerakhir();
        $notif = 0;

        foreach (Kontrak::with('mitra.pengguna')->berlaku()->get() as $k) {
            $notif += $svc->periksa($k) ? 1 : 0;
        }

        $this->info("{$berakhir} kontrak ditandai berakhir, {$notif} notifikasi masa berlaku dikirim.");

        return self::SUCCESS;
    }
}
