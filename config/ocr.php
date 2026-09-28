<?php

return [
    'tesseract_binary' => env('TESSERACT_BINARY', 'tesseract'),
    'tessdata_dir' => env('TESSDATA_DIR'),
    'pdftotext_binary' => env('PDFTOTEXT_BINARY', 'pdftotext'),
    'pdftoppm_binary' => env('PDFTOPPM_BINARY', 'pdftoppm'),
    'languages' => env('OCR_LANGUAGES', 'eng+msa'),
    'pdf_dpi' => (int) env('OCR_PDF_DPI', 200),
    'max_pdf_pages' => (int) env('OCR_MAX_PDF_PAGES', 10),
];
