<?php
/**
 * Generates a TRUE PDF file with embedded CSV data in PDF structure
 * This creates a real PDF (not HTML) with data embedded in PDF objects
 */

function generateQuizTemplatePdfFinal(string $subject_name, array $categories, string $subject_label): void
{
    $cat_keys = array_keys($categories);
    
    // Prepare CSV data
    $csvLines = [];
    $csvLines[] = ['subject_name', 'quiz_level', 'category', 'question', 'answer_a', 'answer_b', 'answer_c', 'answer_d', 'correct_answer_number'];
    $csvLines[] = [$subject_name, '1', $cat_keys[0] ?? 'grammar', 'What is the capital of the Philippines?', 'Cebu', 'Manila', 'Davao', 'Quezon City', '2'];
    $csvLines[] = [$subject_name, '2', $cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'), 'Which planet is closest to the sun?', 'Earth', 'Venus', 'Mercury', 'Mars', '3'];
    
    // Add 15 empty rows
    for ($i = 0; $i < 15; $i++) {
        $csvLines[] = ['', '', '', '', '', '', '', '', ''];
    }
    
    // Convert to CSV string
    $csvContent = '';
    foreach ($csvLines as $line) {
        $escaped = array_map(function($cell) {
            return '"' . str_replace('"', '""', $cell) . '"';
        }, $line);
        $csvContent .= implode(',', $escaped) . "\n";
    }
    
    // Encode CSV data
    $encodedData = base64_encode($csvContent);
    
    // Split encoded data into chunks for PDF
    $dataChunks = str_split($encodedData, 60);
    
    // Build PDF content
    $pdf = "%PDF-1.4\n";
    $pdf .= "%âãÏÓ\n"; // Binary marker
    
    // Object 1: Catalog
    $pdf .= "1 0 obj\n";
    $pdf .= "<< /Type /Catalog /Pages 2 0 R /Metadata 5 0 R >>\n";
    $pdf .= "endobj\n\n";
    
    // Object 2: Pages
    $pdf .= "2 0 obj\n";
    $pdf .= "<< /Type /Pages /Kids [3 0 R] /Count 1 >>\n";
    $pdf .= "endobj\n\n";
    
    // Object 3: Page
    $pdf .= "3 0 obj\n";
    $pdf .= "<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 6 0 R >>\n";
    $pdf .= "endobj\n\n";
    
    // Object 4: Resources (Font)
    $pdf .= "4 0 obj\n";
    $pdf .= "<< /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> >> >>\n";
    $pdf .= "endobj\n\n";
    
    // Object 5: Metadata with embedded CSV data
    $metadataContent = "<?xpacket begin='' id='W5M0MpCehiHzreSzNTczkc9d'?>\n";
    $metadataContent .= "<x:xmpmeta xmlns:x='adobe:ns:meta/'>\n";
    $metadataContent .= "<rdf:RDF xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n";
    $metadataContent .= "<rdf:Description rdf:about='' xmlns:quiz='http://play2review.com/quiz/'>\n";
    $metadataContent .= "<quiz:data>" . $encodedData . "</quiz:data>\n";
    $metadataContent .= "</rdf:Description>\n";
    $metadataContent .= "</rdf:RDF>\n";
    $metadataContent .= "</x:xmpmeta>\n";
    $metadataContent .= "<?xpacket end='w'?>";
    
    $pdf .= "5 0 obj\n";
    $pdf .= "<< /Type /Metadata /Subtype /XML /Length " . strlen($metadataContent) . " >>\n";
    $pdf .= "stream\n";
    $pdf .= $metadataContent . "\n";
    $pdf .= "endstream\n";
    $pdf .= "endobj\n\n";
    
    // Object 6: Page Content
    $content = "BT\n";
    $content .= "/F2 20 Tf\n";
    $content .= "50 750 Td\n";
    $content .= "(" . $subject_label . " - Quiz Template) Tj\n";
    $content .= "0 -30 Td\n";
    $content .= "/F1 11 Tf\n";
    $content .= "(Instructions:) Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "/F1 9 Tf\n";
    $content .= "(1. This PDF contains embedded quiz data) Tj\n";
    $content .= "0 -12 Td\n";
    $content .= "(2. Upload this PDF file to import the quiz questions) Tj\n";
    $content .= "0 -12 Td\n";
    $content .= "(3. Sample questions are included below) Tj\n";
    $content .= "0 -25 Td\n";
    $content .= "/F2 10 Tf\n";
    $content .= "(Available Categories:) Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "/F1 9 Tf\n";
    
    $yOffset = 0;
    foreach ($categories as $key => $label) {
        $content .= "0 -12 Td\n";
        $content .= "(  " . $key . ": " . $label . ") Tj\n";
        $yOffset += 12;
    }
    
    $content .= "0 -25 Td\n";
    $content .= "/F2 10 Tf\n";
    $content .= "(Sample Questions Included:) Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "/F1 9 Tf\n";
    
    // Display sample questions
    for ($i = 1; $i < 3; $i++) {
        $sample = $csvLines[$i];
        $content .= "0 -15 Td\n";
        $content .= "(Q" . $i . ": " . substr($sample[3], 0, 50) . ") Tj\n";
        $content .= "0 -12 Td\n";
        $content .= "(  Level: " . $sample[1] . " | Category: " . $sample[2] . ") Tj\n";
        $content .= "0 -12 Td\n";
        $content .= "(  Correct Answer: " . $sample[8] . ") Tj\n";
    }
    
    $content .= "0 -30 Td\n";
    $content .= "(Total: " . (count($csvLines) - 1) . " question slots available) Tj\n";
    $content .= "0 -20 Td\n";
    $content .= "/F1 8 Tf\n";
    $content .= "(Generated: " . date('Y-m-d H:i:s') . " | Play2Review Quiz System) Tj\n";
    
    // Add data marker as comment in content stream
    $content .= "\n% QUIZ_CSV_DATA_START\n";
    foreach ($dataChunks as $chunk) {
        $content .= "% " . $chunk . "\n";
    }
    $content .= "% QUIZ_CSV_DATA_END\n";
    
    $content .= "ET\n";
    
    $pdf .= "6 0 obj\n";
    $pdf .= "<< /Length " . strlen($content) . " >>\n";
    $pdf .= "stream\n";
    $pdf .= $content;
    $pdf .= "endstream\n";
    $pdf .= "endobj\n\n";
    
    // Cross-reference table
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 7\n";
    $pdf .= "0000000000 65535 f \n";
    
    // Calculate positions (approximate - good enough for simple PDF)
    $positions = [
        9,      // obj 1
        70,     // obj 2
        140,    // obj 3
        270,    // obj 4
        450,    // obj 5
        450 + strlen($metadataContent) + 100,  // obj 6
    ];
    
    foreach ($positions as $pos) {
        $pdf .= sprintf("%010d 00000 n \n", $pos);
    }
    
    // Trailer
    $pdf .= "trailer\n";
    $pdf .= "<< /Size 7 /Root 1 0 R >>\n";
    $pdf .= "startxref\n";
    $pdf .= $xrefPos . "\n";
    $pdf .= "%%EOF\n";
    
    // Output PDF
    if (ob_get_level()) ob_end_clean();
    
    $filename = 'quiz_template_' . $subject_name . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    echo $pdf;
}
