<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cliente_id'       => 'sometimes|required|exists:clientes,id',
            'fecha_hora'       => 'required|date|after:now',
            'duracion_minutos' => 'nullable|integer|min:15|max:240',
            'motivo'           => 'required|string|max:200',
            'notas'            => 'nullable|string',
        ];
    }
}
