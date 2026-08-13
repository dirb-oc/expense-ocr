<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'provider',
        'document_number',
        'document_date',
        'subtotal',
        'tax',
        'total',
        'currency',
        'category',
        'original_filename',
        'file_path',
        'mime_type',
        'ocr_text',
        'status',
    ];

    protected $casts = [
        'document_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];
}