@extends('layouts.app')
@section('title', 'Clínica Dental Mula — Tu salud bucodental en Mula, Murcia')

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<section style="background:linear-gradient(135deg,#1a3a5c 0%,#2563a8 60%,#0d9e6e 100%);color:#fff;padding:6rem 2rem;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"20\" cy=\"20\" r=\"40\" fill=\"rgba(255,255,255,.04)\"/><circle cx=\"80\" cy=\"80\" r=\"50\" fill=\"rgba(255,255,255,.03)\"/></svg>') no-repeat center/cover;"></div>
    <div style="position:relative;max-width:700px;margin:0 auto;">
        <div style="font-size:4rem;margin-bottom:1rem;">🦷</div>
        <h1 style="font-size:2.8rem;color:#fff;font-family:'Playfair Display',serif;margin-bottom:1rem;line-height:1.2;">
            Tu sonrisa, nuestro compromiso
        </h1>
        <p style="font-size:1.15rem;color:rgba(255,255,255,.85);max-width:540px;margin:0 auto 2.5rem;">
            Clínica dental de confianza en el corazón de Mula, Murcia.<br>
            Cuidamos tu salud bucodental con la mejor tecnología y atención personalizada.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('register') }}" style="background:#fff;color:var(--azul);padding:.9rem 2rem;border-radius:8px;font-weight:700;font-size:1rem;text-decoration:none;">
                📅 Pedir cita online
            </a>
            <a href="tel:968660000" style="background:rgba(255,255,255,.15);color:#fff;padding:.9rem 2rem;border-radius:8px;font-weight:600;font-size:1rem;text-decoration:none;border:2px solid rgba(255,255,255,.4);">
                📞 968 66 00 00
            </a>
        </div>
    </div>
</section>

{{-- ── SERVICIOS ─────────────────────────────────────────────────────────── --}}
<section style="padding:5rem 2rem;max-width:1100px;margin:0 auto;">
    <h2 style="text-align:center;font-size:2rem;margin-bottom:.5rem;">Nuestros servicios</h2>
    <p style="text-align:center;color:var(--texto-med);margin-bottom:3rem;">Tratamientos para toda la familia en un entorno cómodo y moderno.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem;">
        @foreach([
            ['🦷','Odontología general','Revisiones, empastes, extracciones y todo lo que tu boca necesita.'],
            ['✨','Blanqueamiento','Recupera el brillo natural de tu sonrisa con nuestros tratamientos.'],
            ['🔧','Ortodoncia','Brackets, alineadores invisibles y corrección de la mordida.'],
            ['🦴','Implantes dentales','Solución definitiva para dientes perdidos con implantes de titanio.'],
            ['👶','Odontopediatría','Cuidados especializados para los dientes de los más pequeños.'],
            ['😴','Cirugía oral','Extracción de muelas del juicio y otras intervenciones quirúrgicas.'],
        ] as [$icon, $title, $desc])
        <div class="card" style="text-align:center;transition:transform .2s,box-shadow .2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div style="font-size:2.5rem;margin-bottom:.75rem;">{{ $icon }}</div>
            <h3 style="font-size:1rem;margin-bottom:.5rem;">{{ $title }}</h3>
            <p style="font-size:.85rem;color:var(--texto-med);">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ── POR QUÉ ELEGIRNOS ─────────────────────────────────────────────────── --}}
<section style="background:#fff;padding:4rem 2rem;border-top:1px solid var(--gris-borde);border-bottom:1px solid var(--gris-borde);">
    <div style="max-width:900px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;">
        <div>
            <h2 style="font-size:2rem;margin-bottom:1rem;">¿Por qué elegirnos?</h2>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:.75rem;">
                @foreach([
                    '✅ Más de 15 años de experiencia en Mula',
                    '✅ Equipo de profesionales especializados',
                    '✅ Tecnología de última generación',
                    '✅ Presupuestos sin compromiso',
                    '✅ Cita online y app móvil para pacientes',
                    '✅ Financiación a medida',
                ] as $item)
                <li style="font-size:.95rem;color:var(--texto-med);">{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        <div style="background:var(--azul-claro);border-radius:16px;padding:2rem;text-align:center;">
            <div style="font-size:4rem;margin-bottom:1rem;">📱</div>
            <h3 style="margin-bottom:.5rem;">App para pacientes</h3>
            <p style="font-size:.9rem;color:var(--texto-med);margin-bottom:1.5rem;">
                Gestiona tus citas, consulta tu historial y recibe recordatorios directamente en tu móvil Android.
            </p>
            <div style="background:var(--azul);color:#fff;padding:.6rem 1.2rem;border-radius:8px;font-size:.85rem;font-weight:600;display:inline-block;">
                🤖 Próximamente en Google Play
            </div>
        </div>
    </div>
</section>

{{-- ── CONTACTO / UBICACIÓN ──────────────────────────────────────────────── --}}
<section style="padding:4rem 2rem;max-width:900px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start;">
    <div>
        <h2 style="font-size:2rem;margin-bottom:1rem;">Encuéntranos</h2>
        <div style="display:flex;flex-direction:column;gap:.75rem;color:var(--texto-med);font-size:.95rem;">
            <p>📍 <strong>Dirección:</strong> C/ Mayor, 1 — 30170 Mula, Murcia</p>
            <p>📞 <strong>Teléfono:</strong> <a href="tel:968660000">968 66 00 00</a></p>
            <p>✉️ <strong>Email:</strong> <a href="mailto:info@clinicadentalmula.es">info@clinicadentalmula.es</a></p>
        </div>
        <h3 style="margin:1.5rem 0 .5rem;font-size:1rem;">Horario</h3>
        <table style="font-size:.9rem;border-collapse:collapse;width:100%;">
            <tr><td style="padding:.3rem .5rem;color:var(--texto-med);">Lunes – Viernes</td><td style="padding:.3rem .5rem;font-weight:600;">9:00 – 14:00 / 16:00 – 20:00</td></tr>
            <tr><td style="padding:.3rem .5rem;color:var(--texto-med);">Sábado</td><td style="padding:.3rem .5rem;font-weight:600;">9:00 – 13:00</td></tr>
            <tr><td style="padding:.3rem .5rem;color:var(--texto-med);">Domingo</td><td style="padding:.3rem .5rem;color:#999;">Cerrado</td></tr>
        </table>
    </div>
    <div>
        <div style="background:var(--gris-fondo);border-radius:12px;height:250px;display:flex;align-items:center;justify-content:center;border:1px solid var(--gris-borde);">
            <div style="text-align:center;color:var(--texto-med);">
                <div style="font-size:3rem;margin-bottom:.5rem;">🗺️</div>
                <p style="font-size:.9rem;">Mapa de ubicación</p>
                <a href="https://maps.google.com/?q=Mula+Murcia" target="_blank" class="btn btn-outline btn-sm" style="margin-top:.75rem;">Ver en Google Maps</a>
            </div>
        </div>
        <div style="margin-top:1rem;text-align:center;">
            <a href="{{ route('register') }}" class="btn btn-success" style="width:100%;justify-content:center;">
                📅 Pedir cita online
            </a>
        </div>
    </div>
</section>

@endsection
