<?php
/**
 * Generates a valid PDF file with embedded CSV data.
 *
 * The CSV payload is embedded in two places for redundancy:
 *   1. XMP Metadata object  (<quiz:data>base64</quiz:data>)
 *   2. Page content stream  (% QUIZ_CSV_DATA_START … % QUIZ_CSV_DATA_END)
 *
 * The cross-reference table uses accurate byte offsets so the file is
 * fully spec-compliant and opens correctly in all PDF readers.
 */

function generateQuizTemplatePdfFinal(string $subject_name, array $categories, string $subject_label): void
{
    $cat_keys = array_keys($categories);

    // ── Build CSV payload ─────────────────────────────────────────────────────
    $csvLines   = [];
    $csvLines[] = ['subject_name', 'quiz_level', 'category', 'question', 'answer_a', 'answer_b', 'answer_c', 'answer_d', 'correct_answer_number'];
    $csvLines[] = [$subject_name, '1', $cat_keys[0] ?? 'grammar',     'What is the capital of the Philippines?', 'Cebu',  'Manila',  'Davao',   'Quezon City', '2'];
    $csvLines[] = [$subject_name, '2', $cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'), 'Which planet is closest to the sun?', 'Earth', 'Venus', 'Mercury', 'Mars', '3'];

    for ($i = 0; $i < 15; $i++) {
        $csvLines[] = ['', '', '', '', '', '', '', '', ''];
    }

    $csvContent = '';
    foreach ($csvLines as $line) {
        $escaped     = array_map(fn($c) => '"' . str_replace('"', '""', $c) . '"', $line);
        $csvContent .= implode(',', $escaped) . "\n";
    }

    $encodedData = base64_encode($csvContent);
    $dataChunks  = str_split($encodedData, 60);

    // ── Object 5: XMP Metadata ────────────────────────────────────────────────
    $metadataContent  = "<?xpacket begin='' id='W5M0MpCehiHzreSzNTczkc9d'?>\n";
    $metadataContent .= "<x:xmpmeta xmlns:x='adobe:ns:meta/'>\n";
    $metadataContent .= "<rdf:RDF xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n";
    $metadataContent .= "<rdf:Description rdf:about='' xmlns:quiz='http://play2review.com/quiz/'>\n";
    $metadataContent .= "<quiz:data>" . $encodedData . "</quiz:data>\n";
    $metadataContent .= "</rdf:Description>\n";
    $metadataContent .= "</rdf:RDF>\n";
    $metadataContent .= "</x:xmpmeta>\n";
    $metadataContent .= "<?xpacket end='w'?>";

    // ── Object 6: Page content stream ─────────────────────────────────────────
    $content  = "BT\n";
    $content .= "/F2 20 Tf\n50 750 Td\n";
    $content .= "(" . _pdfEscape($subject_label) . " - Quiz Template) Tj\n";
    $content .= "0 -30 Td\n/F1 11 Tf\n(Instructions:) Tj\n";
    $content .= "0 -15 Td\n/F1 9 Tf\n";
    $content .= "(1. This PDF contains embedded quiz data) Tj\n0 -12 Td\n";
    $content .= "(2. Upload this PDF file to import the quiz questions) Tj\n0 -12 Td\n";
    $content .= "(3. Sample questions are pre-filled as examples) Tj\n0 -25 Td\n";
    $content .= "/F2 10 Tf\n(Available Categories:) Tj\n0 -15 Td\n/F1 9 Tf\n";

    foreach ($categories as $key => $label) {
        $content .= "(  " . _pdfEscape($key) . ": " . _pdfEscape($label) . ") Tj\n0 -12 Td\n";
    }

    $content .= "0 -13 Td\n/F2 10 Tf\n(Sample Questions Included:) Tj\n0 -15 Td\n/F1 9 Tf\n";

    for ($i = 1; $i <= 2; $i++) {
        $sample   = $csvLines[$i];
        $content .= "(Q" . $i . ": " . _pdfEscape(substr($sample[3], 0, 50)) . ") Tj\n0 -12 Td\n";
        $content .= "(  Level: " . $sample[1] . " | Category: " . _pdfEscape($sample[2]) . ") Tj\n0 -12 Td\n";
        $content .= "(  Correct Answer: " . $sample[8] . ") Tj\n0 -15 Td\n";
    }

    $content .= "(Total: " . (count($csvLines) - 1) . " question slots available) Tj\n0 -20 Td\n";
    $content .= "/F1 8 Tf\n(Generated: " . date('Y-m-d H:i:s') . " | Play2Review Quiz System) Tj\n";

    // Embed CSV data as PDF comments inside the content stream
    $content .= "\n% QUIZ_CSV_DATA_START\n";
    foreach ($dataChunks as $chunk) {
        $content .= "% " . $chunk . "\n";
    }
    $content .= "% QUIZ_CSV_DATA_END\n";
    $content .= "ET\n";

    // ── Build PDF with accurate XRef offsets ──────────────────────────────────
    $pdf     = "%PDF-1.4\n";
    $pdf    .= "%\xE2\xE3\xCF\xD3\n"; // 4 high-bit bytes — marks file as binary

    $offsets = [];

    // Object 1: Catalog
    $offsets[1] = strlen($pdf);
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R /Metadata 5 0 R >>\nendobj\n\n";

    // Object 2: Pages
    $offsets[2] = strlen($pdf);
    $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n\n";

    // Object 3: Page
    $offsets[3] = strlen($pdf);
    $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 6 0 R >>\nendobj\n\n";

    // Object 4: Resources (fonts)
    $offsets[4] = strlen($pdf);
    $pdf .= "4 0 obj\n";
    $pdf .= "<< /Font << ";
    $pdf .= "/F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> ";
    $pdf .= "/F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> ";
    $pdf .= ">> >>\n";
    $pdf .= "endobj\n\n";

    // Object 5: XMP Metadata
    $offsets[5] = strlen($pdf);
    $pdf .= "5 0 obj\n";
    $pdf .= "<< /Type /Metadata /Subtype /XML /Length " . strlen($metadataContent) . " >>\n";
    $pdf .= "stream\n";
    $pdf .= $metadataContent . "\n";
    $pdf .= "endstream\nendobj\n\n";

    // Object 6: Page content
    $offsets[6] = strlen($pdf);
    $pdf .= "6 0 obj\n";
    $pdf .= "<< /Length " . strlen($content) . " >>\n";
    $pdf .= "stream\n";
    $pdf .= $content;
    $pdf .= "endstream\nendobj\n\n";

    // XRef table
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 7\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= 6; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    // Trailer
    $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefPos . "\n%%EOF\n";

    // ── Stream to browser ─────────────────────────────────────────────────────
    if (ob_get_level()) {
        ob_end_clean();
    }

    $filename = 'quiz_template_' . $subject_name . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    echo $pdf;
}

/**
 * Escape special characters for use inside PDF text strings ( ... ).
 * Parentheses and backslashes must be escaped.
 */
function _pdfEscape(string $text): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}
