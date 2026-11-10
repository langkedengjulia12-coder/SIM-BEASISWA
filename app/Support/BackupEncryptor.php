<?php

namespace App\Support;

use RuntimeException;

class BackupEncryptor
{
    private const PREFIX = "SIMBEASISWA-BACKUP-2\n";

    private const CHUNK_SIZE = 65536;

    public function encrypt(string $sourcePath, string $destinationPath, string $applicationKey): void
    {
        $this->transform($sourcePath, $destinationPath, $applicationKey, true);
    }

    public function decrypt(string $sourcePath, string $destinationPath, string $applicationKey): void
    {
        $this->transform($sourcePath, $destinationPath, $applicationKey, false);
    }

    private function transform(string $sourcePath, string $destinationPath, string $applicationKey, bool $encrypt): void
    {
        if (! extension_loaded('openssl')) {
            throw new RuntimeException('Ekstensi PHP openssl wajib aktif untuk backup terenkripsi.');
        }

        $key = $this->deriveKey($applicationKey);
        $source = fopen($sourcePath, 'rb');
        if ($source === false) {
            throw new RuntimeException('File sumber backup tidak dapat dibuka.');
        }

        $destination = fopen($destinationPath, 'wb');
        if ($destination === false) {
            fclose($source);
            throw new RuntimeException('File tujuan backup tidak dapat dibuat.');
        }

        $completed = false;
        try {
            if ($encrypt) {
                $this->encryptStream($source, $destination, $key);
            } else {
                $this->decryptStream($source, $destination, $key);
            }
            $completed = true;
        } finally {
            fclose($source);
            fclose($destination);
            if (! $completed && is_file($destinationPath)) {
                unlink($destinationPath);
            }
        }
    }

    private function encryptStream($source, $destination, string $key): void
    {
        $noncePrefix = random_bytes(8);
        $this->writeAll($destination, self::PREFIX.$noncePrefix);
        $counter = 0;

        while (! feof($source)) {
            $chunk = fread($source, self::CHUNK_SIZE);
            if ($chunk === false) {
                throw new RuntimeException('Gagal membaca data backup.');
            }
            if ($chunk !== '') {
                $this->writeEncryptedFrame($destination, $chunk, $key, $noncePrefix, $counter, 0);
                $counter++;
            }
        }

        $this->writeEncryptedFrame($destination, '', $key, $noncePrefix, $counter, 1);
    }

    private function decryptStream($source, $destination, string $key): void
    {
        $prefix = $this->readExact($source, strlen(self::PREFIX));
        if ($prefix !== self::PREFIX) {
            throw new RuntimeException('Format file backup tidak dikenal.');
        }

        $noncePrefix = $this->readExact($source, 8);
        $counter = 0;
        $finalSeen = false;

        while (! feof($source)) {
            $frameHeader = fread($source, 5);
            if ($frameHeader === '' && feof($source)) {
                break;
            }
            if ($frameHeader === false || strlen($frameHeader) !== 5) {
                throw new RuntimeException('Data backup terpotong.');
            }

            $frame = unpack('Ctype/Nlength', $frameHeader);
            if (! in_array($frame['type'], [0, 1], true)
                || $frame['length'] > self::CHUNK_SIZE
                || ($frame['type'] === 1 && $frame['length'] !== 0)
                || ($frame['type'] === 0 && $frame['length'] === 0)) {
                throw new RuntimeException('Frame backup tidak valid.');
            }
            if ($finalSeen) {
                throw new RuntimeException('Backup memiliki data setelah penanda akhir.');
            }

            $tag = $this->readExact($source, 16);
            $ciphertext = $this->readExact($source, $frame['length']);
            $plaintext = openssl_decrypt(
                $ciphertext,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $noncePrefix.pack('N', $counter),
                $tag,
                self::PREFIX.pack('N', $counter).chr($frame['type'])
            );
            if ($plaintext === false) {
                throw new RuntimeException('Autentikasi backup gagal; file atau kunci tidak valid.');
            }

            $this->writeAll($destination, $plaintext);
            $finalSeen = $frame['type'] === 1;
            $counter++;
        }

        if (! $finalSeen) {
            throw new RuntimeException('Backup tidak lengkap atau terpotong.');
        }
    }

    private function writeEncryptedFrame($destination, string $plaintext, string $key, string $noncePrefix, int $counter, int $type): void
    {
        if ($counter > 4294967295) {
            throw new RuntimeException('Backup melebihi ukuran aman untuk enkripsi streaming.');
        }

        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $noncePrefix.pack('N', $counter),
            $tag,
            self::PREFIX.pack('N', $counter).chr($type),
            16
        );
        if ($ciphertext === false || strlen($tag) !== 16) {
            throw new RuntimeException('Gagal mengenkripsi data backup.');
        }

        $this->writeAll($destination, pack('CN', $type, strlen($ciphertext)).$tag.$ciphertext);
    }

    private function deriveKey(string $applicationKey): string
    {
        if (str_starts_with($applicationKey, 'base64:')) {
            $applicationKey = base64_decode(substr($applicationKey, 7), true);
        }
        if (! is_string($applicationKey) || strlen($applicationKey) !== 32) {
            throw new RuntimeException('APP_KEY harus berupa kunci 32-byte yang valid.');
        }

        return hash_hkdf('sha256', $applicationKey, 32, 'sim-beasiswa-backup');
    }

    private function readExact($stream, int $length): string
    {
        $contents = '';
        while (strlen($contents) < $length && ! feof($stream)) {
            $chunk = fread($stream, $length - strlen($contents));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $contents .= $chunk;
        }
        if (strlen($contents) !== $length) {
            throw new RuntimeException('Data backup terpotong.');
        }

        return $contents;
    }

    private function writeAll($stream, string $contents): void
    {
        while ($contents !== '') {
            $written = fwrite($stream, $contents);
            if ($written === false || $written === 0) {
                throw new RuntimeException('Gagal menulis file backup.');
            }
            $contents = substr($contents, $written);
        }
    }
}
