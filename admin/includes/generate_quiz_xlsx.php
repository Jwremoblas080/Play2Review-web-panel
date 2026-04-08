<?php
/**
 * Generates a quiz template .xlsx with dropdowns for category, level, subject, correct answer.
 * Uses ZipArchive (built-in PHP) — no composer needed.
 *
 * Usage:
 *   require_once 'includes/generate_quiz_xlsx.php';
 *   generateQuizTemplateXlsx($subject_name, $categories, $subject_label);
 *   exit();
 */
function generateQuizTemplateXlsx(string $subject_name, array $categories, string $subject_label): void
{
    $cols = ['A','B','C','D','E','F','G','H','I'];

    $headers = ['subject_name','quiz_level','category','question','answer_a','answer_b','answer_c','answer_d','correct_answer_number'];

    $hints = [
        'e.g. '.$subject_name,
        '1 to 10',
        'Pick category KEY from dropdown',
        'Write the full question here',
        'First choice (Option A)',
        'Second choice (Option B)',
        'Third choice (Option C)',
        'Fourth choice (Option D)',
        '1=A  2=B  3=C  4=D',
    ];

    $cat_keys = array_keys($categories);
    $samples = [
        [$subject_name,'1',$cat_keys[0] ?? 'grammar','What is the capital of the Philippines?','Cebu','Manila','Davao','Quezon City','2'],
        [$subject_name,'2',$cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'),'Which planet is closest to the sun?','Earth','Venus','Mercury','Mars','3'],
    ];

    // ── Shared strings ──────────────────────────────────────────────────────
    $strings = [];
    $idx     = [];
    $ss = function(string $v) use (&$strings, &$idx): int {
        if (!isset($idx[$v])) { $idx[$v] = count($strings); $strings[] = $v; }
        return $idx[$v];
    };

    // ── Styles XML (no heredoc to avoid whitespace issues) ──────────────────
    $sty = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
         . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
         . '<fonts count="5">'
         .   '<font><sz val="11"/><name val="Calibri"/></font>'
         .   '<font><b/><sz val="13"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
         .   '<font><i/><sz val="10"/><color rgb="FF1565C0"/><name val="Calibri"/></font>'
         .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
         .   '<font><i/><sz val="10"/><color rgb="FF555555"/><name val="Calibri"/></font>'
         . '</fonts>'
         . '<fills count="9">'
         .   '<fill><patternFill patternType="none"/></fill>'
         .   '<fill><patternFill patternType="gray125"/></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FF0A5F38"/></patternFill></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FFE3F2FD"/></patternFill></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FF1E7D4E"/></patternFill></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F5E9"/></patternFill></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF9C4"/></patternFill></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF3E0"/></patternFill></fill>'
         .   '<fill><patternFill patternType="solid"><fgColor rgb="FFC8E6C9"/></patternFill></fill>'
         . '</fills>'
         . '<borders count="2">'
         .   '<border><left/><right/><top/><bottom/><diagonal/></border>'
         .   '<border>'
         .     '<left style="thin"><color rgb="FFCCCCCC"/></left>'
         .     '<right style="thin"><color rgb="FFCCCCCC"/></right>'
         .     '<top style="thin"><color rgb="FFCCCCCC"/></top>'
         .     '<bottom style="thin"><color rgb="FFCCCCCC"/></bottom>'
         .   '</border>'
         . '</borders>'
         . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
         . '<cellXfs count="8">'
         // 0 default
         .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
         // 1 title: dark green bg, white bold, centered
         .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
         // 2 info: light blue bg, blue italic
         .   '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
         // 3 header: medium green bg, white bold, centered
         .   '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
         // 4 hint: light green bg, grey italic
         .   '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
         // 5 sample yellow
         .   '<xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
         // 6 sample orange
         .   '<xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
         // 7 correct answer: green bg, bold centered
         .   '<xf numFmtId="0" fontId="3" fillId="8" borderId="1" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
         . '</cellXfs>'
         . '</styleSheet>';

    // ── Sheet rows ──────────────────────────────────────────────────────────
    $rows = '';

    // Row 1: title
    $rows .= '<row r="1" ht="28" customHeight="1">'
           . '<c r="A1" t="s" s="1"><v>'.$ss($subject_label.' - Quiz Template').'</v></c>'
           . '</row>';

    // Row 2: instructions
    $instr = 'INSTRUCTIONS: Fill one question per row starting from Row 6. '
           . 'Use the dropdowns for Subject, Level, Category and Correct Answer. '
           . 'Do NOT edit rows 1-5. Save as CSV before uploading.';
    $rows .= '<row r="2" ht="40" customHeight="1">'
           . '<c r="A2" t="s" s="2"><v>'.$ss($instr).'</v></c>'
           . '</row>';

    // Row 3: spacer
    $rows .= '<row r="3"><c r="A3" s="0"/></row>';

    // Row 4: headers
    $rows .= '<row r="4" ht="20" customHeight="1">';
    foreach ($cols as $i => $c) {
        $rows .= '<c r="'.$c.'4" t="s" s="3"><v>'.$ss($headers[$i]).'</v></c>';
    }
    $rows .= '</row>';

    // Row 5: hints
    $rows .= '<row r="5" ht="16" customHeight="1">';
    foreach ($cols as $i => $c) {
        $rows .= '<c r="'.$c.'5" t="s" s="4"><v>'.$ss($hints[$i]).'</v></c>';
    }
    $rows .= '</row>';

    // Rows 6-7: samples
    foreach ($samples as $si => $sample) {
        $rn  = 6 + $si;
        $sty_idx = ($si === 0) ? 5 : 6;
        $rows .= '<row r="'.$rn.'" ht="16" customHeight="1">';
        foreach ($cols as $i => $c) {
            $s = ($i === 8) ? 7 : $sty_idx;
            $rows .= '<c r="'.$c.$rn.'" t="s" s="'.$s.'"><v>'.$ss($sample[$i]).'</v></c>';
        }
        $rows .= '</row>';
    }

    // ── Column widths ───────────────────────────────────────────────────────
    $widths = [18,12,30,52,22,22,22,22,22];
    $cols_xml = '<cols>';
    foreach ($cols as $i => $c) {
        $n = $i + 1;
        $cols_xml .= '<col min="'.$n.'" max="'.$n.'" width="'.$widths[$i].'" customWidth="1"/>';
    }
    $cols_xml .= '</cols>';

    // ── Merge cells ─────────────────────────────────────────────────────────
    $merges = '<mergeCells count="2"><mergeCell ref="A1:I1"/><mergeCell ref="A2:I2"/></mergeCells>';

    // ── Data validations ────────────────────────────────────────────────────
    // Build category list — escape ampersands etc for XML
    $cat_escaped = implode(',', array_map(function($c) {
        return htmlspecialchars($c, ENT_XML1, 'UTF-8');
    }, $categories));
    $cat_formula = '"'.$cat_escaped.'"';

    $dv  = '<dataValidations count="4">';
    $dv .= '<dataValidation type="list" allowBlank="0" showDropDown="0" sqref="A6:A200">'
         . '<formula1>&quot;english,ap,filipino,math,science&quot;</formula1>'
         . '</dataValidation>';
    $dv .= '<dataValidation type="list" allowBlank="0" showDropDown="0" sqref="B6:B200">'
         . '<formula1>&quot;1,2,3,4,5,6,7,8,9,10&quot;</formula1>'
         . '</dataValidation>';
    $dv .= '<dataValidation type="list" allowBlank="0" showDropDown="0" sqref="C6:C200">'
         . '<formula1>'.$cat_formula.'</formula1>'
         . '</dataValidation>';
    $dv .= '<dataValidation type="list" allowBlank="0" showDropDown="0" sqref="I6:I200">'
         . '<formula1>&quot;1,2,3,4&quot;</formula1>'
         . '</dataValidation>';
    $dv .= '</dataValidations>';

    // ── Shared strings XML ──────────────────────────────────────────────────
    $cnt = count($strings);
    $sst = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
         . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
         . ' count="'.$cnt.'" uniqueCount="'.$cnt.'">';
    foreach ($strings as $s) {
        $sst .= '<si><t xml:space="preserve">'.htmlspecialchars($s, ENT_XML1|ENT_QUOTES, 'UTF-8').'</t></si>';
    }
    $sst .= '</sst>';

    // ── Sheet XML ───────────────────────────────────────────────────────────
    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
           . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
           . '<sheetViews><sheetView workbookViewId="0"><selection activeCell="A6" sqref="A6"/></sheetView></sheetViews>'
           . '<sheetFormatPr defaultRowHeight="15"/>'
           . $cols_xml
           . '<sheetData>'.$rows.'</sheetData>'
           . $merges
           . $dv
           . '</worksheet>';

    // ── Workbook XML ────────────────────────────────────────────────────────
    $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
        . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="14400" windowHeight="8700"/></bookViews>'
        . '<sheets><sheet name="Quiz Template" sheetId="1" r:id="rId1"/></sheets>'
        . '</workbook>';

    // ── Relationships ────────────────────────────────────────────────────────
    $wb_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
             . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
             . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
             . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
             . '</Relationships>';

    $pkg_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
              . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
              . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
              . '</Relationships>';

    $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';

    // ── Build ZIP ────────────────────────────────────────────────────────────
    $tmp = tempnam(sys_get_temp_dir(), 'qxlsx_');
    @unlink($tmp); // ZipArchive needs the file not to exist for CREATE
    $tmp .= '.xlsx';

    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE) !== true) {
        http_response_code(500);
        die('Could not create xlsx file. Check server temp directory permissions.');
    }

    $zip->addFromString('[Content_Types].xml',        $ct);
    $zip->addFromString('_rels/.rels',                $pkg_rels);
    $zip->addFromString('xl/workbook.xml',            $wb);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $wb_rels);
    $zip->addFromString('xl/worksheets/sheet1.xml',   $sheet);
    $zip->addFromString('xl/sharedStrings.xml',       $sst);
    $zip->addFromString('xl/styles.xml',              $sty);
    $zip->close();

    // ── Stream to browser ────────────────────────────────────────────────────
    // Clear any buffered output that would corrupt the binary file
    if (ob_get_level()) ob_end_clean();

    $fname = 'quiz_template_'.$subject_name.'.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Content-Length: '.filesize($tmp));
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    readfile($tmp);
    unlink($tmp);
}
