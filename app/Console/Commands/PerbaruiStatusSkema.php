<?php

namespace App\Console\Commands;

use App\Models\SkemaBeasiswa;
use Illuminate\Console\Command;

/** ALGO-05b: buka skema saat tgl_buka tiba (bila dana cukup) dan tutup saat tgl_tutup lewat. */
class PerbaruiStatusSkema extends Command
{
    protected $signature = 'beasiswa:perbarui-skema';
    protected $description = 'Buka / tutup pendaftaran skema beasiswa otomatis sesuai tanggal';

    public function handle(): int
    {
        $dibuka = $ditutup = 0;
        $hari = now()->toDateString();

        foreach (SkemaBeasiswa::where('status', 'draf')->whereDate('tgl_buka', '<=', $hari)->whereDate('tgl_tutup', '>=', $hari)->get() as $s) {
            if ($s->komitmen()->exists() && $s->kebutuhan_dana <= $s->sisaKomitmen()) {
                $s->update(['status' => 'dibuka']);
                $dibuka++;
            }
        }

        $ditutup = SkemaBeasiswa::where('status', 'dibuka')->whereDate('tgl_tutup', '<', $hari)->update(['status' => 'ditutup']);

        $this->info("{$dibuka} skema dibuka, {$ditutup} skema ditutup.");

        return self::SUCCESS;
    }
}
