{{--
    Formulário da Informação Diária (inclusão e alteração).
    Variáveis: $informacao (null na inclusão), $route, $type e as listas dos selects.
    Os id/name dos campos são lidos pelo custom.js (form#cadastro-informacao): não renomear.
--}}
@php
    $informacao = $informacao ?? null;
    $v   = fn($campo) => $informacao ? $informacao->$campo : null;
    $sel = fn($campo, $id) => $informacao && $informacao->$campo == $id ? 'selected' : '';

    $cargas = [
        'julieta' => 'Julieta',
        'truck'   => 'Truck',
        'bitrem'  => 'Bitrem',
    ];
    $distancias = [
        'curto' => 'Curto · 0–100 m',
        'medio' => 'Médio · 100–300 m',
        'longo' => 'Longo · 300 m+',
    ];
@endphp

@include('partials.estilo-formulario')

<form action="" id="cadastro-informacao" nome="cadastro-informacao" method="post" class="info-form">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="informacao">

    <div class="info-topo">
        <div>
            <small>Apontamento · {{ $informacao ? 'Alteração' : 'Novo lançamento' }}</small>
            <h3><i class="fas fa-tree"></i> Informação Diária</h3>
        </div>
        <span class="info-obrig">Campos com <b>*</b> são obrigatórios</span>
    </div>

    {{-- 1. Identificação --}}
    <section class="info-card">
        <div class="info-card-topo"><h4>1. Identificação</h4></div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="data">Data <b>*</b></label>
                <input class="form-control" type="date" name="data" id="data" value="{{ $v('data') ?? date('Y-m-d') }}">
            </div>
            <div class="info-campo"><label for="equipamento">Veículo / Equipamento <b>*</b></label>
                <select class="form-control limpar" name="equipamento" id="equipamento">
                    <option value="">Selecione</option>
                    @foreach ($equipamentos as $equipamento)
                        <option value="{{ $equipamento->id }}" {{ $sel('equipamento', $equipamento->id) }}>{{ $equipamento->placa }} - {{ $equipamento->equipamento }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="colaborador">Colaborador</label>
                <select class="form-control limpar" name="colaborador" id="colaborador">
                    <option value="0">Selecione</option>
                    @foreach ($colaboradores as $colaborador)
                        <option value="{{ $colaborador->id }}" {{ $sel('colaborador', $colaborador->id) }}>{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="fazenda">Fazenda</label>
                <select class="form-control limpar" name="fazenda" id="fazenda">
                    <option value="0">Selecione</option>
                    @foreach ($fazendas as $fazenda)
                        <option value="{{ $fazenda->id }}" {{ $sel('fazenda', $fazenda->id) }}>{{ $fazenda->fazenda }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="atividade">Atividade</label>
                <select class="form-control limpar" name="atividade" id="atividade">
                    <option value="0">Selecione</option>
                    @foreach ($atividades as $atividade)
                        <option value="{{ $atividade->id }}" {{ $sel('atividade', $atividade->id) }}>{{ $atividade->atividade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="corte">Tipo do corte</label>
                <select class="form-control limpar" name="corte" id="corte">
                    <option value="0">Selecione</option>
                    @foreach ($cortes as $corte)
                        <option value="{{ $corte->id }}" {{ $sel('corte', $corte->id) }}>{{ $corte->corte }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="terreno">Tipo do terreno</label>
                <select class="form-control limpar" name="terreno" id="terreno">
                    <option value="0">Selecione</option>
                    @foreach ($terrenos as $terreno)
                        <option value="{{ $terreno->id }}" {{ $sel('terreno', $terreno->id) }}>{{ $terreno->terreno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="clima">Clima</label>
                <select class="form-control limpar" name="clima" id="clima">
                    <option value="0">Selecione</option>
                    @foreach ($climas as $clima)
                        <option value="{{ $clima->id }}" {{ $sel('clima', $clima->id) }}>{{ $clima->clima }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- 2. Jornada --}}
    <section class="info-card">
        <div class="info-card-topo">
            <h4>2. Jornada</h4>
            <span class="info-horas" id="horasTrabalhadas" hidden></span>
        </div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="hora_inicial">Hora inicial</label>
                <input class="form-control" type="time" name="hora_inicial" id="hora_inicial" value="{{ $v('hora_inicial') }}">
            </div>
            <div class="info-campo"><label for="hora_final">Hora final</label>
                <input class="form-control" type="time" name="hora_final" id="hora_final" value="{{ $v('hora_final') }}">
            </div>
            <div class="info-campo"><label for="horimetro_inicial">Horímetro inicial</label>
                <input class="form-control" type="number" step="any" name="horimetro_inicial" id="horimetro_inicial" value="{{ $v('horimetro_inicial') }}">
            </div>
            <div class="info-campo"><label for="horimetro_final">Horímetro final</label>
                <input class="form-control" type="number" step="any" name="horimetro_final" id="horimetro_final" value="{{ $v('horimetro_final') }}">
            </div>
        </div>
    </section>

    {{-- 3. Produção --}}
    <section class="info-card">
        <div class="info-card-topo"><h4>3. Produção</h4></div>
        <div class="info-grid info-g3">
            <div class="info-campo"><label for="fat_m">Faturamento (m²)</label>
                <input class="form-control" type="number" step="any" name="fat_m" id="fat_m" value="{{ $v('fat_m') }}">
            </div>
            <div class="info-campo"><label for="producao_terceiros">Produção de terceiros (m³)</label>
                <input class="form-control" type="number" step="any" name="producao_terceiros" id="producao_terceiros" value="{{ $v('producao_terceiros') }}">
            </div>
            <div class="info-campo"><label for="comprimento_madeira">Comprimento da madeira (m)</label>
                <input class="form-control" type="number" step="any" name="comprimento_madeira" id="comprimento_madeira" value="{{ $v('comprimento_madeira') }}">
            </div>
        </div>
        <div class="info-grid info-g2">
            <div class="info-quadro">
                <h5>Carregamento e descarregamento</h5>
                <table>
                    <thead><tr><th></th><th>Carreg.</th><th>Descarreg.</th></tr></thead>
                    <tbody>
                        @foreach ($cargas as $campo => $nome)
                            <tr>
                                <td>{{ $nome }}</td>
                                <td><input class="form-control" type="number" step="any" name="carg_{{ $campo }}" id="carg_{{ $campo }}" value="{{ $v('carg_'.$campo) }}" aria-label="Carregamento {{ $nome }}"></td>
                                <td><input class="form-control" type="number" step="any" name="descarg_{{ $campo }}" id="descarg_{{ $campo }}" value="{{ $v('descarg_'.$campo) }}" aria-label="Descarregamento {{ $nome }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="info-quadro">
                <h5>Baldeio e arrasto por distância</h5>
                <table>
                    <thead><tr><th></th><th>Baldeio</th><th>Arrasto</th></tr></thead>
                    <tbody>
                        @foreach ($distancias as $campo => $nome)
                            <tr>
                                <td>{{ $nome }}</td>
                                <td><input class="form-control" type="number" step="any" name="baldeio_{{ $campo }}" id="baldeio_{{ $campo }}" value="{{ $v('baldeio_'.$campo) }}" aria-label="Baldeio {{ $nome }}"></td>
                                <td><input class="form-control" type="number" step="any" name="arrasto_{{ $campo }}" id="arrasto_{{ $campo }}" value="{{ $v('arrasto_'.$campo) }}" aria-label="Arrasto {{ $nome }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- 4. Abastecimento e lubrificação --}}
    <section class="info-card">
        <div class="info-card-topo"><h4>4. Abastecimento e lubrificação</h4></div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="origem_abastecimento">Tanque de origem</label>
                <select class="form-control limpar" name="origem_abastecimento" id="origem_abastecimento">
                    <option value="0">Selecione</option>
                    @foreach ($comboios as $comboio)
                        <option value="{{ $comboio->id }}" {{ $sel('origem_abastecimento', $comboio->id) }}>{{ $comboio->tanque }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="nr_nf">Número NF</label>
                <input class="form-control" type="number" step="any" name="nr_nf" id="nr_nf" value="{{ $v('nr_nf') }}">
            </div>
            <div class="info-campo"><label for="qnt_diesel">Total diesel (litros)</label>
                <input class="form-control" type="number" step="any" name="qnt_diesel" id="qnt_diesel" value="{{ $v('qnt_diesel') }}">
            </div>
            <div class="info-campo"><label for="horimetro_abastecimento">Horímetro abastecimento</label>
                <input class="form-control" type="number" step="any" name="horimetro_abastecimento" id="horimetro_abastecimento" value="{{ $v('horimetro_abastecimento') }}">
            </div>
        </div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="relogio_tanque_inicial">Horímetro tanque inicial</label>
                <input class="form-control" type="number" step="any" name="relogio_tanque_inicial" id="relogio_tanque_inicial" value="{{ $v('relogio_tanque_inicial') }}">
            </div>
            <div class="info-campo"><label for="relogio_tanque_final">Horímetro tanque final</label>
                <input class="form-control" type="number" step="any" name="relogio_tanque_final" id="relogio_tanque_final" value="{{ $v('relogio_tanque_final') }}">
            </div>
            <div class="info-campo"><label for="tipo_lubrificante">Lubrificante</label>
                <select class="form-control limpar" name="tipo_lubrificante" id="tipo_lubrificante">
                    <option value="0">Selecione</option>
                    @foreach ($lubrificantes as $lubrificante)
                        <option value="{{ $lubrificante->id }}" {{ $sel('tipo_lubrificante', $lubrificante->id) }}>{{ $lubrificante->lubrificante }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="qnt_lubrificante">Quantidade lubrificante</label>
                <input class="form-control" type="number" step="any" name="qnt_lubrificante" id="qnt_lubrificante" value="{{ $v('qnt_lubrificante') }}">
            </div>
        </div>
    </section>

    {{-- 5. Observação --}}
    <section class="info-card">
        <div class="info-card-topo"><h4>5. Observação</h4></div>
        <label class="sr-only" for="obs">Observação</label>
        <input class="form-control" type="text" maxlength="255" name="obs" id="obs" value="{{ $v('obs') }}" placeholder="Ocorrências, paradas, detalhes do dia…">
    </section>

    {{-- Rodapé fixo com o resumo e os botões --}}
    <div class="info-rodape">
        <span id="resumoLancamento"></span>
        <div>
            <button type="button" name="sair" id="sair" value="" class="btn btn-outline-danger">
                <span class="fa fa-door-open"></span> Sair
            </button>
            <button type="submit" name="salvar" value="" id="salvar" class="btn btn-success">
                <span class="fas fa-save"></span> Salvar
            </button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function () {

        $('#sair').on('click', function () {
            window.location.href = "{{ route('informacao.listAll') }}";
        });

        // Horas trabalhadas (horímetro final - inicial) e resumo do rodapé
        function atualizaResumo() {
            var inicial = parseFloat($('#horimetro_inicial').val());
            var final = parseFloat($('#horimetro_final').val());
            var horas = (!isNaN(inicial) && !isNaN(final)) ? final - inicial : null;
            var textoHoras = horas === null ? '' : horas.toLocaleString('pt-BR', { maximumFractionDigits: 2 }) + ' h';

            if (horas === null) {
                $('#horasTrabalhadas').prop('hidden', true);
            } else {
                $('#horasTrabalhadas').text('Horas trabalhadas: ' + textoHoras).prop('hidden', false);
            }

            var partes = [];
            if ($('#equipamento').val()) {
                partes.push($('#equipamento option:selected').text().split(' - ')[0]);
            }
            var data = $('#data').val();
            if (data) {
                partes.push(data.split('-').reverse().join('/'));
            }
            if (textoHoras) {
                partes.push(textoHoras + ' trabalhadas');
            }
            $('#resumoLancamento').text(partes.join(' · '));
        }
        $(document).on('input change', '#horimetro_inicial, #horimetro_final, #equipamento, #data', atualizaResumo);
        atualizaResumo();

    });
</script>
