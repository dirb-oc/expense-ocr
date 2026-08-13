<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $file = $request->file('document');

        $path = $file->store('documents');

        $document = Document::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Documento cargado correctamente.',
            'data' => $document,
        ], 201);
    }
}