<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpanan berkas pada direktori storage terenkripsi (SKPL 2.1 "Filing system").
 * Isi berkas dienkripsi dengan Crypt (AES-256) sebelum ditulis ke disk.
 */
class DokumenService
{
    public function simpan(UploadedFile $file, string $folder): array
    {
        return $this->simpanKonten(
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName(),
            $folder
        );
    }

    public function simpanKonten(string $isi, string $namaAsli, string $folder): array
    {
        $path = trim($folder, '/').'/'.Str::uuid().'.enc';
        Storage::disk('local')->put($path, Crypt::encryptString($isi));

        return [
            'path' => $path,
            'nama' => mb_substr($namaAsli, 0, 150),
            'ukuran_kb' => (int) max(1, ceil(strlen($isi) / 1024)),
        ];
    }

    public function baca(string $path): ?string
    {
        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Crypt::decryptString(Storage::disk('local')->get($path));
    }

    public function hapus(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function respons(string $path, string $namaFile, bool $inline = true)
    {
        $isi = $this->baca($path);
        abort_if($isi === null, 404, 'Berkas tidak ditemukan.');

        $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $mime = [
            'pdf' => 'application/pdf', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'csv' => 'text/csv', 'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ][$ext] ?? 'application/octet-stream';

        return response($isi, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($inline && in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']) ? 'inline' : 'attachment')
                .'; filename="'.addslashes($namaFile).'"',
        ]);
    }
}
