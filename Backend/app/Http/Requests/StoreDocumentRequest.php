<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Debes seleccionar un documento.',
            'document.file' => 'El archivo enviado no es válido.',
            'document.mimes' => 'El documento debe ser JPG, PNG o PDF.',
            'document.max' => 'El documento no puede superar los 10 MB.',
        ];
    }
}