// ══════════════════════════════════════════════════════════════════════════
// ODONTOGRAMA NUEVO — Lámina Anatómica Interactiva
// ══════════════════════════════════════════════════════════════════════════

const ODON_UP = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
const ODON_LO = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];

// Face states (13 — includes legacy obturacion/sellante/desgaste)
const ODON_FS = [
    {k:'sano',           l:'Sano',             c:'#faf5ea', b:'#c8b090'},
    {k:'caries',         l:'Caries',           c:'#ef4444'},
    {k:'caries_det',     l:'Caries detenida',  c:'#f59e0b'},
    {k:'composite',      l:'Composite',        c:'#3b82f6'},
    {k:'amalgama',       l:'Amalgama',         c:'#64748b'},
    {k:'sellante',       l:'Sellante',         c:'#06b6d4'},
    {k:'erosion',        l:'Erosión',          c:'#d97706'},
    {k:'fractura',       l:'Fractura',         c:'#991b1b'},
    {k:'tincion',        l:'Tinción',          c:'#92400e'},
    {k:'fisura',         l:'Fisura',           c:'#374151'},
    {k:'reconstruccion', l:'Reconstrucción',   c:'#059669'},
    {k:'desgaste',       l:'Desgaste',         c:'#78716c'},
    {k:'obturacion',     l:'Obturación',       c:'#f97316'},
];

// Piece states (27)
const ODON_PS = [
    {k:'presente',        l:'Presente',            c:'#faf5ea', b:'#c8b090'},
    {k:'ausente',         l:'Ausente',             c:'#d1d5db'},
    {k:'no_erupcionado',  l:'No erupcionado',      c:'#fef3c7'},
    {k:'extrac_indicada', l:'Extracción indicada', c:'#fca5a5'},
    {k:'extraido',        l:'Extraído',            c:'#9ca3af'},
    {k:'temporal',        l:'Temporal (deciduo)',  c:'#f9a8d4'},
    {k:'implante',        l:'Implante',            c:'#1d4ed8'},
    {k:'corona',          l:'Corona',              c:'#b45309'},
    {k:'puente',          l:'Puente',              c:'#3b82f6'},
    {k:'endodoncia',      l:'Endodoncia',          c:'#dc2626'},
    {k:'pulpitis',        l:'Pulpitis',            c:'#f97316'},
    {k:'necrosis',        l:'Necrosis pulpar',     c:'#1f2937'},
    {k:'apicectomia',     l:'Apicectomía',         c:'#0f766e'},
    {k:'incluido',        l:'Incluido/Retenido',   c:'#7c3aed'},
    {k:'supernumerario',  l:'Supernumerario',      c:'#db2777'},
    {k:'movilidad_1',     l:'Movilidad Grado I',   c:'#facc15'},
    {k:'movilidad_2',     l:'Movilidad Grado II',  c:'#ea580c'},
    {k:'movilidad_3',     l:'Movilidad Grado III', c:'#b91c1c'},
    {k:'carilla',         l:'Carilla',             c:'#93c5fd'},
    {k:'pilar_puente',    l:'Pilar de puente',     c:'#a78bfa'},
    {k:'pontico',         l:'Póntico de puente',   c:'#c4b5fd'},
    {k:'prot_removible',  l:'Prótesis removible',  c:'#fb923c'},
    {k:'giroversion',     l:'Giroversión',         c:'#84cc16'},
    {k:'migracion',       l:'Migración',           c:'#22d3ee'},
    {k:'diastema',        l:'Diastema',            c:'#e879f9'},
    {k:'fluorosis',       l:'Fluorosis',           c:'#a3e635'},
    {k:'agenesia',        l:'Agenesia',            c:'#94a3b8'},
];

const SK  = '#2a1a08';
const SKR = '#8b5830';
const SF  = '#faf5ea';
const RF  = '#e8d4a8';

let odonState = {};
let odonUI    = { tooth: null, face: null, mode: 'cara' };

function odonType(n) {
    if ([11,12,21,22,31,32,41,42].includes(n)) return 'inc';
    if ([13,23,33,43].includes(n))             return 'can';
    if ([14,15,24,25,34,35,44,45].includes(n)) return 'pre';
    if ([16,17,18,26,27,28].includes(n))       return 'mS';
    return 'mI';
}
function odonDistalRight(n) { return [1,4].includes(Math.floor(n/10)); }
function odonIsAbsent(n) {
    return ['ausente','extraido','agenesia','no_erupcionado'].includes(odonState[n]?.pieza);
}
function odonFaceFill(n, face) {
    const s = odonState[n];
    if (!s) return SF;
    if (s.pieza !== 'presente') return ODON_PS.find(x => x.k === s.pieza)?.c || '#d1d5db';
    return ODON_FS.find(x => x.k === s[face])?.c || SF;
}
function odonRootFill(n) {
    const s = odonState[n];
    if (!s) return RF;
    if (['ausente','extraido','agenesia'].includes(s.pieza)) return 'none';
    if (s.pieza !== 'presente') return ODON_PS.find(x => x.k === s.pieza)?.c || RF;
    return RF;
}

function odonSvgRoot(n) {
    const t = odonType(n), f = odonRootFill(n);
    const gone = ['ausente','extraido','agenesia'].includes(odonState[n]?.pieza);
    const ra = `fill="${f}" stroke="${SKR}" stroke-width=".9" stroke-linejoin="round"`;
    let p = '';
    if (!gone) {
        if (t === 'inc') {
            p = `<path d="M18,0C16,0 13,7 13,20L13,30 23,30 23,20C23,7 20,0 18,0Z" ${ra}/>`;
        } else if (t === 'can') {
            p = `<path d="M18,0C15,0 11,9 11,24L11,30 25,30 25,24C25,9 21,0 18,0Z" ${ra}/>`;
        } else if (t === 'pre') {
            p = `<path d="M11,0C9,0 7,6 7,18L7,30 16,30 16,18C16,6 13,0 11,0Z" ${ra}/>
                 <path d="M25,0C23,0 21,6 21,18L21,30 30,30 30,18C30,6 27,0 25,0Z" ${ra}/>`;
        } else if (t === 'mS') {
            p = `<path d="M9,3C7,3 5,9 5,20L5,30 15,30 15,20C15,9 11,3 9,3Z" ${ra}/>
                 <path d="M27,3C25,3 23,9 23,20L23,30 33,30 33,20C33,9 29,3 27,3Z" ${ra}/>
                 <path d="M18,0C16,0 14,6 14,17L14,27 22,27 22,17C22,6 20,0 18,0Z" ${ra}/>`;
        } else {
            p = `<path d="M10,0C8,0 6,7 6,20L6,30 17,30 17,20C17,7 13,0 10,0Z" ${ra}/>
                 <path d="M26,0C24,0 22,7 22,20L22,30 32,30 32,20C32,7 29,0 26,0Z" ${ra}/>`;
        }
    }
    const g = window.odontogramaNuevoGestor;
    const click = g ? `onclick="odonZoneClick(${n},'root',event)"` : '';
    return `<svg viewBox="0 0 36 30" width="36" height="30">
        <rect width="36" height="30" fill="transparent" class="odon-tz" ${click} title="Raíz — pieza entera"/>
        ${p}
    </svg>`;
}

function odonSvgCej() {
    return `<svg viewBox="0 0 36 4" width="36" height="4">
        <line x1="1" y1="2" x2="35" y2="2" stroke="${SKR}" stroke-width="1.4"/>
    </svg>`;
}

function odonSvgCrownV(n) {
    const t = odonType(n), f = odonFaceFill(n, 'V');
    const abs = odonIsAbsent(n), ex = odonState[n]?.pieza === 'extraido';
    let d = '', dec = '';
    if (t === 'inc') {
        d = 'M10,2L26,2C28,2 30,5 30,11L30,34C30,39 24,41 18,41C12,41 6,39 6,34L6,11C6,5 8,2 10,2Z';
        dec = `<ellipse cx="11" cy="3" rx="3" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>
               <ellipse cx="18" cy="2" rx="3" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>
               <ellipse cx="25" cy="3" rx="3" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>`;
    } else if (t === 'can') {
        d = 'M8,0L28,0C30,0 32,6 32,15L32,34C32,41 25,45 18,45C11,45 4,41 4,34L4,15C4,6 6,0 8,0Z';
        dec = `<path d="M10,0L18,-4L26,0" fill="${f}" stroke="${SK}" stroke-width=".6" stroke-linejoin="round" pointer-events="none"/>`;
    } else if (t === 'pre') {
        d = 'M6,1L30,1C32,1 33,4 33,9L33,30C33,37 27,40 18,40C9,40 3,37 3,30L3,9C3,4 4,1 6,1Z';
        dec = `<ellipse cx="12" cy="2" rx="5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>
               <ellipse cx="24" cy="2" rx="5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>
               <line x1="18" y1="5" x2="18" y2="36" stroke="${SK}" stroke-width=".6" opacity=".2" pointer-events="none"/>`;
    } else {
        d = 'M2,0L34,0C36,0 36,4 36,8L36,30C36,36 28,38 18,38C8,38 0,36 0,30L0,8C0,4 0,0 2,0Z';
        dec = `<ellipse cx="8" cy="2" rx="5.5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>
               <ellipse cx="18" cy="1" rx="4" ry="3" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>
               <ellipse cx="28" cy="2" rx="5.5" ry="3.5" fill="${f}" stroke="${SK}" stroke-width=".5" pointer-events="none"/>`;
    }
    const h = t === 'can' ? 45 : (t === 'inc' ? 41 : 40);
    const g = window.odontogramaNuevoGestor;
    const click = g ? `class="odon-tz" onclick="odonZoneClick(${n},'V',event)"` : '';
    if (abs) {
        return `<svg viewBox="0 0 36 ${h}" width="36" height="${h}">
            <path d="${d}" fill="none" stroke="#9ca3af" stroke-width=".8" stroke-dasharray="2,2"/>
            ${ex ? `<line x1="10" y1="8" x2="26" y2="${h-6}" stroke="#9ca3af" stroke-width="1.2"/>
                    <line x1="26" y1="8" x2="10" y2="${h-6}" stroke="#9ca3af" stroke-width="1.2"/>` : ''}
        </svg>`;
    }
    return `<svg viewBox="0 0 36 ${h}" width="36" height="${h}">
        ${dec}
        <path d="${d}" fill="${f}" stroke="${SK}" stroke-width="1.1" stroke-linejoin="round" ${click}/>
    </svg>`;
}

function odonSvgOcl(n) {
    const t = odonType(n);
    const hasO = (t === 'pre' || t === 'mS' || t === 'mI');
    const dr = odonDistalRight(n);
    const fV = odonFaceFill(n,'V'), fL = odonFaceFill(n,'L');
    const fM = odonFaceFill(n,'M'), fD = odonFaceFill(n,'D'), fO = odonFaceFill(n,'O');
    const fLft = dr ? fD : fM, fRgt = dr ? fM : fD;
    const lLft = dr ? 'D' : 'M', lRgt = dr ? 'M' : 'D';
    const lb = `font-size="5.5" fill="${SK}" opacity=".45" pointer-events="none" font-family="monospace" text-anchor="middle"`;
    const abs = odonIsAbsent(n);
    const g = window.odontogramaNuevoGestor;

    function poly(face, fill, pts) {
        const cl = g ? `class="odon-tz" onclick="odonZoneClick(${n},'${face}',event)"` : '';
        return `<polygon ${cl} points="${pts}" fill="${fill}" stroke="${SK}" stroke-width=".7"/>`;
    }
    function cRect(face, fill) {
        const cl = g ? `class="odon-tz" onclick="odonZoneClick(${n},'${face}',event)"` : '';
        return `<rect ${cl} x="8" y="8" width="20" height="12" fill="${fill}" stroke="${SK}" stroke-width=".7"/>`;
    }

    let inner = '';
    if (abs) {
        inner = `<rect x="0" y="0" width="36" height="28" fill="none" stroke="#9ca3af" stroke-width=".7" stroke-dasharray="2,2"/>`;
    } else if (hasO) {
        const fiss = t === 'mS'
            ? `<line x1="18" y1="14" x2="18" y2="8" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>
               <line x1="18" y1="14" x2="8" y2="22" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>
               <line x1="18" y1="14" x2="28" y2="22" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>`
            : `<line x1="18" y1="8" x2="18" y2="22" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>
               <line x1="8" y1="14" x2="28" y2="14" stroke="${SK}" stroke-width=".5" opacity=".3" pointer-events="none"/>`;
        inner = `
            ${poly('V',   fV,   '0,0 36,0 28,8 8,8')}
            ${poly(lLft,  fLft, '0,0 8,8 8,20 0,28')}
            ${poly(lRgt,  fRgt, '28,8 36,0 36,28 28,20')}
            ${poly('L',   fL,   '8,20 28,20 36,28 0,28')}
            ${cRect('O',  fO)}
            <text x="18" y="5.5" ${lb}>V</text>
            <text x="18" y="25.5" ${lb}>L</text>
            <text x="3.5" y="15.5" ${lb}>${lLft}</text>
            <text x="32.5" y="15.5" ${lb}>${lRgt}</text>
            <text x="18" y="15.5" ${lb}>O</text>
            ${fiss}`;
    } else {
        const cl4 = g ? `class="odon-tz" onclick="odonZoneClick(${n},'O',event)"` : '';
        inner = `
            ${poly('V',   fV,   '0,0 36,0 28,9 8,9')}
            ${poly(lLft,  fLft, '0,0 8,9 8,19 0,28')}
            ${poly(lRgt,  fRgt, '28,9 36,0 36,28 28,19')}
            ${poly('L',   fL,   '8,19 28,19 36,28 0,28')}
            <rect ${cl4} x="8" y="9" width="20" height="10" fill="${fO}" stroke="${SK}" stroke-width=".6" opacity=".8"/>
            <text x="18" y="5.5" ${lb}>V</text>
            <text x="18" y="26" ${lb}>L</text>
            <text x="3.5" y="15" ${lb}>${lLft}</text>
            <text x="32.5" y="15" ${lb}>${lRgt}</text>
            <text x="18" y="15" ${lb}>I</text>`;
    }
    return `<svg viewBox="0 0 36 28" width="36" height="28">
        ${inner}
        <rect x="0" y="0" width="36" height="28" fill="none" stroke="${SK}" stroke-width=".8" pointer-events="none"/>
    </svg>`;
}

function odonSvgCrownL(n) {
    const t = odonType(n), f = odonFaceFill(n, 'L');
    const abs = odonIsAbsent(n);
    let d = '', dec = '';
    if (t === 'inc') {
        d = 'M11,0L25,0C27,0 28,3 28,8L28,22C28,26 23,28 18,28C13,28 8,26 8,22L8,8C8,3 9,0 11,0Z';
        dec = `<ellipse cx="18" cy="24" rx="5" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" opacity=".5" pointer-events="none"/>`;
    } else if (t === 'can') {
        d = 'M10,0L26,0C28,0 30,4 30,11L30,26C30,31 24,33 18,33C12,33 6,31 6,26L6,11C6,4 8,0 10,0Z';
        dec = `<ellipse cx="18" cy="29" rx="5" ry="2.5" fill="${f}" stroke="${SK}" stroke-width=".5" opacity=".5" pointer-events="none"/>`;
    } else if (t === 'pre') {
        d = 'M8,0L28,0C30,0 30,3 30,7L30,22C30,27 24,29 18,29C12,29 6,27 6,22L6,7C6,3 6,0 8,0Z';
    } else {
        d = 'M4,0L32,0C34,0 34,3 34,7L34,22C34,27 27,29 18,29C9,29 2,27 2,22L2,7C2,3 2,0 4,0Z';
    }
    const h = t === 'can' ? 33 : 29;
    const g = window.odontogramaNuevoGestor;
    const click = g ? `class="odon-tz" onclick="odonZoneClick(${n},'L',event)"` : '';
    return `<svg viewBox="0 0 36 ${h}" width="36" height="${h}">
        ${abs
            ? `<path d="${d}" fill="none" stroke="#9ca3af" stroke-width=".7" stroke-dasharray="1.5,2"/>`
            : `${dec}<path d="${d}" fill="${f}" stroke="${SK}" stroke-width=".9" stroke-linejoin="round" ${click}/>`
        }
    </svg>`;
}

function odonRenderTooth(n, arch) {
    const up = (arch === 'upper');
    const parts = up
        ? [odonSvgRoot(n), odonSvgCej(), odonSvgCrownV(n), odonSvgOcl(n), odonSvgCrownL(n)]
        : [odonSvgCrownL(n), odonSvgOcl(n), odonSvgCrownV(n), odonSvgCej(), odonSvgRoot(n)];
    const fdi = `<div class="odon-nuevo-fdi">${n}</div>`;
    const sel = (odonUI.tooth === n) ? ' odon-sel' : '';
    const click = window.odontogramaNuevoGestor
        ? `onclick="odonSelectTooth(${n},event)"` : '';
    return `<div class="odon-nuevo-tc${sel}" id="odon-tc-${n}" ${click}>
        ${up ? fdi : ''}${parts.join('')}${up ? '' : fdi}
    </div>`;
}

function odonRenderBoard() {
    const upper = ODON_UP.map((n, i) =>
        (i === 8 ? '<div class="odon-nuevo-qsep"></div>' : '') + odonRenderTooth(n, 'upper')
    ).join('');
    const lower = ODON_LO.map((n, i) =>
        (i === 8 ? '<div class="odon-nuevo-qsep"></div>' : '') + odonRenderTooth(n, 'lower')
    ).join('');
    document.getElementById('odon-nuevo-board').innerHTML = `
        <div class="odon-nuevo-arch-lbl">◀ Arcada Superior — Cuadrante 1 · 2 ▶</div>
        <div class="odon-nuevo-row upper">${upper}</div>
        <div class="odon-nuevo-divider"></div>
        <div class="odon-nuevo-row lower">${lower}</div>
        <div class="odon-nuevo-arch-lbl">◀ Arcada Inferior — Cuadrante 4 · 3 ▶</div>`;
}

function odonRedrawTooth(n) {
    const el = document.getElementById(`odon-tc-${n}`);
    if (!el) return;
    const arch = ODON_UP.includes(n) ? 'upper' : 'lower';
    const tmp = document.createElement('div');
    tmp.innerHTML = odonRenderTooth(n, arch);
    el.replaceWith(tmp.firstElementChild);
}
