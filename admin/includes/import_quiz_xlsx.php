<?php
/**
 * Import quiz data from XLSX file
 * Uses ZipArchive to read Excel files
 */

function importQuizFromXlsx(string $filepath): array
{
    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'File not found.'];
    }

    $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) {
        return ['success' => false, 'error' => 'Could not open XLSX file.'];
    }

    // Read shared strings
    $sharedStrings = [];
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml) {
        $xml = simplexml_load_string($sharedStringsXml);
        if ($xml) {
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string)$si->t;
            }
        }
    }

    // Read worksheet
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if (!$sheetXml) {
        return ['success' => false, 'error' => 'Could not read worksheet data.'];
    }

    $xml = simplexml_load_string($sheetXml);
    if (!$xml) {
        return ['success' => false, 'error' => 'Invalid XLSX format.'];
    }

    $data = [];
    $found_header = false;

    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        $rowNum = (int)$row['r'];

        // Skip rows 1-3 (title, instructions, spacer)
        if ($rowNum < 4) {
            continue;
        }

        foreach ($row->c as $cell) {
            $cellValue = '';
            
            if (isset($cell->v)) {
                $value = (string)$cell->v;
                $type = (string)$cell['t'];
                
                // If type is 's', it's a shared string
                if ($type === 's') {
                    $index = (int)$value;
                    $cellValue = isset($sharedStrings[$index]) ? $sharedStrings[$index] : '';
                } else {
                    $cellValue = $value;
                }
            }
            
            $rowData[] = $cellValue;
        }

        // Row 4 is header
        if ($rowNum == 4) {
            $found_header = true;
            continue;
        }

        // Row 5 is hints, skip
        if ($rowNum == 5) {
            continue;
        }

        // Add data rows (starting from row 6)
        if ($found_header && count($rowData) >= 9) {
            $data[] = $rowData;
        }
    }

    if (!$found_header) {
        return ['success' => false, 'error' => 'Could not find header row in XLSX file.'];
    }

    return ['success' => true, 'data' => $data];
}
