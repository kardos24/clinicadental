<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCitaEstadoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:pendiente,confirmada,realizada,cancelada,no_presentado',
            'notas'  => 'nullable|string',
        ];
    }
}
