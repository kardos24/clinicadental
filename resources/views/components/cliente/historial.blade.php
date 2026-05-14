@props(['cliente', 'historial'])

<div class="card" style="padding:0;">
    <div class="card-header" style="padding:1.25rem 1.5rem;">
        <h3 class="card-title">📋 Historial clínico</h3>
        @if(auth()->user()->isGestor())
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-historial').classList.add('open')">
            + Añadir registro
        </button>
        @endif
    </div>

    {{-- Resumen financiero --}}
    @php
        $totalDebe  = $historial->sum('debe');
        $totalHaber = $historial->sum('haber');
        $saldo      = $totalDebe - $totalHaber;
    @endphp
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid var(--gray-200);">
        @foreach([['Debe total','€ ' . number_format($totalDebe,2),'var(--danger)'],['Haber total','€ ' . number_format($totalHaber,2),'var(--success)'],['Saldo','€ ' . number_format($saldo,2),$saldo > 0 ? 'var(--danger)' : 'var(--success)']] as [$l,$v,$c])
        <div style="padding:1rem;text-align:center;border-right:1px solid var(--gray-200);">
            <div style="font-size:.75rem;color:var(--text-light);font-weight:600;">{{ $l }}</div>
            <div style="font-size:1.1rem;font-weight:700;color:{{ $c }};">{{ $v }}</div>
        </div>
        @endforeach
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th><th>Signo</th><th>Diagnóstico</th>
                    <th>Ses.</th><th>Importe</th><th>Debe</th><th>Haber</th><th>Saldo</th>
                    @if(auth()->user()->isGestor())<th>Acc.</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($historial as $reg)
                <tr>
                    <td style="white-space:nowrap;font-size:.82rem;">{{ $reg->fecha_formateada }}</td>
                    <td><span style="font-family:monospace;font-size:.82rem;">{{ $reg->signo ?? '—' }}</span></td>
                    <td style="max-width:200px;font-size:.85rem;">{{ Str::limit($reg->diagnostico, 60) }}</td>
                    <td style="text-align:center;">{{ $reg->num_sesiones }}</td>
                    <td style="text-align:right;font-size:.85rem;">{{ number_format($reg->importe,2) }}€</td>
                    <td style="text-align:right;font-size:.85rem;color:var(--danger);">{{ number_format($reg->debe,2) }}€</td>
                    <td style="text-align:right;font-size:.85rem;color:var(--success);">{{ number_format($reg->haber,2) }}€</td>
                    <td style="text-align:right;font-size:.85rem;font-weight:600;color:{{ $reg->saldo > 0 ? 'var(--danger)' : 'var(--success)' }};">
                        {{ number_format($reg->saldo,2) }}€
                    </td>
                    @if(auth()->user()->isGestor())
                    <td>
                        <form method="POST" action="{{ route('historial.destroy', $reg) }}"
                              onsubmit="return confirm('¿Eliminar este registro?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-light);">Sin registros de historial.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($historial->hasPages())
    <div style="padding:1rem 1.5rem;">{{ $historial->links() }}</div>
    @endif
</div>
