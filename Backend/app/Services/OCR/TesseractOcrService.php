<?php

namespace App\Services\OCR;

use RuntimeException;

class TesseractOcrService implements OcrService
{
    public function extractText(
        string $filePath,
        string $mimeType
    ): string {
        if (!file_exists($filePath)) {
            throw new RuntimeException(
                "El archivo no existe: {$filePath}"
            );
        }

        if ($mimeType === 'application/pdf') {
            return $this->extractFromPdf($filePath);
        }

        return $this->extractFromImage($filePath);
    }

    private function extractFromImage(string $filePath): string
    {
        return $this->runTesseract($filePath);
    }

    private function extractFromPdf(string $filePath): string
    {
        $directory = sys_get_temp_dir() . '/ocr_' . uniqid();

        if (!mkdir($directory, 0755, true)) {
            throw new RuntimeException(
                'No fue posible crear el directorio temporal.'
            );
        }

        $outputPrefix = $directory . '/page';

        $command = sprintf(
            'pdftoppm -png %s %s 2>&1',
            escapeshellarg($filePath),
            escapeshellarg($outputPrefix)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->removeDirectory($directory);

            throw new RuntimeException(
                'No fue posible convertir el PDF: '
                . implode("\n", $output)
            );
        }

        $images = glob($directory . '/page-*.png');

        if (!$images) {
            $this->removeDirectory($directory);

            throw new RuntimeException(
                'No se encontraron páginas en el PDF.'
            );
        }

        sort($images);

        $texts = [];

        foreach ($images as $image) {
            $texts[] = $this->runTesseract($image);
        }

        $this->removeDirectory($directory);

        return trim(implode("\n\n", $texts));
    }

    private function runTesseract(string $filePath): string
    {
        $outputBase = tempnam(sys_get_temp_dir(), 'ocr_');

        if ($outputBase === false) {
            throw new RuntimeException(
                'No fue posible crear el archivo temporal.'
            );
        }

        unlink($outputBase);

        $command = sprintf(
            'tesseract %s %s -l spa 2>&1',
            escapeshellarg($filePath),
            escapeshellarg($outputBase)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Tesseract no pudo procesar el documento: '
                . implode("\n", $output)
            );
        }

        $textFile = $outputBase . '.txt';

        if (!file_exists($textFile)) {
            throw new RuntimeException(
                'Tesseract no generó el archivo de texto.'
            );
        }

        $text = file_get_contents($textFile);

        unlink($textFile);

        return trim($text);
    }

    private function removeDirectory(string $directory): void
    {
        $files = glob($directory . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($directory);
    }
}