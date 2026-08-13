<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Services\Documents\DocumentProcessingService;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function store(
        StoreDocumentRequest $request,
        DocumentProcessingService $processor
    ): JsonResponse {
        $file = $request->file('document');

        $path = $file->store('documents');

        $document = Document::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
        ]);

        $document = $processor->process($document);

        return response()->json([
            'message' => 'Documento procesado correctamente.',
            'data' => $document,
        ], 201);
    }
}