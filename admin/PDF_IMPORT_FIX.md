# PDF Import/Export Fix

## Problem
PDF import was failing with error: "Could not extract quiz data from this PDF"

## Root Cause
The PDF download handler was using `generate_quiz_pdf_v2.php` (HTML-based PDF) which doesn't reliably embed data, while a better implementation `generate_quiz_pdf_final.php` (TRUE PDF with embedded data) existed but wasn't being used.

## Solution

### 1. Updated PDF Download Handler
**File**: `admin/manage-quizes.php` (line ~67)

Changed from:
```php
require_once('includes/generate_quiz_pdf_v2.php');
generateQuizTemplatePdfV2($subject_name, $categories, $subject_label);
```

To:
```php
require_once('includes/generate_quiz_pdf_final.php');
generateQuizTemplatePdfFinal($subject_name, $categories, $subject_label);
```

### 2. Enhanced PDF Import Parser
**File**: `admin/includes/import_quiz_pdf.php`

Added support for multiple data extraction methods:

1. **XMP Metadata Extraction** - Extracts data from `<quiz:data>` tag in PDF metadata
2. **PDF Comment Extraction** - Extracts data from PDF comments (lines starting with `%`)
3. **Legacy Format Support** - Still supports old `QUIZ_DATA_START:base64:QUIZ_DATA_END` format

The parser now tries all three methods in order, ensuring maximum compatibility.

## How It Works

### PDF Generation (`generate_quiz_pdf_final.php`)
1. Creates a TRUE PDF (not HTML-to-PDF)
2. Embeds CSV data in TWO locations for redundancy:
   - **XMP Metadata**: `<quiz:data>base64_encoded_csv</quiz:data>`
   - **PDF Comments**: Chunked base64 data in comment lines between markers
3. Displays human-readable instructions and sample questions
4. Includes all quiz categories for reference

### PDF Import (`import_quiz_pdf.php`)
1. Reads PDF file as binary
2. Tries Method 1: Extract from XMP metadata
3. Tries Method 2: Extract from PDF comments
4. Tries Method 3: Extract from legacy format
5. Tries Fallback: Extract from PDF text streams
6. Parses extracted CSV data and returns question rows

## Testing
1. Download a PDF template from "Download Template" → "PDF"
2. Upload the same PDF via "Import Questions"
3. Should successfully import the 2 sample questions

## Files Modified
- `admin/manage-quizes.php` - Updated PDF download handler
- `admin/includes/import_quiz_pdf.php` - Enhanced import parser with multiple extraction methods

## Files Involved (Not Modified)
- `admin/includes/generate_quiz_pdf_final.php` - TRUE PDF generator (already existed)
- `admin/includes/generate_quiz_pdf_v2.php` - Old HTML-based generator (deprecated, no longer used)
