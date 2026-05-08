<?php
/**
 * Generates a quiz template PDF with embedded form data
 * This creates a PDF that includes both visual content and structured data
 */

function generateQuizTemplatePdfV2(string $subject_name, array $categories, string $subject_label): void
{
    $cat_keys = array_keys($categories);
    
    // Prepare sample data
    $rows = [
        ['subject_name', 'quiz_level', 'category', 'question', 'answer_a', 'answer_b', 'answer_c', 'answer_d', 'correct_answer_number'],
        ['# HINT', '1-10', 'category_key', 'Your question here', 'Option A', 'Option B', 'Option C', 'Option D', '1-4'],
        [$subject_name, '1', $cat_keys[0] ?? 'grammar', 'What is the capital of the Philippines?', 'Cebu', 'Manila', 'Davao', 'Quezon City', '2'],
        [$subject_name, '2', $cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'), 'Which planet is closest to the sun?', 'Earth', 'Venus', 'Mercury', 'Mars', '3'],
    ];
    
    // Add 10 empty rows
    for ($i = 0; $i < 10; $i++) {
        $rows[] = ['', '', '', '', '', '', '', '', ''];
    }
    
    // Convert to CSV format
    $csvContent = '';
    foreach ($rows as $row) {
        $csvContent .= '"' . implode('","', array_map(function($cell) {
            return str_replace('"', '""', $cell);
        }, $row)) . '"' . "\n";
    }
    
    // Encode CSV as base64 for embedding
    $encodedData = base64_encode($csvContent);
    
    // Create PDF content with embedded data marker
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($subject_label) . ' - Quiz Template</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 10pt; }
        h1 { color: #0A5F38; text-align: center; border-bottom: 3px solid #0A5F38; padding-bottom: 10px; font-size: 18pt; }
        .instructions { background: #E8F5E9; padding: 10px; border-left: 4px solid #0A5F38; margin: 15px 0; font-size: 9pt; }
        .instructions h3 { margin-top: 0; color: #0A5F38; font-size: 11pt; }
        .instructions ol { margin: 5px 0; padding-left: 20px; }
        .category-list { background: #F5F5F5; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 9pt; }
        .category-list h4 { margin-top: 0; color: #0A5F38; font-size: 10pt; }
        .category-list ul { margin: 5px 0; padding-left: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 8pt; }
        th { background: #0A5F38; color: white; padding: 6px 4px; text-align: left; font-size: 8pt; border: 1px solid #0A5F38; }
        td { border: 1px solid #ddd; padding: 6px 4px; min-height: 20px; }
        .sample-row { background: #FFF9C4; }
        .hint-row { background: #E8F5E9; font-style: italic; color: #555; }
        .data-marker { display: none; font-size: 1pt; color: white; }
        .footer { text-align: center; margin-top: 20px; font-size: 8pt; color: #666; }
        @media print {
            body { margin: 10mm; }
            .data-marker { display: block !important; }
        }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($subject_label) . ' - Quiz Template</h1>
    
    <div class="instructions">
        <h3>Instructions:</h3>
        <ol>
            <li>Fill in your questions in the table below</li>
            <li>Subject: Use "' . htmlspecialchars($subject_name) . '"</li>
            <li>Level: Enter 1-10</li>
            <li>Category: Use category keys from the list below</li>
            <li>Save as PDF and upload for import</li>
        </ol>
    </div>
    
    <div class="category-list">
        <h4>Available Categories:</h4>
        <ul>';
    
    foreach ($categories as $key => $label) {
        $html .= '<li><strong>' . htmlspecialchars($key) . '</strong>: ' . htmlspecialchars($label) . '</li>';
    }
    
    $html .= '</ul>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Subject</th>
                <th style="width: 6%;">Level</th>
                <th style="width: 12%;">Category</th>
                <th style="width: 28%;">Question</th>
                <th style="width: 11%;">Answer A</th>
                <th style="width: 11%;">Answer B</th>
                <th style="width: 11%;">Answer C</th>
                <th style="width: 11%;">Answer D</th>
                <th style="width: 6%;">Correct</th>
            </tr>
        </thead>
        <tbody>';
    
    // Skip header row (index 0) and hint row (index 1) for display
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $rowClass = '';
        if ($i == 1) $rowClass = 'hint-row';
        elseif ($i == 2 || $i == 3) $rowClass = 'sample-row';
        
        $html .= '<tr class="' . $rowClass . '">';
        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody>
    </table>
    
    <div class="data-marker">QUIZ_DATA_START:' . $encodedData . ':QUIZ_DATA_END</div>
    
    <div class="footer">
        <p>Generated: ' . date('F d, Y') . ' | Play2Review Quiz Management System</p>
        <p><strong>Important:</strong> Save this page as PDF (Ctrl+P or Cmd+P) to preserve the data for import.</p>
    </div>
    
    <script>
        // Auto-trigger print dialog
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>';
    
    // Output HTML for browser to convert to PDF
    if (ob_get_level()) ob_end_clean();
    
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
}
