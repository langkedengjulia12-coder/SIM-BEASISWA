<?php

namespace App\Support;

/**
 * Pembuat PDF tabel sederhana (tanpa pustaka eksternal) untuk P-13 / P-14.
 * A4 landscape, font Helvetica, kolom otomatis, teks dibungkus per sel,
 * header tabel diulang di tiap halaman.
 */
class SimplePdf
{
    private const W = 842;
    private const H = 595;
    private const M = 36;
    private const FS = 8;
    private const CW = 4.3;   // perkiraan lebar rata-rata karakter (pt)
    private const LH = 10;    // tinggi baris teks (pt)

    /**
     * @param  array<int,string>  $header
     * @param  array<int,array<int,mixed>>  $rows
     * @param  array<int,string>  $tipe  teks|uang|angka per kolom
     */
    public static function tabel(string $judul, string $subjudul, array $header, array $rows, array $tipe = []): string
    {
        $n = count($header);
        $areaW = self::W - 2 * self::M;

        // ---- siapkan teks sel (cp1252) ----
        $data = [];
        foreach ($rows as $r) {
            $baris = [];
            for ($i = 0; $i < $n; $i++) {
                $baris[$i] = self::enc(self::format($r[$i] ?? '', $tipe[$i] ?? 'teks'));
            }
            $data[] = $baris;
        }
        $head = array_map(fn ($h) => self::enc((string) $h), $header);

        // ---- lebar kolom proporsional ----
        $maks = [];
        for ($i = 0; $i < $n; $i++) {
            $m = strlen($head[$i]);
            foreach ($data as $b) {
                $m = max($m, strlen($b[$i]));
            }
            $maks[$i] = min(max($m, 6), 36);
        }
        $total = array_sum($maks) ?: 1;
        $lebar = $chars = $x = [];
        $cursor = self::M;
        for ($i = 0; $i < $n; $i++) {
            $lebar[$i] = $areaW * $maks[$i] / $total;
            $chars[$i] = max(3, (int) floor(($lebar[$i] - 6) / self::CW));
            $x[$i] = $cursor;
            $cursor += $lebar[$i];
        }

        // ---- tata letak ke halaman ----
        $halaman = [];
        $ops = '';
        $y = self::H - self::M;

        $judulOps = function () use ($judul, $subjudul, &$y) {
            $o = self::teks(self::M, $y - 10, self::enc($judul), 12, true);
            $y -= 16;
            $o .= self::teks(self::M, $y - 8, self::enc($subjudul), 8, false);
            $y -= 20;

            return $o;
        };

        // judul kolom dibungkus agar tidak terpotong
        $headBaris = [];
        $headTinggi = 1;
        for ($i = 0; $i < $n; $i++) {
            $headBaris[$i] = self::bungkus($head[$i], $chars[$i]);
            $headTinggi = max($headTinggi, count($headBaris[$i]));
        }

        $headerOps = function () use (&$y, $headBaris, $headTinggi, $x, $lebar, $n) {
            $h = $headTinggi * self::LH + 6;
            $o = '0.88 g '.self::f(self::M).' '.self::f($y - $h).' '.self::f(array_sum($lebar)).' '.self::f($h).' re f 0 g ';
            for ($i = 0; $i < $n; $i++) {
                foreach ($headBaris[$i] as $k => $line) {
                    $o .= self::teks($x[$i] + 3, $y - self::LH - 1 - ($k * self::LH), $line, self::FS, true);
                }
            }
            $y -= $h;

            return $o;
        };

        $ops .= $judulOps();
        $ops .= $headerOps();

        foreach ($data as $b) {
            $baris = [];
            $tinggi = 1;
            for ($i = 0; $i < $n; $i++) {
                $baris[$i] = self::bungkus($b[$i], $chars[$i]);
                $tinggi = max($tinggi, count($baris[$i]));
            }
            $hBaris = $tinggi * self::LH + 4;

            if ($y - $hBaris < self::M + 18) {
                $halaman[] = $ops;
                $ops = '';
                $y = self::H - self::M;
                $ops .= $headerOps();
            }

            for ($i = 0; $i < $n; $i++) {
                $kanan = in_array($tipe[$i] ?? 'teks', ['uang', 'angka'], true);
                foreach ($baris[$i] as $k => $line) {
                    $px = $kanan ? $x[$i] + $lebar[$i] - 3 - strlen($line) * self::CW : $x[$i] + 3;
                    $ops .= self::teks($px, $y - self::LH - ($k * self::LH), $line, self::FS, false);
                }
            }
            $y -= $hBaris;
            $ops .= '0.8 G 0.4 w '.self::f(self::M).' '.self::f($y + 1).' m '.self::f(self::M + array_sum($lebar)).' '.self::f($y + 1).' l S 0 G ';
        }

        if (empty($data)) {
            $ops .= self::teks(self::M + 3, $y - self::LH, self::enc('(tidak ada data)'), self::FS, false);
        }
        $halaman[] = $ops;

        // ---- rakit objek PDF ----
        $jml = count($halaman);
        $cetak = self::enc('SIM-BEASISWA UKRI - dicetak '.now()->format('d-m-Y H:i'));
        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        for ($i = 0; $i < $jml; $i++) {
            $kids[] = (5 + 2 * $i).' 0 R';
        }
        $objs[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.$jml.' >>';
        $objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objs[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        foreach ($halaman as $i => $isi) {
            $isi .= self::teks(self::M, 20, $cetak, 7, false);
            $ket = 'Halaman '.($i + 1).' dari '.$jml;
            $isi .= self::teks(self::W - self::M - strlen($ket) * 3.8, 20, $ket, 7, false);

            $pid = 5 + 2 * $i;
            $cid = $pid + 1;
            $objs[$pid] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::W.' '.self::H.'] '
                .'/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$cid.' 0 R >>';
            $objs[$cid] = '<< /Length '.strlen($isi)." >>\nstream\n".$isi."\nendstream";
        }

        ksort($objs);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offset = [];
        foreach ($objs as $no => $isi) {
            $offset[$no] = strlen($pdf);
            $pdf .= $no." 0 obj\n".$isi."\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objs) + 1)."\n0000000000 65535 f \n";
        foreach ($objs as $no => $_) {
            $pdf .= sprintf("%010d 00000 n \n", $offset[$no]);
        }
        $pdf .= 'trailer << /Size '.(count($objs) + 1).' /Root 1 0 R >>'."\nstartxref\n".$xref."\n%%EOF";

        return $pdf;
    }

    // ------------------------------------------------------------------ util

    private static function format(mixed $v, string $tipe): string
    {
        if ($v === null) {
            return '';
        }
        if ($tipe === 'uang') {
            return number_format((float) $v, 0, ',', '.');
        }

        return (string) $v;
    }

    private static function enc(string $s): string
    {
        $s = str_replace(["\r", "\n", "\t"], ' ', $s);
        $out = @mb_convert_encoding($s, 'Windows-1252', 'UTF-8');

        return $out === false ? preg_replace('/[^\x20-\x7E]/', '?', $s) : $out;
    }

    private static function potong(string $s, int $maks): string
    {
        return strlen($s) > $maks ? substr($s, 0, max($maks - 1, 1)).'.' : $s;
    }

    /** @return array<int,string> */
    private static function bungkus(string $s, int $maks): array
    {
        if ($s === '') {
            return [''];
        }
        $baris = explode("\n", wordwrap($s, $maks, "\n", true));

        return $baris ?: [''];
    }

    private static function esc(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private static function f(float|int $n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.') ?: '0';
    }

    private static function teks(float|int $x, float|int $y, string $s, int|float $size, bool $bold): string
    {
        if ($s === '') {
            return '';
        }

        return 'BT /'.($bold ? 'F2' : 'F1').' '.self::f($size).' Tf '.self::f($x).' '.self::f($y).' Td ('.self::esc($s).') Tj ET ';
    }
}
