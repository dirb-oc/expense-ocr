<?php

namespace App\Services\Documents;

use App\Services\Extraction\DocumentExtractor;
use App\Services\OCR\OcrService;
use App\Models\Document;
use RuntimeException;

class DocumentProcessingService
{
    public function __construct(
        private OcrService $ocrService,
        private DocumentExtractor $extractor
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

            $data = $this->extractor->extract($text);

            $document->update([
                'ocr_text' => $text,
            
                'provider' => $data['provider']['value'],
                'document_number' => $data['document_number']['value'],
                'document_date' => $data['document_date']['value'],
            
                'subtotal' => $data['subtotal']['value'],
                'tax' => $data['tax']['value'],
                'total' => $data['total']['value'],
            
                'currency' => $data['currency']['value'],
                'category' => $data['category']['value'],
            
                'extraction_data' => $data,
            
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