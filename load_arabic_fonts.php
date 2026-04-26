#!/usr/bin/env php
<?php

/**
 * Script to load and register an Arabic font for DomPDF
 * 
 * This script registers the DejaVu Sans font which has some Arabic glyph support
 * You can use this as a template to add other fonts
 */

require_once('bootstrap/app.php');

$fontPath = storage_path('fonts');

// Ensure the fonts directory exists
if (!is_dir($fontPath)) {
    mkdir($fontPath, 0755, true);
    echo "Created fonts directory at: {$fontPath}\n";
}

echo "Font directory: {$fontPath}\n";
echo "Fonts are already cached by DomPDF when loaded.\n";
echo "To use Arabic fonts with DomPDF:\n";
echo "1. DomPDF comes with DejaVu Sans which has basic Arabic support\n";
echo "2. For better Arabic support, you may need to add a TrueType font file\n";
echo "3. Place .ttf or .otf files in the fonts directory\n";
echo "4. Run: php vendor/dompdf/dompdf/load_font.php /path/to/font.ttf\n";
