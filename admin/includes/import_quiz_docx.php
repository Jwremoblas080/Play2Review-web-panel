<?php
/**
 * Import quiz data from DOCX file
 * Uses ZipArchive to read Word documents
 */

function importQuizFromDocx(string $filepath): array
{
    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'File not found.'];
    }

    $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) {
        return ['success' => false, 'error' => 'Could not open DOCX file.'];
    }

    // Read document XML
    $docXml = $zip->getFromName('word/document.xml');
    $zip->close();

    if (!$docXml) {
        return ['success' => false, 'error' => 'Could not read document data.'];
    }

    $xml = simplexml_load_string($docXml);
    if (!$xml) {
        return ['success' => false, 'error' => 'Invalid DOCX format.'];
    }

    // Register namespace
    $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

    // Find the table
    $tables = $xml->xpath('//w:tbl');
    if (empty($tables)) {
        return ['success' => false, 'error' => 'No table found in DOCX file.'];
    }

    $table = $tables[0]; // Get first table
    $data = [];
    $rowNum = 0;
    $found_header = false;

    foreach ($table->xpath('.//w:tr') as $row) {
        $rowNum++;
        $rowData = [];

        // Extract text from each cell
        foreach ($row->xpath('.//w:tc') as $cell) {
            $cellText = '';
            $textNodes = $cell->xpath('.//w:t');
            foreach ($textNodes as $textNode) {
                $cellText .= (string)$textNode;
            }
            $rowData[] = trim($cellText);
        }

        // Row 1 is header
        if ($rowNum == 1) {
            $found_header = true;
            continue;
        }

        // Row 2 is hints, skip
        if ($rowNum == 2) {
            continue;
        }

        // Add data rows (starting from row 3)
        if ($found_header && count($rowData) >= 9) {
            // Skip empty rows
            $hasContent = false;
            foreach ($rowData as $cell) {
                if (!empty(trim($cell))) {
                    $hasContent = true;
                    break;
                }
            }
            
            if ($hasContent) {
                $data[] = $rowData;
            }
        }
    }

    if (!$found_header) {
        return ['success' => false, 'error' => 'Could not find header row in DOCX file.'];
    }

    return ['success' => true, 'data' => $data];
}
