<?php
/**
 * Import quiz data from DOCX file
 * Uses ZipArchive to read Word documents
 *
 * DOCX template structure:
 *   Row 1 → Header  (Subject | Level | Category | Question | A | B | C | D | Correct)
 *   Row 2 → Hints   (skip)
 *   Row 3+ → Sample / user data rows
 *
 * Sample rows (filled with example data) are detected and skipped automatically.
 */

function importQuizFromDocx(string $filepath): array
{
    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'File not found.'];
    }

    $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) {
        return ['success' => false, 'error' => 'Could not open DOCX file. The file may be corrupted or not a valid Word document.'];
    }

    // Read document XML
    $docXml = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($docXml === false) {
        return ['success' => false, 'error' => 'Could not read document data from DOCX.'];
    }

    // Suppress XML errors and load with namespace awareness
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($docXml);
    libxml_clear_errors();

    if ($xml === false) {
        return ['success' => false, 'error' => 'Invalid DOCX XML format.'];
    }

    // Register the Word namespace so XPath works correctly
    $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    $xml->registerXPathNamespace('w', $ns);

    // Find all tables in the document
    $tables = $xml->xpath('//w:tbl');

    if (empty($tables)) {
        return ['success' => false, 'error' => 'No table found in DOCX file. Please use the downloaded template without removing the table.'];
    }

    $table = $tables[0]; // First table is the quiz data table
    $data  = [];
    $rowNum = 0;

    foreach ($table->xpath('.//w:tr') as $row) {
        $rowNum++;
        $rowData = [];

        // Extract all text from each cell, preserving spaces between runs
        foreach ($row->xpath('.//w:tc') as $cell) {
            $cellText = '';
            foreach ($cell->xpath('.//w:t') as $textNode) {
                $cellText .= (string)$textNode;
            }
            $rowData[] = trim($cellText);
        }

        // Row 1: header — validate it looks right, then skip
        if ($rowNum === 1) {
            // Sanity check: first cell should contain "subject" (case-insensitive)
            if (!empty($rowData[0]) && stripos($rowData[0], 'subject') === false) {
                // Table doesn't look like our template; still continue but warn via data
            }
            continue;
        }

        // Row 2: hints row — skip
        if ($rowNum === 2) {
            continue;
        }

        // Rows 3+: data rows
        // Need at least 9 columns
        if (count($rowData) < 9) {
            continue;
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

        // Skip sample/hint rows: first cell starts with "e.g." or contains "pick"
        $firstCell = strtolower(trim($rowData[0]));
        if (strpos($firstCell, 'e.g') !== false || strpos($firstCell, 'pick') !== false) {
            continue;
        }

        // Skip rows where the question column (index 3) is empty
        if (trim($rowData[3]) === '') {
            continue;
        }

        $data[] = $rowData;
    }

    if ($rowNum < 1) {
        return ['success' => false, 'error' => 'The table in the DOCX file appears to be empty.'];
    }

    if (empty($data)) {
        return ['success' => false, 'error' => 'No valid question rows found. Make sure you filled in the table rows below the header and hint rows.'];
    }

    return ['success' => true, 'data' => $data];
}
