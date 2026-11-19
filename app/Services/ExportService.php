<?php

namespace App\Services;

use App\Support\SimplePdf;
use App\Support\XlsxWriter;
use Illuminate\Support\Carbon;

/** Pembangkit berkas laporan: CSV, XLSX, PDF (ALGO-13 / ALGO-14). */
class ExportService
{
    /** @return array{isi:string,ext:string,mime:string} */
    public function buat(string $format, array $dataset, Carbon $awal, Carbon $akhir): array
    {
        $sub = 'Periode '.$awal->format('d-m-Y').' s.d. '.$akhir->format('d-m-Y')
            .' | Universitas Kebangsaan Republik Indonesia';

        return match (strtoupper($format)) {
            'XLSX' => [
                'isi' => XlsxWriter::buat($dataset['judul'], $sub, $dataset['header'], $dataset['rows'], $dataset['tipe']),
                'ext' => 'xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'PDF' => [
                'isi' => SimplePdf::tabel($dataset['judul'], $sub, $dataset['header'], $dataset['rows'], $dataset['tipe']),
                'ext' => 'pdf',
                'mime' => 'application/pdf',
            ],
            default => [
                'isi' => $this->csv($dataset),
                'ext' => 'csv',
                'mime' => 'text/csv',
            ],
        };
    }

    private function csv(array $d): string
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF");                       // BOM agar Excel membaca UTF-8
        fputcsv($h, $d['header'], ';');
        foreach ($d['rows'] as $r) {
            fputcsv($h, $r, ';');
        }
        rewind($h);
        $isi = stream_get_contents($h);
        fclose($h);

        return $isi;
    }
}
