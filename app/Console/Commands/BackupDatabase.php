<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * KAK 7.1.B - backup otomatis harian dan mingguan.
 *
 *   php artisan beasiswa:backup harian
 *   php artisan beasiswa:backup mingguan
 *
 * Hasil: storage/app/backup/{harian|mingguan}/sikb_YYYYMMDD_HHMMSS.sql.gz
 * Kolom sensitif (rekening) sudah terenkripsi di database sehingga tetap
 * terenkripsi di dalam berkas backup; berkas dokumen ada di storage/app (backup folder ini juga).
 */
class BackupDatabase extends Command
{
    protected $signature = 'beasiswa:backup {jenis=harian : harian atau mingguan}';
    protected $description = 'Backup database SIM-BEASISWA (harian / mingguan) beserta rotasi berkas lama';

    public function handle(): int
    {
        $jenis = $this->argument('jenis');
        if (! in_array($jenis, ['harian', 'mingguan'], true)) {
            $this->error('Jenis backup harus harian atau mingguan.');

            return self::INVALID;
        }

        $dir = storage_path("app/backup/{$jenis}");
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("Folder backup tidak dapat dibuat: {$dir}");

            return self::FAILURE;
        }

        $tujuan = $dir.'/sikb_'.now()->format('Ymd_His').'.sql.gz';

        try {
            $isi = $this->dump();
        } catch (Throwable $e) {
            $this->error('Backup gagal: '.$e->getMessage());
            LogAktivitas::catat('backup_gagal', 'sistem', mb_substr($e->getMessage(), 0, 300));

            return self::FAILURE;
        }

        file_put_contents($tujuan, gzencode($isi, 9));
        $this->rotasi($dir, (int) config('beasiswa.backup.simpan_'.$jenis, 7));

        LogAktivitas::catat('backup', 'sistem', basename($tujuan).' ('.round(filesize($tujuan) / 1024, 1).' KB)');
        $this->info("Backup {$jenis} tersimpan: {$tujuan}");

        return self::SUCCESS;
    }

    private function dump(): string
    {
        $koneksi = config('database.default');
        $c = config("database.connections.{$koneksi}");

        if (($c['driver'] ?? '') === 'sqlite') {
            if (! is_file($c['database'])) {
                throw new \RuntimeException('Berkas database SQLite tidak ditemukan.');
            }

            return (string) file_get_contents($c['database']);
        }

        if (! in_array($c['driver'] ?? '', ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('Driver database belum didukung untuk backup: '.($c['driver'] ?? '?'));
        }

        $proses = new Process([
            config('beasiswa.backup.mysqldump'), '--single-transaction', '--routines', '--default-character-set=utf8mb4',
            '-h', (string) $c['host'], '-P', (string) $c['port'], '-u', (string) $c['username'], $c['database'],
        ], null, ['MYSQL_PWD' => (string) ($c['password'] ?? '')]);   // kata sandi tidak tampil di daftar proses
        $proses->setTimeout(600);
        $proses->run();

        if (! $proses->isSuccessful()) {
            throw new \RuntimeException(trim($proses->getErrorOutput()) ?: 'mysqldump gagal dijalankan (cek MYSQLDUMP_PATH di .env).');
        }

        return $proses->getOutput();
    }

    private function rotasi(string $dir, int $simpan): void
    {
        $berkas = glob($dir.'/sikb_*.sql.gz') ?: [];
        rsort($berkas);   // nama berkas memuat tanggal-waktu: terbaru di depan

        foreach (array_slice($berkas, $simpan) as $lama) {
            @unlink($lama);
        }
    }
}
