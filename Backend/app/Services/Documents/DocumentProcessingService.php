<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Services\OCR\OcrService;
use RuntimeException;

class DocumentProcessingService
{
    public function __construct(
        private OcrService $ocrService
    ) {}

    public function process(Document $document): Document
    {
        $document->update([
            'status' => 'processing',
        ]);

        try {
            $filePath = storage_path(
                'app/private/' . $document->file_path
            );

            $text = $this->ocrService->extractText(
                $filePath,
                $document->mime_type
            );

            $document->update([
                'ocr_text' => $text,
                'status' => 'review',
            ]);

            return $document->refresh();

        } catch (\Throwable $e) {
            $document->update([
                'status' => 'pending',
            ]);

            throw new RuntimeException(
                'Error procesando el documento: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}