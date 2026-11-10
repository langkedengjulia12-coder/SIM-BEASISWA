<?php

namespace App\Console\Commands;

use App\Support\BackupEncryptor;
use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupBeasiswa extends Command
{
    protected $signature = 'beasiswa:backup
        {--decrypt= : Decrypt an encrypted backup file to a ZIP archive}
        {--output= : Destination ZIP path used with --decrypt}';

    protected $description = 'Create an encrypted database and uploaded-file backup';

    public function handle(BackupEncryptor $encryptor): int
    {
        if ($this->option('decrypt')) {
            return $this->decryptBackup($encryptor, $this->option('decrypt'), $this->option('output'));
        }

        return $this->createBackup($encryptor);
    }

    private function createBackup(BackupEncryptor $encryptor): int
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip wajib aktif untuk membuat backup.');
        }

        $database = config('database.connections.'.config('database.default'));
        if (($database['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Backup otomatis saat ini hanya mendukung database MySQL.');
        }

        $directory = config('backup.path');
        if (! is_string($directory) || $directory === '') {
            throw new RuntimeException('Lokasi backup tidak dikonfigurasi.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Direktori backup tidak dapat dibuat.');
        }
        $resolvedDirectory = realpath($directory);
        $resolvedPublicPath = realpath(public_path());
        if ($resolvedDirectory === false || $resolvedPublicPath === false) {
            throw new RuntimeException('Lokasi backup atau web root tidak dapat diverifikasi.');
        }
        $publicPrefix = rtrim($resolvedPublicPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if (str_starts_with(strtolower($resolvedDirectory.DIRECTORY_SEPARATOR), strtolower($publicPrefix))) {
            throw new RuntimeException('Lokasi backup tidak boleh berada di dalam web root.');
        }
        $directory = $resolvedDirectory;

        $basename = 'sim-beasiswa-'.now()->format('Ymd-His');
        $sqlPath = $directory.DIRECTORY_SEPARATOR.$basename.'.sql';
        $zipPath = $directory.DIRECTORY_SEPARATOR.$basename.'.zip';
        $encryptedPath = $directory.DIRECTORY_SEPARATOR.$basename.'.zip.enc';
        $backupCompleted = false;

        try {
            $this->dumpDatabase($database, $sqlPath);
            $this->createArchive($sqlPath, $zipPath);
            $encryptor->encrypt($zipPath, $encryptedPath, (string) config('app.key'));
            $this->pruneBackups($directory, (int) config('backup.retention_days', 7));
            $backupCompleted = true;
        } finally {
            foreach ([$sqlPath, $zipPath] as $temporaryPath) {
                if (is_file($temporaryPath)) {
                    unlink($temporaryPath);
                }
            }
            if (! $backupCompleted && is_file($encryptedPath)) {
                unlink($encryptedPath);
            }
        }

        $this->info("Backup terenkripsi tersimpan: {$encryptedPath}");

        return self::SUCCESS;
    }

    private function dumpDatabase(array $database, string $destination): void
    {
        $output = fopen($destination, 'wb');
        if ($output === false) {
            throw new RuntimeException('File dump database tidak dapat dibuat.');
        }

        try {
            $process = new Process([
                config('backup.mysqldump_binary', 'mysqldump'),
                '--single-transaction',
                '--quick',
                '--skip-lock-tables',
                '--default-character-set=utf8mb4',
                '--host='.($database['host'] ?? '127.0.0.1'),
                '--port='.($database['port'] ?? 3306),
                '--user='.($database['username'] ?? ''),
                (string) ($database['database'] ?? ''),
            ]);
            $process->setTimeout(null);
            $process->run(function (string $type, string $buffer) use ($output): void {
                if ($type === Process::OUT && fwrite($output, $buffer) !== strlen($buffer)) {
                    throw new RuntimeException('Gagal menulis dump database ke disk.');
                }
            }, ['MYSQL_PWD' => (string) ($database['password'] ?? '')]);

            if (! $process->isSuccessful()) {
                throw new RuntimeException('mysqldump gagal: '.$process->getErrorOutput());
            }
        } finally {
            fclose($output);
        }
    }

    private function createArchive(string $sqlPath, string $zipPath): void
    {
        $archive = new ZipArchive;
        if ($archive->open($zipPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new RuntimeException('Arsip backup tidak dapat dibuat.');
        }

        if (! $archive->addFile($sqlPath, 'database.sql')) {
            $archive->close();
            throw new RuntimeException('Dump database tidak dapat ditambahkan ke arsip.');
        }

        $uploadsPath = storage_path('app/public');
        if (is_dir($uploadsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($uploadsPath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->isLink()) {
                    continue;
                }
                $relativePath = substr($file->getPathname(), strlen($uploadsPath) + 1);
                $archivePath = 'storage/public/'.str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                if (! $archive->addFile($file->getPathname(), $archivePath)) {
                    $archive->close();
                    throw new RuntimeException("File unggahan tidak dapat ditambahkan ke backup: {$relativePath}");
                }
            }
        }

        if (! $archive->close()) {
            throw new RuntimeException('Arsip backup tidak dapat diselesaikan.');
        }
    }

    private function decryptBackup(BackupEncryptor $encryptor, string $source, ?string $destination): int
    {
        if (! $destination) {
            $this->error('Tentukan path ZIP tujuan dengan --output.');

            return self::INVALID;
        }
        if (file_exists($destination)) {
            $this->error('File tujuan sudah ada; gunakan path baru agar data tidak tertimpa.');

            return self::INVALID;
        }

        $encryptor->decrypt($source, $destination, (string) config('app.key'));
        $this->info("Arsip backup terverifikasi dan didekripsi: {$destination}");

        return self::SUCCESS;
    }

    private function pruneBackups(string $directory, int $retentionDays): void
    {
        if ($retentionDays < 1) {
            throw new RuntimeException('Masa simpan backup harus minimal satu hari.');
        }

        $cutoff = now()->subDays($retentionDays)->getTimestamp();
        foreach (glob($directory.DIRECTORY_SEPARATOR.'sim-beasiswa-*.zip.enc') ?: [] as $path) {
            if (is_file($path) && filemtime($path) < $cutoff && unlink($path) === false) {
                throw new RuntimeException("Backup lama tidak dapat dihapus: {$path}");
            }
        }
    }
}
