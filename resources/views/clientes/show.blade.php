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
<div class="page-col">
    <div class="cliente-grid">
        <div class="cliente-sidebar">
            <x-cliente.datos-personales :cliente="$cliente" />
        </div>
        <div class="cliente-main">
            @include('clientes.partials._odontograma')
        </div>
    </div>
    <x-cliente.citas :cliente="$cliente" :citas="$citasFuturas" />
    <x-cliente.historial :cliente="$cliente" :historial="$historial" />
</div>
<x-cliente.modales :cliente="$cliente" />
@endsection
