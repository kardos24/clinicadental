<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:100',
            'contacto' => 'required|string|max:150',
            'asunto'   => 'nullable|string|max:100',
            'mensaje'  => 'required|string|max:2000',
        ];
    }
}
