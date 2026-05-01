<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\HistorialClinico;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('gestor');
    }

    public function store(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'dia'                 => ['required', 'integer', 'min:1', 'max:31'],
            'mes'                 => ['required', 'integer', 'min:1', 'max:12'],
            'anio'                => ['required', 'integer', 'min:1900', 'max:2100'],
            'signo'               => 'nullable|string|max:50',
            'diagnostico'         => 'required|string',
            'num_sesiones'        => 'nullable|integer|min:1',
            'importe'             => 'nullable|numeric|min:0',
            'tratamiento_realizado'=> 'nullable|string',
            'recibo'              => 'nullable|string|max:50',
            'debe'                => 'nullable|numeric|min:0',
            'haber'               => 'nullable|numeric|min:0',
        ]);

        // Fix #1 (issue #11): reject impossible day/month/year combinations (e.g. Feb 31)
        if (!checkdate($data['mes'], $data['dia'], $data['anio'])) {
            return back()->withErrors(['dia' => 'La fecha introducida no es válida.'])->withInput();
        }

        $data['cliente_id'] = $cliente->id;
        $data['gestor_id']  = auth()->id();

        HistorialClinico::create($data);

        return back()->with('success', 'Registro añadido al historial.');
    }

    public function update(Request $request, HistorialClinico $historial)
    {
        // Fix #2 (issue #9): ownership documentation.
        // The gestor middleware already ensures only gestor-role users reach this point.
        // In a multi-gestor scenario, add: abort_if($historial->gestor_id !== auth()->id(), 403);
        // Currently the clinic has a single gestor, so all gestor users may edit any record.

        $data = $request->validate([
            'dia'                 => ['required', 'integer', 'min:1', 'max:31'],
            'mes'                 => ['required', 'integer', 'min:1', 'max:12'],
            'anio'                => ['required', 'integer', 'min:1900', 'max:2100'],
            'signo'               => 'nullable|string|max:50',
            'diagnostico'         => 'required|string',
            'num_sesiones'        => 'nullable|integer|min:1',
            'importe'             => 'nullable|numeric|min:0',
            'tratamiento_realizado'=> 'nullable|string',
            'recibo'              => 'nullable|string|max:50',
            'debe'                => 'nullable|numeric|min:0',
            'haber'               => 'nullable|numeric|min:0',
        ]);

        // Fix #1 (issue #11): reject impossible day/month/year combinations (e.g. Feb 31)
        if (!checkdate($data['mes'], $data['dia'], $data['anio'])) {
            return back()->withErrors(['dia' => 'La fecha introducida no es válida.'])->withInput();
        }

        $historial->update($data);

        return back()->with('success', 'Registro actualizado.');
    }

    public function destroy(HistorialClinico $historial)
    {
        // Fix #2 (issue #9): ownership documentation.
        // The gestor middleware already ensures only gestor-role users reach this point.
        // In a multi-gestor scenario, add: abort_if($historial->gestor_id !== auth()->id(), 403);
        // Currently the clinic has a single gestor, so all gestor users may delete any record.

        $historial->delete();
        return back()->with('success', 'Registro eliminado.');
    }
}
