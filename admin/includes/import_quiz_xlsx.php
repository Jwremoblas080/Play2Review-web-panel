<?php
/**
 * Import quiz data from XLSX file
 * Uses ZipArchive to read Excel files (no external library needed)
 *
 * XLSX template structure:
 *   Rows 1-3 → Title / instructions / spacer  (skip)
 *   Row 4    → Header row                      (skip)
 *   Row 5    → Hints row                       (skip)
 *   Row 6+   → Data rows
 *
 * Handles:
 *  - Shared strings (plain text and rich-text <si> elements)
 *  - Sparse columns (cells referenced by column letter, e.g. A1, C3)
 *  - Empty row detection
 */

function importQuizFromXlsx(string $filepath): array
{
    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'File not found.'];
    }

    $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) {
        return ['success' => false, 'error' => 'Could not open XLSX file. The file may be corrupted or not a valid Excel document.'];
    }

    // ── Shared strings ────────────────────────────────────────────────────────
    $sharedStrings = [];
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml !== false) {
        libxml_use_internal_errors(true);
        $ssXml = simplexml_load_string($sharedStringsXml);
        libxml_clear_errors();

        if ($ssXml !== false) {
            foreach ($ssXml->si as $si) {
                // Plain text: <si><t>value</t></si>
                // Rich text:  <si><r><t>part1</t></r><r><t>part2</t></r></si>
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    // Concatenate all <r><t> runs for rich-text cells
                    $text = '';
                    foreach ($si->r as $run) {
                        if (isset($run->t)) {
                            $text .= (string)$run->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
    }

    // ── Worksheet ─────────────────────────────────────────────────────────────
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if ($sheetXml === false) {
        return ['success' => false, 'error' => 'Could not read worksheet data from XLSX.'];
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($sheetXml);
    libxml_clear_errors();

    if ($xml === false) {
        return ['success' => false, 'error' => 'Invalid XLSX format.'];
    }

    $data        = [];
    $found_header = false;

    foreach ($xml->sheetData->row as $row) {
        $rowNum = (int)$row['r'];

        // Skip title / instructions / spacer rows (1-3)
        if ($rowNum < 4) {
            continue;
        }

        // Row 4 is the header — mark found and skip
        if ($rowNum === 4) {
            $found_header = true;
            continue;
        }

        // Row 5 is the hints row — skip
        if ($rowNum === 5) {
            continue;
        }

        // ── Build a column-indexed array (handles sparse columns) ──────────
        // Excel cells have a reference like "A6", "B6", "C6" etc.
        // We convert the column letter(s) to a 0-based index.
        $rowData = array_fill(0, 9, ''); // pre-fill 9 columns with empty string

        foreach ($row->c as $cell) {
            $cellRef = (string)$cell['r'];           // e.g. "C6"
            $colLetter = preg_replace('/[0-9]/', '', $cellRef); // e.g. "C"
            $colIndex  = _xlsxColToIndex($colLetter);           // e.g. 2

            if ($colIndex > 8) {
                continue; // We only care about columns A-I (0-8)
            }

            $cellValue = '';
            if (isset($cell->v)) {
                $value = (string)$cell->v;
                $type  = (string)$cell['t'];

                if ($type === 's') {
                    // Shared string reference
                    $index     = (int)$value;
                    $cellValue = $sharedStrings[$index] ?? '';
                } elseif ($type === 'inlineStr') {
                    // Inline string
                    $cellValue = isset($cell->is->t) ? (string)$cell->is->t : '';
                } else {
                    $cellValue = $value;
                }
            }

            $rowData[$colIndex] = $cellValue;
        }

        // Skip completely empty rows
        $hasContent = false;
        foreach ($rowData as $cell) {
            if (trim($cell) !== '') {
                $hasContent = true;
                break;
            }
        }
        if (!$hasContent) {
            continue;
        }

        // Skip sample/hint rows
        $firstCell = strtolower(trim($rowData[0]));
        if (strpos($firstCell, 'e.g') !== false || strpos($firstCell, 'pick') !== false) {
            continue;
        }

        // Skip rows where the question column (index 3) is empty
        if (trim($rowData[3]) === '') {
            continue;
        }

        $data[] = array_values($rowData);
    }

    if (!$found_header) {
        return ['success' => false, 'error' => 'Could not find the header row (row 4) in the XLSX file. Please use the downloaded template.'];
    }

    if (empty($data)) {
        return ['success' => false, 'error' => 'No valid question rows found. Make sure you filled in rows starting from row 6 of the template.'];
    }

    return ['success' => true, 'data' => $data];
}

/**
 * Convert Excel column letters to a 0-based index.
 * A=0, B=1, ..., Z=25, AA=26, AB=27, ...
 */
function _xlsxColToIndex(string $col): int
{
    $col   = strtoupper($col);
    $index = 0;
    $len   = strlen($col);
    for ($i = 0; $i < $len; $i++) {
        $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $index - 1; // convert to 0-based
}
