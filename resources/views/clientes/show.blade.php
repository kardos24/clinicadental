@extends('layouts.app')
@section('title', $cliente->nombre_completo . ' — Clínica Dental Mula')
@section('page-title', $cliente->nombre_completo)

@section('topbar-actions')
    @if(auth()->user()->isGestor())
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary btn-sm">✏️ Editar datos</a>
        <a href="{{ route('citas.calendario') }}" class="btn btn-outline btn-sm">📅 Calendario</a>
    @endif
@endsection

@section('content')
<div class="cliente-grid">
    <div class="cliente-sidebar">
        @include('clientes.partials._datos-personales', ['cliente' => $cliente])
    </div>
    <div class="cliente-main">
        @include('clientes.partials._odontograma', ['cliente' => $cliente, 'dentadura' => $dentadura])
        @include('clientes.partials._historial', ['cliente' => $cliente, 'historial' => $historial])
        @include('clientes.partials._citas', ['cliente' => $cliente, 'citas' => $citasFuturas])
    </div>
</div>
@include('clientes.partials._modales', ['cliente' => $cliente, 'dentadura' => $dentadura])
@endsection
