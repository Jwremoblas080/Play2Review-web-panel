<?php
/**
 * Generates a quiz template .docx file
 * Uses ZipArchive (built-in PHP) — no composer needed
 */

function generateQuizTemplateDocx(string $subject_name, array $categories, string $subject_label): void
{
    $cat_keys = array_keys($categories);
    $samples = [
        [
            'subject' => $subject_name,
            'level' => '1',
            'category' => $cat_keys[0] ?? 'grammar',
            'question' => 'What is the capital of the Philippines?',
            'a' => 'Cebu',
            'b' => 'Manila',
            'c' => 'Davao',
            'd' => 'Quezon City',
            'correct' => '2'
        ],
        [
            'subject' => $subject_name,
            'level' => '2',
            'category' => $cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'),
            'question' => 'Which planet is closest to the sun?',
            'a' => 'Earth',
            'b' => 'Venus',
            'c' => 'Mercury',
            'd' => 'Mars',
            'correct' => '3'
        ]
    ];
    
    // Build document XML
    $doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $doc .= '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">';
    $doc .= '<w:body>';
    
    // Title
    $doc .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>';
    $doc .= '<w:r><w:rPr><w:b/><w:sz w:val="32"/><w:color w:val="0A5F38"/></w:rPr>';
    $doc .= '<w:t>' . htmlspecialchars($subject_label, ENT_XML1) . ' - Quiz Template</w:t></w:r></w:p>';
    
    // Instructions heading
    $doc .= '<w:p><w:pPr><w:spacing w:before="240"/></w:pPr>';
    $doc .= '<w:r><w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="0A5F38"/></w:rPr>';
    $doc .= '<w:t>Instructions:</w:t></w:r></w:p>';
    
    // Instructions list
    $instructions = [
        'Fill one question per row in the table below',
        'Subject Name: Use "' . $subject_name . '"',
        'Quiz Level: Enter a number from 1 to 10',
        'Category: Choose from the available categories listed below',
        'Question: Write the full question text',
        'Answers A-D: Provide four answer choices',
        'Correct Answer: Enter 1 for A, 2 for B, 3 for C, or 4 for D'
    ];
    
    foreach ($instructions as $i => $instruction) {
        $doc .= '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr>';
        $doc .= '<w:r><w:t>' . htmlspecialchars($instruction, ENT_XML1) . '</w:t></w:r></w:p>';
    }
    
    // Categories heading
    $doc .= '<w:p><w:pPr><w:spacing w:before="240"/></w:pPr>';
    $doc .= '<w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="0A5F38"/></w:rPr>';
    $doc .= '<w:t>Available Categories for ' . htmlspecialchars($subject_label, ENT_XML1) . ':</w:t></w:r></w:p>';
    
    // Categories list
    foreach ($categories as $key => $label) {
        $doc .= '<w:p><w:pPr><w:ind w:left="360"/></w:pPr>';
        $doc .= '<w:r><w:rPr><w:b/></w:rPr><w:t>' . htmlspecialchars($key, ENT_XML1) . '</w:t></w:r>';
        $doc .= '<w:r><w:t>: ' . htmlspecialchars($label, ENT_XML1) . '</w:t></w:r></w:p>';
    }
    
    // Table
    $doc .= '<w:p><w:pPr><w:spacing w:before="240"/></w:pPr></w:p>';
    $doc .= '<w:tbl>';
    $doc .= '<w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders>';
    $doc .= '<w:top w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
    $doc .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
    $doc .= '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
    $doc .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
    $doc .= '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
    $doc .= '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
    $doc .= '</w:tblBorders></w:tblPr>';
    
    // Table header
    $headers = ['Subject', 'Level', 'Category', 'Question', 'Answer A', 'Answer B', 'Answer C', 'Answer D', 'Correct'];
    $doc .= '<w:tr>';
    foreach ($headers as $header) {
        $doc .= '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="0A5F38"/></w:tcPr>';
        $doc .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>';
        $doc .= '<w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/><w:sz w:val="18"/></w:rPr>';
        $doc .= '<w:t>' . htmlspecialchars($header, ENT_XML1) . '</w:t></w:r></w:p></w:tc>';
    }
    $doc .= '</w:tr>';
    
    // Hint row
    $hints = [
        'e.g. ' . $subject_name,
        '1 to 10',
        'Category key',
        'Full question text',
        'Option A',
        'Option B',
        'Option C',
        'Option D',
        '1-4'
    ];
    $doc .= '<w:tr>';
    foreach ($hints as $hint) {
        $doc .= '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="E8F5E9"/></w:tcPr>';
        $doc .= '<w:p><w:r><w:rPr><w:i/><w:color w:val="555555"/><w:sz w:val="16"/></w:rPr>';
        $doc .= '<w:t>' . htmlspecialchars($hint, ENT_XML1) . '</w:t></w:r></w:p></w:tc>';
    }
    $doc .= '</w:tr>';
    
    // Sample rows
    foreach ($samples as $sample) {
        $doc .= '<w:tr>';
        $values = [
            $sample['subject'],
            $sample['level'],
            $sample['category'],
            $sample['question'],
            $sample['a'],
            $sample['b'],
            $sample['c'],
            $sample['d'],
            $sample['correct']
        ];
        foreach ($values as $value) {
            $doc .= '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="FFF9C4"/></w:tcPr>';
            $doc .= '<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr>';
            $doc .= '<w:t>' . htmlspecialchars($value, ENT_XML1) . '</w:t></w:r></w:p></w:tc>';
        }
        $doc .= '</w:tr>';
    }
    
    // Empty rows for filling
    for ($i = 0; $i < 10; $i++) {
        $doc .= '<w:tr>';
        for ($j = 0; $j < 9; $j++) {
            $doc .= '<w:tc><w:p><w:r><w:t> </w:t></w:r></w:p></w:tc>';
        }
        $doc .= '</w:tr>';
    }
    
    $doc .= '</w:tbl>';
    
    // Footer
    $doc .= '<w:p><w:pPr><w:spacing w:before="240"/><w:jc w:val="center"/></w:pPr>';
    $doc .= '<w:r><w:rPr><w:sz w:val="16"/><w:color w:val="666666"/></w:rPr>';
    $doc .= '<w:t>Generated on ' . date('F d, Y') . ' | Play2Review Quiz Management System</w:t></w:r></w:p>';
    
    $doc .= '</w:body></w:document>';
    
    // Numbering XML for instructions list
    $numbering = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $numbering .= '<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">';
    $numbering .= '<w:abstractNum w:abstractNumId="0">';
    $numbering .= '<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/>';
    $numbering .= '<w:lvlText w:val="%1."/><w:lvlJc w:val="left"/></w:lvl>';
    $numbering .= '</w:abstractNum>';
    $numbering .= '<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>';
    $numbering .= '</w:numbering>';
    
    // Relationships
    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $rels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    $rels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>';
    $rels .= '</Relationships>';
    
    // Content types
    $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $ct .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
    $ct .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
    $ct .= '<Default Extension="xml" ContentType="application/xml"/>';
    $ct .= '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>';
    $ct .= '<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>';
    $ct .= '</Types>';
    
    // Package relationships
    $pkg_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $pkg_rels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    $pkg_rels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>';
    $pkg_rels .= '</Relationships>';
    
    // Build ZIP
    $tmp = tempnam(sys_get_temp_dir(), 'qdocx_');
    @unlink($tmp);
    $tmp .= '.docx';
    
    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE) !== true) {
        http_response_code(500);
        die('Could not create docx file. Check server temp directory permissions.');
    }
    
    $zip->addFromString('[Content_Types].xml', $ct);
    $zip->addFromString('_rels/.rels', $pkg_rels);
    $zip->addFromString('word/document.xml', $doc);
    $zip->addFromString('word/_rels/document.xml.rels', $rels);
    $zip->addFromString('word/numbering.xml', $numbering);
    $zip->close();
    
    // Stream to browser
    if (ob_get_level()) ob_end_clean();
    
    $fname = 'quiz_template_' . $subject_name . '.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    readfile($tmp);
    unlink($tmp);
}
