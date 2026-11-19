<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Penulis berkas .xlsx minimal (satu sheet) tanpa pustaka eksternal.
 * Membutuhkan ekstensi PHP zip (aktif secara bawaan di XAMPP/Laragon).
 */
class XlsxWriter
{
    /**
     * @param  array<int,string>  $header
     * @param  array<int,array<int,mixed>>  $rows
     * @param  array<int,string>  $tipe  teks|uang|angka
     */
    public static function buat(string $judul, string $subjudul, array $header, array $rows, array $tipe = []): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip tidak aktif.');
        }

        $n = count($header);
        $sheetRows = '';
        $r = 1;

        $sheetRows .= self::barisTeks($r++, [$judul], 1);
        $sheetRows .= self::barisTeks($r++, [$subjudul], 0);
        $r++;

        $sheetRows .= self::barisTeks($r++, $header, 2);

        $lebar = array_map(fn ($h) => mb_strlen((string) $h), $header);

        foreach ($rows as $row) {
            $cells = '';
            for ($i = 0; $i < $n; $i++) {
                $v = $row[$i] ?? '';
                $ref = self::kolom($i).$r;
                $t = $tipe[$i] ?? 'teks';
                if (($t === 'uang' || $t === 'angka') && is_numeric($v)) {
                    $cells .= '<c r="'.$ref.'"'.($t === 'uang' ? ' s="3"' : '').'><v>'.$v.'</v></c>';
                } else {
                    $cells .= self::sel($ref, (string) $v, 0);
                }
                $lebar[$i] = max($lebar[$i], min(mb_strlen((string) $v), 45));
            }
            $sheetRows .= '<row r="'.$r.'">'.$cells.'</row>';
            $r++;
        }

        $cols = '<cols>';
        foreach ($lebar as $i => $w) {
            $cols .= '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.(min($w, 45) + 3).'" customWidth="1"/>';
        }
        $cols .= '</cols>';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .$cols.'<sheetData>'.$sheetRows.'</sheetData></worksheet>';

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                .'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                .'</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
                .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                .'</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                .'<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font>'
                .'<font><b/><sz val="14"/><name val="Calibri"/></font>'
                .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
                .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
                .'<fill><patternFill patternType="solid"><fgColor rgb="FFB30000"/><bgColor indexed="64"/></patternFill></fill></fills>'
                .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
                .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                .'<cellXfs count="4">'
                .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
                .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
                .'<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
                .'<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
                .'</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
            'xl/worksheets/sheet1.xml' => $sheet,
        ];

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat berkas xlsx.');
        }
        foreach ($files as $nama => $isi) {
            $zip->addFromString($nama, $isi);
        }
        $zip->close();
        $bin = file_get_contents($tmp);
        @unlink($tmp);

        return $bin;
    }

    private static function barisTeks(int $r, array $nilai, int $style): string
    {
        $cells = '';
        foreach (array_values($nilai) as $i => $v) {
            $cells .= self::sel(self::kolom($i).$r, (string) $v, $style);
        }

        return '<row r="'.$r.'">'.$cells.'</row>';
    }

    private static function sel(string $ref, string $nilai, int $style): string
    {
        $nilai = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $nilai);

        return '<c r="'.$ref.'" t="inlineStr"'.($style ? ' s="'.$style.'"' : '').'><is><t xml:space="preserve">'
            .htmlspecialchars($nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
    }

    private static function kolom(int $i): string
    {
        $s = '';
        $i++;
        while ($i > 0) {
            $m = ($i - 1) % 26;
            $s = chr(65 + $m).$s;
            $i = intdiv($i - $m - 1, 26);
        }

        return $s;
    }
}
