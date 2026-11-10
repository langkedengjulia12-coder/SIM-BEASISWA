<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class ReportFileBuilder
{
    public function xlsx(string $title, array $headers, array $rows): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip wajib aktif untuk membuat laporan Excel.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'beasiswa-xlsx-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Tidak dapat membuat file sementara untuk laporan Excel.');
        }

        try {
            $zip = new ZipArchive;
            if ($zip->open($temporaryPath, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Tidak dapat membuat arsip laporan Excel.');
            }

            $files = [
                '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                    .'<Default Extension="xml" ContentType="application/xml"/>'
                    .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                    .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                    .'</Types>',
                '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                    .'</Relationships>',
                'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                    .'<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets></workbook>',
                'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                    .'</Relationships>',
                'xl/worksheets/sheet1.xml' => $this->worksheet($title, $headers, $rows),
            ];

            foreach ($files as $name => $contents) {
                if (! $zip->addFromString($name, $contents)) {
                    $zip->close();
                    throw new RuntimeException('Tidak dapat menulis isi laporan Excel.');
                }
            }

            if (! $zip->close()) {
                throw new RuntimeException('Tidak dapat menyelesaikan arsip laporan Excel.');
            }

            $contents = file_get_contents($temporaryPath);
            if ($contents === false) {
                throw new RuntimeException('Tidak dapat membaca laporan Excel yang telah dibuat.');
            }

            return $contents;
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    public function pdf(string $title, array $headers, array $rows): string
    {
        $lines = [$title, 'Dibuat: '.date('d-m-Y H:i:s'), implode(' | ', $headers)];
        foreach ($rows as $row) {
            $lines[] = implode(' | ', array_map(static fn ($value): string => (string) $value, $row));
        }

        $wrappedLines = [];
        foreach ($lines as $line) {
            foreach (explode("\n", wordwrap($line, 100, "\n", true)) as $wrappedLine) {
                $wrappedLines[] = $this->toPdfText($wrappedLine);
            }
        }

        $pages = array_chunk($wrappedLines, 48);
        if ($pages === []) {
            $pages = [[]];
        }

        $objects = [];
        $pageReferences = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        foreach ($pages as $index => $pageLines) {
            $pageObject = 4 + ($index * 2);
            $contentObject = $pageObject + 1;
            $pageReferences[] = "{$pageObject} 0 R";

            $commands = "BT\n/F1 9 Tf\n40 760 Td\n";
            foreach ($pageLines as $lineIndex => $line) {
                if ($lineIndex > 0) {
                    $commands .= "0 -15 Td\n";
                }
                $escapedLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
                $commands .= "({$escapedLine}) Tj\n";
            }
            $commands .= 'ET';

            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObject} 0 R >>";
            $objects[$contentObject] = '<< /Length '.strlen($commands)." >>\nstream\n{$commands}\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageReferences).'] /Count '.count($pages).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($number = 1; $number <= count($objects); $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function worksheet(string $title, array $headers, array $rows): string
    {
        $allRows = [[$title], $headers, ...$rows];
        $xmlRows = '';

        foreach ($allRows as $rowIndex => $row) {
            $cells = '';
            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $value = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cells .= '<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$value.'</t></is></c>';
            }
            $xmlRows .= '<row r="'.($rowIndex + 1).'">'.$cells.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.$xmlRows.'</sheetData></worksheet>';
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function toPdfText(string $value): string
    {
        if (! function_exists('iconv')) {
            throw new RuntimeException('Ekstensi PHP iconv wajib aktif untuk membuat laporan PDF.');
        }

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii === false) {
            throw new RuntimeException('Tidak dapat mengonversi teks laporan ke format PDF.');
        }

        return $ascii;
    }
}
