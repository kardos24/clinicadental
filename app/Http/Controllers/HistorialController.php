<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHistorialRequest;
use App\Http\Requests\UpdateHistorialRequest;
use App\Models\Cliente;
use App\Models\HistorialClinico;

class HistorialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('gestor');
    }

    public function store(StoreHistorialRequest $request, Cliente $cliente)
    {
        $data = $request->validated();
        $data['cliente_id'] = $cliente->id;
        $data['gestor_id']  = auth()->id();
        HistorialClinico::create($data);
        return back()->with('success', 'Registro añadido al historial.');
    }

    public function update(UpdateHistorialRequest $request, HistorialClinico $historial)
    {
        $historial->update($request->validated());
        return back()->with('success', 'Registro actualizado.');
    }

    public function destroy(HistorialClinico $historial)
    {
        $historial->delete();
        return back()->with('success', 'Registro eliminado.');
    }
}
