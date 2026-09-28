<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class SupplierQuotationOcr
{
    public function extractText(UploadedFile $file): array
    {
        if ($file->getMimeType() === 'application/pdf') {
            $text = $this->run([config('ocr.pdftotext_binary'), '-layout', $file->getPathname(), '-'], 30, false);

            if ($this->hasUsableText($text)) {
                return ['text' => $text, 'method' => 'teks PDF'];
            }

            return ['text' => $this->extractPdfPages($file->getPathname()), 'method' => 'OCR PDF scan'];
        }

        return ['text' => $this->extractImage($file->getPathname()), 'method' => 'OCR imej'];
    }

    private function extractPdfPages(string $pdfPath): string
    {
        $directory = storage_path('app/private/ocr/' . Str::uuid());
        File::ensureDirectoryExists($directory);
        $prefix = $directory . DIRECTORY_SEPARATOR . 'page';

        try {
            $this->run([
                config('ocr.pdftoppm_binary'),
                '-f', '1',
                '-l', (string) config('ocr.max_pdf_pages'),
                '-r', (string) config('ocr.pdf_dpi'),
                '-png',
                $pdfPath,
                $prefix,
            ], 90);

            $pages = glob($prefix . '-*.png') ?: [];
            natsort($pages);
            if ($pages === []) {
                throw new RuntimeException('Tiada halaman PDF dapat diproses untuk OCR.');
            }

            return collect($pages)
                ->map(fn (string $page) => $this->extractImage($page))
                ->filter()
                ->implode("\n");
        } finally {
            File::deleteDirectory($directory);
        }
    }

    private function extractImage(string $imagePath): string
    {
        $languages = (string) config('ocr.languages');
        $command = [config('ocr.tesseract_binary')];
        if ($tessdataDirectory = config('ocr.tessdata_dir')) {
            $command[] = '--tessdata-dir';
            $command[] = $tessdataDirectory;
        }
        $command = [...$command, $imagePath, 'stdout', '-l', $languages, '--psm', '6'];
        $text = $this->run($command, 45);

        if ($this->hasUsableText($text)) {
            return $text;
        }

        throw new RuntimeException('OCR tidak dapat mengenal pasti teks pada dokumen ini. Sila gunakan imej yang jelas dan tidak kabur.');
    }

    private function run(array $command, int $timeout, bool $required = true): string
    {
        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        if (! $required) {
            return '';
        }

        $binary = basename((string) $command[0]);
        throw new RuntimeException("{$binary} tidak dapat digunakan. Sila pastikan OCR telah dipasang dan tetapan server adalah betul.");
    }

    private function hasUsableText(string $text): bool
    {
        return mb_strlen(trim($text)) >= 20 && preg_match('/[[:alpha:]]/u', $text) === 1;
    }
}
