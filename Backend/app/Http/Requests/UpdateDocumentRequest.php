<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'max:255'],

            'document_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'document_date' => [
                'nullable',
                'date',
            ],

            'subtotal' => [
                'required',
                'numeric',
                'min:0',
            ],

            'tax' => [
                'required',
                'numeric',
                'min:0',
            ],

            'total' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'category' => [
                'required',
                Rule::in([
                    'food',
                    'transport',
                    'technology',
                    'services',
                    'other',
                ]),
            ],
        ];
    }
}