@extends('layouts.app')
@section('title', 'Servicios — Clínica Dental Mula')

@section('content')
<section class="section-public">
    <h1 style="font-size:clamp(1.7rem,4.5vw,2.5rem);text-align:center;margin-bottom:.5rem;">Nuestros tratamientos</h1>
    <p style="text-align:center;color:var(--text-light);margin-bottom:3rem;font-size:1.05rem;">
        Ofrecemos una atención integral para toda la familia en Mula, Murcia.
    </p>

    @foreach([
        ['🦷','Odontología general','Revisiones periódicas, empastes de composite y amalgama, tratamientos de conducto (endodoncia), extracciones simples y complejas. La base de una buena salud bucal es la prevención.','desde 30€'],
        ['✨','Blanqueamiento dental','Blanqueamiento profesional en clínica con láser o en casa con férulas personalizadas. Recupera el brillo y la blancura natural de tu sonrisa de forma segura.','desde 150€'],
        ['🔧','Ortodoncia','Brackets metálicos y estéticos, alineadores invisibles (Invisalign y similares), ortodoncia lingual y aparatos removibles para niños. Corregimos cualquier problema de mordida o alineación.','desde 800€'],
        ['🦴','Implantes dentales','Implantes de titanio de la más alta calidad. Solución definitiva y natural para dientes perdidos. Incluye la corona de porcelana. Financiación disponible.','desde 750€ por implante'],
        ['👶','Odontopediatría','Especialistas en odontología infantil. Selladores de fisuras, fluoraciones preventivas, extracciones de dientes de leche y seguimiento del desarrollo dental de los más pequeños.','desde 25€'],
        ['😴','Cirugía oral','Extracción de muelas del juicio (cordales), cirugía preprotésica, frenectomías y otras intervenciones quirúrgicas menores. Realizadas con anestesia local y total seguridad.','Consultar'],
        ['🦷','Prótesis y coronas','Prótesis fija (coronas y puentes), prótesis removible (esquelética, acrílica), prótesis sobre implantes. Materiales de alta estética: zirconio, porcelana, resina.','Consultar'],
        ['🧹','Higiene y profilaxis','Limpieza dental profesional, tartrectomía (eliminación de sarro), tratamiento de la gingivitis y periodontitis. Recomendamos una limpieza cada 6-12 meses.','desde 50€'],
    ] as [$ico, $titulo, $desc, $precio])
    <div class="card" style="display:flex;gap:1.5rem;margin-bottom:1rem;align-items:start;">
        <div style="font-size:2.5rem;flex-shrink:0;width:56px;text-align:center;">{{ $ico }}</div>
        <div style="flex:1;">
            <h2 style="font-size:1.2rem;margin-bottom:.4rem;">{{ $titulo }}</h2>
            <p style="color:var(--text-light);font-size:.9rem;margin-bottom:.5rem;">{{ $desc }}</p>
            <span style="background:var(--success-light);color:var(--success);font-size:.82rem;font-weight:700;
                         padding:.2rem .7rem;border-radius:20px;">💶 {{ $precio }}</span>
        </div>
    </div>
    @endforeach

    <div class="cta-box">
        <h2 style="color:#fff;margin-bottom:.75rem;">¿Tienes dudas sobre algún tratamiento?</h2>
        <p style="color:rgba(255,255,255,.8);margin-bottom:1.5rem;">
            Llámanos o pide cita y te asesoramos sin compromiso. El primer diagnóstico es gratuito.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="tel:968660000" style="background:#fff;color:var(--primary);padding:.75rem 1.5rem;border-radius:8px;font-weight:700;text-decoration:none;">
                📞 968 66 00 00
            </a>
            <a href="{{ route('register') }}" style="background:var(--success);color:#fff;padding:.75rem 1.5rem;border-radius:8px;font-weight:700;text-decoration:none;">
                📅 Pedir cita online
            </a>
        </div>
    </div>
</section>
@endsection
