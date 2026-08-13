<?php

namespace App\Services\OCR;

interface OcrService
{
    public function extractText(
        string $filePath,
        string $mimeType
    ): string;
}