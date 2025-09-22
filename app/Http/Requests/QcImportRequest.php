<?php
// bagian ini yang ditambah

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QcImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'payload'   => ['required', 'string', 'min:3'],             // paste text
            'delimiter' => ['nullable', 'in:tab,comma,semicolon,space'],// pilihan delimiter
        ];
    }
}
