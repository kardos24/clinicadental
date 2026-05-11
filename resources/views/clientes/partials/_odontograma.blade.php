{{-- Tab activo por defecto: nuevo (lámina completa) --}}
<div class="card" style="padding:0;border:none;background:transparent;">

    {{-- Tab buttons --}}
    <div class="odon-tabs">
        <button class="odon-tab odon-tab-active" id="odon-tab-nuevo"
                onclick="odonSwitchTab('nuevo')">
            Lámina completa
        </button>
        <button class="odon-tab" id="odon-tab-oclusal"
                onclick="odonSwitchTab('oclusal')">
            Oclusal
        </button>
        <button class="odon-tab" id="odon-tab-anatomico"
                onclick="odonSwitchTab('anatomico')">
            Anatómico
        </button>
    </div>

    {{-- Panel: lámina completa --}}
    <div id="odon-panel-nuevo">
        <x-cliente.odontograma.nuevo.index
            :cliente="$cliente"
            :dentadura="$dentadura" />
    </div>

    {{-- Panel: oclusal (existente sin cambios) --}}
    <div id="odon-panel-oclusal" style="display:none">
        <x-cliente.odontograma.oclusal.index
            :cliente="$cliente"
            :dentadura="$dentadura" />
    </div>

    {{-- Panel: anatómico (existente sin cambios) --}}
    <div id="odon-panel-anatomico" style="display:none">
        <x-cliente.odontograma.anatomico.index
            :dentadura="$dentadura"
            :modoEdicion="true" />
    </div>

</div>

@push('scripts')
<script>
function odonSwitchTab(tab) {
    ['nuevo','oclusal','anatomico'].forEach(function(t) {
        document.getElementById('odon-panel-' + t).style.display = (t === tab) ? '' : 'none';
        document.getElementById('odon-tab-' + t).classList.toggle('odon-tab-active', t === tab);
    });
}
</script>
@endpush
