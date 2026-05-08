<?php
/**
 * Generates a quiz template PDF using FPDF-like approach
 * Creates a structured PDF that can be reliably parsed back
 */

function generateQuizTemplatePdf(string $subject_name, array $categories, string $subject_label): void
{
    // We'll create a simple CSV-like structure embedded in PDF metadata
    // This allows reliable round-trip import/export
    
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
    
    // Build CSV content for embedding
    $csvData = "subject_name,quiz_level,category,question,answer_a,answer_b,answer_c,answer_d,correct_answer_number\n";
    $csvData .= "# Hint: e.g. $subject_name,1 to 10,Category key,Full question text,Option A,Option B,Option C,Option D,1-4\n";
    
    foreach ($samples as $sample) {
        $csvData .= sprintf('"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
            $sample['subject'],
            $sample['level'],
            $sample['category'],
            $sample['question'],
            $sample['a'],
            $sample['b'],
            $sample['c'],
            $sample['d'],
            $sample['correct']
        );
    }
    
    // Add 10 empty rows for filling
    for ($i = 0; $i < 10; $i++) {
        $csvData .= '"","","","","","","","",""\n';
    }
    
    // Create a simple PDF with embedded CSV data
    $pdfContent = "%PDF-1.4\n";
    $pdfContent .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $pdfContent .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    
    // Embed CSV data in a custom field
    $csvDataEncoded = base64_encode($csvData);
    
    // Page content with visible table
    $pageContent = "BT\n";
    $pageContent .= "/F1 16 Tf\n";
    $pageContent .= "50 750 Td\n";
    $pageContent .= "($subject_label - Quiz Template) Tj\n";
    $pageContent .= "0 -30 Td\n";
    $pageContent .= "/F1 10 Tf\n";
    $pageContent .= "(Instructions:) Tj\n";
    $pageContent .= "0 -15 Td\n";
    $pageContent .= "(1. Fill in your questions in this template) Tj\n";
    $pageContent .= "0 -15 Td\n";
    $pageContent .= "(2. Save and upload the PDF file for import) Tj\n";
    $pageContent .= "0 -30 Td\n";
    $pageContent .= "(Available Categories:) Tj\n";
    
    $yPos = -15;
    foreach ($categories as $key => $label) {
        $pageContent .= "0 $yPos Td\n";
        $pageContent .= "(  $key: $label) Tj\n";
    }
    
    $pageContent .= "0 -30 Td\n";
    $pageContent .= "(Sample Questions:) Tj\n";
    
    foreach ($samples as $idx => $sample) {
        $pageContent .= "0 -20 Td\n";
        $pageContent .= "(Q" . ($idx + 1) . ": {$sample['question']}) Tj\n";
        $pageContent .= "0 -12 Td\n";
        $pageContent .= "(  A: {$sample['a']}  B: {$sample['b']}) Tj\n";
        $pageContent .= "0 -12 Td\n";
        $pageContent .= "(  C: {$sample['c']}  D: {$sample['d']}) Tj\n";
        $pageContent .= "0 -12 Td\n";
        $pageContent .= "(  Correct: {$sample['correct']}) Tj\n";
    }
    
    $pageContent .= "0 -30 Td\n";
    $pageContent .= "(DATA: $csvDataEncoded) Tj\n";
    $pageContent .= "ET\n";
    
    $contentLength = strlen($pageContent);
    
    $pdfContent .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n";
    $pdfContent .= "4 0 obj\n<< /Length $contentLength >>\nstream\n$pageContent\nendstream\nendobj\n";
    $pdfContent .= "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n";
    
    $contentPos = strpos($pdfContent, "4 0 obj");
    $pdfContent .= sprintf("%010d 00000 n \n", $contentPos);
    $pdfContent .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n";
    $pdfContent .= strlen($pdfContent) . "\n%%EOF";
    
    // Output PDF
    if (ob_get_level()) ob_end_clean();
    
    $filename = 'quiz_template_' . $subject_name . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    echo $pdfContent;
}
