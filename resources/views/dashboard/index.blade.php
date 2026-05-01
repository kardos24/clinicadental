@extends('layouts.app')
@section('title', 'Dashboard — Clínica Dental Mula')
@section('page-title', 'Dashboard')

@section('content')
<div class="dashboard-stats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
    @foreach([
        ['👥','Total pacientes',   $stats['total_clientes'],   '#2563eb'],
        ['📅','Citas hoy',         $stats['citas_hoy'],        '#059669'],
        ['⏳','Pendientes',         $stats['citas_pendientes'], '#f59e0b'],
        ['💶','Ingresos este mes',  '€ '.number_format($stats['ingresos_mes'],2), '#1e3a8a'],
    ] as [$ico,$lbl,$val,$color])
    <div class="card card-primary" style="text-align:center;border-top:4px solid {{ $color }}">
        <div style="font-size:2rem;margin-bottom:.5rem">{{ $ico }}</div>
        <div style="font-size:1.8rem;font-weight:700;color:{{ $color }};margin-bottom:.25rem">{{ $val }}</div>
        <div style="font-size:.8125rem;color:var(--texto-med);font-weight:500">{{ $lbl }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">📅 Citas de hoy</h3>
        </div>
        @forelse($stats['citas_hoy_lista'] as $cita)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 0;border-bottom:1px solid var(--gris-borde);">
            <div style="background:#2563eb;color:#fff;width:48px;height:48px;border-radius:8px;
                        display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.8125rem;flex-shrink:0;">
                {{ $cita->fecha_hora->format('H:i') }}
            </div>
            <div style="flex:1;">
                <div style="font-weight:600;font-size:.9375rem;">{{ $cita->cliente->nombre_completo }}</div>
                <div style="font-size:.8125rem;color:var(--texto-med);">{{ $cita->motivo }}</div>
            </div>
            <span class="badge badge-{{ $cita->estado }}">{{ $cita->estado_label }}</span>
        </div>
        @empty
        <p style="text-align:center;color:var(--texto-med);padding:1.5rem;font-size:.875rem;">Sin citas para hoy</p>
        @endforelse
        <a href="{{ route('citas.calendario') }}" class="btn btn-outline btn-sm" style="margin-top:1rem;">Ver calendario completo</a>
    </div>

    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">🆕 Últimos pacientes</h3>
        </div>
        @foreach($stats['ultimos_clientes'] as $cliente)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gris-borde);">
            <div>
                <div style="font-weight:600;font-size:.9375rem;">{{ $cliente->nombre_completo }}</div>
                <div style="font-size:.78125rem;color:var(--texto-med);">{{ $cliente->num_filiacion }}</div>
            </div>
            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline btn-sm">Ver</a>
        </div>
        @endforeach
        <a href="{{ route('clientes.index') }}" class="btn btn-outline btn-sm" style="margin-top:1rem;">Ver todos los pacientes</a>
    </div>
</div>
@endsection
