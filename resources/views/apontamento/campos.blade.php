{{-- Campos compartilhados entre o cadastro e a edição do abastecimento --}}
<div class="row">
    <div class="form-group col-md-3">
        Data: <span class="text-danger">*</span>
        <input class="form-control" type="date" name="data" id="data" max="{{ date('Y-m-d') }}"
               value="{{ $apontamento ? $apontamento->data : date('Y-m-d') }}" required>
    </div>
    <div class="form-group col-md-4">
        Placa / Equipamento: <span class="text-danger">*</span>
        <select class="form-control limpar" name="placa" id="placa" required>
            <option value="">Selecione</option>
            @foreach ($placas as $placa)
                <option value="{{ $placa->id }}" {{ $apontamento && $placa->id == $apontamento->equipamento ? 'selected' : '' }}>
                    {{ $placa->placa }} - {{ $placa->equipamento }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-2">
        Total Litros: <span class="text-danger">*</span>
        <input class="form-control limpar" type="number" step="any" min="0.01" name="litros" id="litros"
               value="{{ $apontamento ? $apontamento->litros : '' }}" required>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-3">
        Km Atual:
        <input class="form-control limpar" type="number" step="any" min="0" name="km" id="km"
               value="{{ $apontamento ? $apontamento->km : '' }}">
        <small class="text-muted" id="infoUltimoKm"></small>
    </div>
    <div class="form-group col-md-3">
        Hora Atual:
        <input class="form-control limpar" type="number" step="any" min="0" name="horas" id="horas"
               value="{{ $apontamento ? $apontamento->horas : '' }}">
        <small class="text-muted" id="infoUltimaHora"></small>
    </div>
    <div class="form-group col-md-3">
        Combustível: <span class="text-danger">*</span>
        <select class="form-control limpar" name="combustivel" id="combustivel" required>
            <option value="">Selecione</option>
            @foreach ($combustiveis as $combustivel)
                <option value="{{ $combustivel }}" {{ $apontamento && $apontamento->combustivel == $combustivel ? 'selected' : '' }}>{{ $combustivel }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        Comboio:
        <select class="form-control limpar" name="origemAbastecimento" id="origemAbastecimento">
            <option value="">Selecione</option>
            @foreach ($comboios as $comboio)
                <option value="{{ $comboio }}" {{ $apontamento && $apontamento->origem == $comboio ? 'selected' : '' }}>{{ $comboio }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-9">
        Observação:
        <input class="form-control limpar" type="text" name="obs" id="obs" maxlength="50"
               value="{{ $apontamento ? $apontamento->obs : '' }}">
    </div>
</div>
<p class="text-muted"><small><span class="text-danger">*</span> Campos obrigatórios. Informe o Km atual e/ou a Hora atual.</small></p>
