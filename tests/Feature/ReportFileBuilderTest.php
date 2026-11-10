<?php

namespace Tests\Feature;

use App\Support\BackupEncryptor;
use App\Support\ReportFileBuilder;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class ReportFileBuilderTest extends TestCase
{
    public function test_backup_encryption_round_trips_and_rejects_tampering(): void
    {
        if (! extension_loaded('openssl')) {
            self::markTestSkipped('OpenSSL extension is unavailable.');
        }

        $sourcePath = tempnam(sys_get_temp_dir(), 'beasiswa-backup-source-');
        $encryptedPath = tempnam(sys_get_temp_dir(), 'beasiswa-backup-encrypted-');
        $decryptedPath = tempnam(sys_get_temp_dir(), 'beasiswa-backup-decrypted-');
        $key = 'base64:'.base64_encode(random_bytes(32));

        try {
            $contents = random_bytes(150000);
            file_put_contents($sourcePath, $contents);
            $encryptor = new BackupEncryptor;
            $encryptor->encrypt($sourcePath, $encryptedPath, $key);
            $encryptor->decrypt($encryptedPath, $decryptedPath, $key);
            self::assertSame($contents, file_get_contents($decryptedPath));

            $tampered = file_get_contents($encryptedPath);
            $tampered[strlen($tampered) - 1] = chr(ord($tampered[strlen($tampered) - 1]) ^ 1);
            file_put_contents($encryptedPath, $tampered);
            $this->expectException(\RuntimeException::class);
            $encryptor->decrypt($encryptedPath, $decryptedPath, $key);
        } finally {
            foreach ([$sourcePath, $encryptedPath, $decryptedPath] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_excel_output_is_a_readable_workbook(): void
    {
        $contents = (new ReportFileBuilder)->xlsx(
            'Laporan Uji',
            ['Nama', 'Status'],
            [['Aulia & Budi', 'Lolos']]
        );
        $path = tempnam(sys_get_temp_dir(), 'beasiswa-xlsx-test-');
        self::assertNotFalse($path);

        try {
            file_put_contents($path, $contents);
            $archive = new ZipArchive;
            self::assertTrue($archive->open($path) === true);
            $sheet = $archive->getFromName('xl/worksheets/sheet1.xml');
            $archive->close();

            self::assertIsString($sheet);
            self::assertStringContainsString('Laporan Uji', $sheet);
            self::assertStringContainsString('Aulia &amp; Budi', $sheet);
        } finally {
            unlink($path);
        }
    }

    public function test_pdf_output_contains_a_valid_header_and_trailer(): void
    {
        $contents = (new ReportFileBuilder)->pdf(
            'Laporan Uji',
            ['Nama', 'Status'],
            [['Aulia', 'Lolos']]
        );

        self::assertStringStartsWith('%PDF-1.4', $contents);
        self::assertStringContainsString('xref', $contents);
        self::assertStringEndsWith('%%EOF', $contents);
    }
}
