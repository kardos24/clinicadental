@props(['cliente'])

<div class="card">
    <div class="card-header">
        <h3 class="card-title">👤 Datos personales</h3>
        <span style="font-family:monospace;font-size:.8rem;background:var(--primary);color:#fff;
              padding:.2rem .6rem;border-radius:4px;">{{ $cliente->num_filiacion ?? '—' }}</span>
    </div>
    @foreach([
        ['Apellidos',  $cliente->apellidos],
        ['Nombre',     $cliente->nombre],
        ['Edad',       $cliente->edad ? $cliente->edad . ' años' : '—'],
        ['Profesión',  $cliente->profesion ?? '—'],
        ['Dirección',  $cliente->direccion ?? '—'],
        ['CP',         $cliente->cp ?? '—'],
        ['Teléfono',   $cliente->telefono ?? '—'],
    ] as [$label, $val])
    <div style="display:flex;justify-content:space-between;padding:.5rem 0;
                border-bottom:1px solid var(--gray-200);font-size:.88rem;">
        <span style="color:var(--text-light);font-weight:600;">{{ $label }}</span>
        <span style="text-align:right;max-width:60%;">{{ $val }}</span>
    </div>
    @endforeach
    @if($cliente->observaciones)
    <div style="margin-top:1rem;">
        <p style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-bottom:.3rem;">Observaciones</p>
        <p style="font-size:.88rem;background:var(--gray-50);padding:.75rem;border-radius:6px;">
            {{ $cliente->observaciones }}
        </p>
    </div>
    @endif
</div>
