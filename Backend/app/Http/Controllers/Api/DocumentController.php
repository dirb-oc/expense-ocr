<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Services\Documents\DocumentProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function index(Request $request): JsonResponse
    {
        $query = Document::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('document_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('document_date', '<=', $request->date_to);
        }

        $documents = $query
            ->latest('document_date')
            ->get();

        return response()->json([
            'data' => $documents,
        ]);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json([
            'data' => $document,
        ]);
    }

    public function update(
        UpdateDocumentRequest $request,
        Document $document
    ): JsonResponse {
        $document->update($request->validated());

        return response()->json([
            'message' => 'Documento actualizado correctamente.',
            'data' => $document->refresh(),
        ]);
    }

    public function destroy(Document $document): JsonResponse
    {
        \Storage::disk('local')->delete($document->file_path);

        $document->delete();

        return response()->json([
            'message' => 'Documento eliminado correctamente.',
        ]);
    }

    public function file(Document $document)
    {
        if (!Storage::disk('local')->exists($document->file_path)) {
            return response()->json([
                'message' => 'Archivo no encontrado.',
            ], 404);
        }
    
        return Storage::disk('local')->response(
            $document->file_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
            ]
        );
    }
}