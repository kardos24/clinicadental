'use strict';

var dienteActual = null;

function abrirModalDiente(num, caraResaltar) {
    dienteActual = num;
    var d = window.dentaduraData[String(num)] || {};

    document.getElementById('diente-num-label').textContent = num;
    document.getElementById('diente-pieza').value      = d.estado_pieza    || 'presente';
    document.getElementById('diente-vestibular').value = d.cara_vestibular || 'sano';
    document.getElementById('diente-lingual').value    = d.cara_lingual    || 'sano';
    document.getElementById('diente-mesial').value     = d.cara_mesial     || 'sano';
    document.getElementById('diente-distal').value     = d.cara_distal     || 'sano';
    document.getElementById('diente-oclusal').value    = d.cara_oclusal    || 'sano';
    document.getElementById('diente-notas').value      = d.notas           || '';

    var oclusalField  = document.getElementById('campo-oclusal');
    var oclusalSelect = document.getElementById('diente-oclusal');
    if (d.tiene_oclusal) {
        oclusalField.style.display = '';
        oclusalSelect.disabled = false;
    } else {
        oclusalField.style.display = 'none';
        oclusalSelect.disabled = true;
        oclusalSelect.value = 'sano';
    }

    toggleCaras();

    document.querySelectorAll('.cara-field').forEach(function (f) {
        f.classList.remove('cara-highlight');
    });

    document.getElementById('modal-diente').classList.add('open');

    if (caraResaltar) {
        setTimeout(function () {
            var campo = document.getElementById('diente-' + caraResaltar);
            if (campo && !campo.disabled) {
                var field = campo.closest('.cara-field');
                if (field) {
                    field.classList.add('cara-highlight');
                    campo.focus();
                    setTimeout(function () { field.classList.remove('cara-highlight'); }, 2500);
                }
            }
        }, 60);
    }
}

function abrirModalCaraDiente(num, cara) {
    abrirModalDiente(num, cara);
}

function toggleCaras() {
    var pieza   = document.getElementById('diente-pieza').value;
    var section = document.getElementById('caras-section');
    if (pieza !== 'presente') {
        section.classList.add('caras-disabled');
    } else {
        section.classList.remove('caras-disabled');
    }
}

function abrirModalCita() {
    document.getElementById('modal-cita').classList.add('open');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}

function guardarDiente() {
    var payload = {
        num_diente:      String(dienteActual),
        estado_pieza:    document.getElementById('diente-pieza').value,
        cara_vestibular: document.getElementById('diente-vestibular').value,
        cara_lingual:    document.getElementById('diente-lingual').value,
        cara_mesial:     document.getElementById('diente-mesial').value,
        cara_distal:     document.getElementById('diente-distal').value,
        cara_oclusal:    document.getElementById('diente-oclusal').value,
        notas:           document.getElementById('diente-notas').value,
    };

    fetch(window.dentaduraUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ dientes: [payload] }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) { cerrarModal('modal-diente'); location.reload(); }
        else { alert(data.message || 'Error al guardar.'); }
    })
    .catch(function () { alert('Error al guardar. Inténtalo de nuevo.'); });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal-backdrop').forEach(function (b) {
        b.addEventListener('click', function (e) {
            if (e.target === b) b.classList.remove('open');
        });
    });
});
