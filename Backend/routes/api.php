<?php

use App\Http\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

Route::post('/documents', [DocumentController::class, 'store']);
Route::put('/documents/{document}', [DocumentController::class, 'update']);

Route::get('/documents', [DocumentController::class, 'index']);
Route::get('/documents/{document}', [DocumentController::class, 'show']);
Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);