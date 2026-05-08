<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'apellidos'     => 'required|string|max:100',
            'nombre'        => 'required|string|max:100',
            'edad'          => 'nullable|integer|min:0|max:150',
            'profesion'     => 'nullable|string|max:100',
            'direccion'     => 'nullable|string|max:200',
            'cp'            => 'nullable|string|max:10',
            'telefono'      => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
        ];
    }
}
