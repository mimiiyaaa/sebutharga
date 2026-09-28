# OCR Sebut Harga Pembekal

Sistem membaca PDF yang mempunyai teks secara terus. Jika PDF ialah dokumen scan atau fail yang dimuat naik ialah JPG, JPEG atau PNG, sistem menggunakan Tesseract OCR.

## Server Linux (Ubuntu atau Debian)

Pasang kebergantungan berikut pada server production:

```bash
sudo apt-get update
sudo apt-get install -y poppler-utils tesseract-ocr tesseract-ocr-eng tesseract-ocr-msa
```

Tetapan `.env` menggunakan nilai berikut jika executable berada dalam `PATH`:

```dotenv
TESSERACT_BINARY=tesseract
PDFTOTEXT_BINARY=pdftotext
PDFTOPPM_BINARY=pdftoppm
OCR_LANGUAGES=eng+msa
OCR_PDF_DPI=200
OCR_MAX_PDF_PAGES=10
```

Selepas deploy, bina semula cache aplikasi:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Sahkan pemasangan dengan:

```bash
tesseract --list-langs
pdftotext -v
pdftoppm -v
```

Senarai bahasa Tesseract mesti mengandungi `eng` dan `msa`. Jika server tidak mempunyai data bahasa Melayu, tetapkan `OCR_LANGUAGES=eng` sementara data `msa` dipasang.

## Had pemprosesan

- Saiz dokumen: maksimum 10MB.
- PDF scan: maksimum 10 halaman bagi satu bacaan OCR.
- Resolusi pemprosesan: 200 DPI.

Selepas OCR, maklumat pembekal dan item masih perlu disemak sebelum disimpan kerana ketepatan teks bergantung pada kualiti dokumen asal.
