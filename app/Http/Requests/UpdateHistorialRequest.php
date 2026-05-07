<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateHistorialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'dia'                   => ['required', 'integer', 'min:1', 'max:31'],
            'mes'                   => ['required', 'integer', 'min:1', 'max:12'],
            'anio'                  => ['required', 'integer', 'min:1900', 'max:2100'],
            'signo'                 => 'nullable|string|max:50',
            'diagnostico'           => 'required|string',
            'num_sesiones'          => 'nullable|integer|min:1',
            'importe'               => 'nullable|numeric|min:0',
            'tratamiento_realizado' => 'nullable|string',
            'recibo'                => 'nullable|string|max:50',
            'debe'                  => 'nullable|numeric|min:0',
            'haber'                 => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (!checkdate($this->mes, $this->dia, $this->anio)) {
                $v->errors()->add('dia', 'La fecha introducida no es válida.');
            }
        });
    }
}
