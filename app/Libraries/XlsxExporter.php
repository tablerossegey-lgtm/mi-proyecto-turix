<?php

namespace App\Libraries;

use RuntimeException;
use ZipArchive;

class XlsxExporter
{
    /**
     * Genera un archivo .xlsx en memoria y devuelve su contenido binario.
     *
     * @param array<int, array<int, string|int|float>> $rows
     * @param array<int, float> $colWidths
     */
    public function generarBinario(string $sheetName, array $rows, array $colWidths = []): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extensión ZipArchive de PHP es necesaria para exportar a Excel.');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'turix_xlsx_');
        if ($tmpFile === false) {
            throw new RuntimeException('No se pudo crear archivo temporal para exportación.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpFile);
            throw new RuntimeException('No se pudo inicializar el archivo Excel.');
        }

        $safeSheetName = mb_substr(preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '', $sheetName) ?: 'Ranking', 0, 31);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($safeSheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows, $colWidths));
        $zip->close();

        $binary = file_get_contents($tmpFile);
        @unlink($tmpFile);

        if ($binary === false) {
            throw new RuntimeException('No se pudo leer el archivo Excel generado.');
        }

        return $binary;
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26) - 1;
        }
        return $letter;
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->esc($sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml(): string
    {
        // Style 0: default, Style 1: title bold, Style 2: header (dark blue bg, white bold), Style 3: currency (#,##0.00)
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="3">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="13"/><color rgb="FF001233"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF001233"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFD0D7DE"/></left><right style="thin"><color rgb="FFD0D7DE"/></right><top style="thin"><color rgb="FFD0D7DE"/></top><bottom style="thin"><color rgb="FFD0D7DE"/></bottom></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="4" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    /**
     * @param array<int, array<int, string|int|float>> $rows
     * @param array<int, float> $colWidths
     */
    private function worksheetXml(array $rows, array $colWidths): string
    {
        $colsXml = '';
        if (!empty($colWidths)) {
            $colsXml = '<cols>';
            foreach ($colWidths as $i => $w) {
                $colNum = $i + 1;
                $colsXml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $w . '" customWidth="1"/>';
            }
            $colsXml .= '</cols>';
        }

        $sheetData = '<sheetData>';
        foreach ($rows as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $sheetData .= '<row r="' . $rowNum . '">';
            foreach ($row as $cIdx => $cell) {
                $ref = $this->colLetter($cIdx) . $rowNum;
                $style = '';
                $val = $cell;
                if (is_array($cell) && array_key_exists('v', $cell)) {
                    $val = $cell['v'];
                    $style = isset($cell['s']) ? ' s="' . (int) $cell['s'] . '"' : '';
                }

                if (is_int($val) || is_float($val)) {
                    $sheetData .= '<c r="' . $ref . '"' . $style . '><v>' . $val . '</v></c>';
                } else {
                    $sheetData .= '<c r="' . $ref . '" t="inlineStr"' . $style . '><is><t>' . $this->esc((string) $val) . '</t></is></c>';
                }
            }
            $sheetData .= '</row>';
        }
        $sheetData .= '</sheetData>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $colsXml
            . $sheetData
            . '</worksheet>';
    }
}

