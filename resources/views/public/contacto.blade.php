@extends('layouts.app')
@section('title', 'Contacto — Clínica Dental Mula')

@section('content')
<section class="section-public" style="max-width:900px;">
    <h1 style="font-size:clamp(1.7rem,4.5vw,2.5rem);text-align:center;margin-bottom:.5rem;">Contacto</h1>
    <p style="text-align:center;color:var(--text-light);margin-bottom:3rem;">
        Estamos en el corazón de Mula. Escríbenos o llámanos.
    </p>

    <div class="grid-2col">
        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <h3 style="margin-bottom:1rem;">📍 Datos de contacto</h3>
                @foreach([
                    ['📍','Dirección','C/ Mayor, 1 — 30170 Mula, Murcia'],
                    ['📞','Teléfono','968 66 00 00'],
                    ['✉️','Email','info@clinicadentalmula.es'],
                ] as [$ico,$lbl,$val])
                <div style="display:flex;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--gray-200);font-size:.9rem;">
                    <span>{{ $ico }}</span>
                    <div><strong>{{ $lbl }}:</strong> {{ $val }}</div>
                </div>
                @endforeach
            </div>

            <div class="card">
                <h3 style="margin-bottom:1rem;">🕐 Horario de atención</h3>
                @foreach([
                    ['Lunes','9:00 – 14:00 / 16:00 – 20:00'],
                    ['Martes','9:00 – 14:00 / 16:00 – 20:00'],
                    ['Miércoles','9:00 – 14:00 / 16:00 – 20:00'],
                    ['Jueves','9:00 – 14:00 / 16:00 – 20:00'],
                    ['Viernes','9:00 – 14:00 / 16:00 – 20:00'],
                    ['Sábado','9:00 – 13:00'],
                    ['Domingo','Cerrado'],
                ] as [$dia,$hora])
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;
                            border-bottom:1px solid var(--gray-200);font-size:.88rem;">
                    <span style="color:var(--text-light);">{{ $dia }}</span>
                    <span style="font-weight:{{ $dia === 'Domingo' ? '400' : '600' }};
                                 color:{{ $dia === 'Domingo' ? '#999' : 'var(--text)' }};">{{ $hora }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="card">
                <h3 style="margin-bottom:1rem;">✉️ Envíanos un mensaje</h3>
                @if(session('contacto_ok'))
                <div class="alert alert-success">✅ Mensaje enviado. Te contactaremos pronto.</div>
                @endif
                <form method="POST" action="{{ route('contacto') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Tu nombre *</label>
                        <input class="form-control" type="text" name="nombre" required value="{{ old('nombre') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email o teléfono *</label>
                        <input class="form-control" type="text" name="contacto" required value="{{ old('contacto') }}"
                               placeholder="tu@email.com o 600 000 000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asunto</label>
                        <select class="form-control" name="asunto">
                            <option>Solicitar información</option>
                            <option>Pedir cita</option>
                            <option>Presupuesto</option>
                            <option>Urgencia dental</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mensaje *</label>
                        <textarea class="form-control" name="mensaje" rows="4" required>{{ old('mensaje') }}</textarea>
                    </div>
                    <div style="font-size:.78rem;color:var(--text-light);margin-bottom:1rem;">
                        🔒 Tus datos se usan solo para responderte. Ver nuestra
                        <a href="#">política de privacidad</a>.
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Enviar mensaje →
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
