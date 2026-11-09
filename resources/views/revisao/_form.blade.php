{{--
    Formulário da Revisão (inclusão e alteração).
    Variáveis: $revisao (null na inclusão), $route, $type, $equipamentos, $tipos_revisao, $intervalos_revisao, $opcoesPecas,
               $opcoesLubrificantes, $linhasPecas e $linhasLubrificantes (só na alteração).
    Os id/name dos campos são lidos pelo custom.js (form#cadastro-revisao): não renomear.
    "Revisão Atual" é o campo tipo_revisao; "Próxima Revisão" (proxima_revisao) também é um tipo de revisão.
--}}
@php
    $revisao = $revisao ?? null;
    // Na inclusão, o Painel de Revisões pode já mandar equipamento, revisão, intervalo e horímetro
    // (?equipamento=&tipo_revisao=&intervalo_revisao=&horimetro_atual=)
    $preenchidos = ['equipamento', 'tipo_revisao', 'intervalo_revisao', 'horimetro_atual'];
    $v   = fn($campo) => $revisao ? $revisao->$campo : (in_array($campo, $preenchidos) ? request($campo) : null);
    $sel = fn($campo, $id) => (string) $v($campo) !== '' && $v($campo) == $id ? 'selected' : '';
@endphp

@include('partials.estilo-formulario')

<form action="" id="cadastro-revisao" nome="cadastro-revisao" method="post" class="info-form">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="revisao">

    <div class="info-topo">
        <div>
            <small>Apontamento · {{ $revisao ? 'Alteração' : 'Nova revisão' }}</small>
            <h3><i class="fas fa-calendar-check"></i> Revisão</h3>
        </div>
        <span class="info-obrig">Campos com <b>*</b> são obrigatórios</span>
    </div>

    <section class="info-card">
        <div class="info-card-topo"><h4>Dados da revisão</h4></div>
        <div class="info-grid info-g3">
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
            <div class="info-campo"><label for="nr_os">Número da OS</label>
                <input class="form-control" type="text" maxlength="30" name="nr_os" id="nr_os" value="{{ $v('nr_os') }}">
            </div>
        </div>
        <div class="info-grid info-g3">
            <div class="info-campo"><label for="tipo_revisao">Revisão Atual <b>*</b></label>
                <select class="form-control limpar" name="tipo_revisao" id="tipo_revisao">
                    <option value="">Selecione</option>
                    @foreach ($tipos_revisao as $tipo)
                        <option value="{{ $tipo->id }}" {{ $sel('tipo_revisao', $tipo->id) }}>{{ $tipo->descricao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="horimetro_atual">Horímetro atual <b>*</b></label>
                <input class="form-control" type="number" step="any" min="0" name="horimetro_atual" id="horimetro_atual" value="{{ $v('horimetro_atual') }}">
            </div>
            <div class="info-campo"><label for="intervalo_revisao">Intervalo de Revisão <b>*</b></label>
                <select class="form-control limpar" name="intervalo_revisao" id="intervalo_revisao">
                    <option value="" data-horas="">Selecione</option>
                    @foreach ($intervalos_revisao as $intervalo)
                        <option value="{{ $intervalo->id }}" data-horas="{{ $intervalo->horas }}" {{ $sel('intervalo_revisao', $intervalo->id) }}>{{ rtrim(rtrim(number_format($intervalo->horas, 2, ',', '.'), '0'), ',') }} h</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="info-grid info-g3">
            <div class="info-campo"><label for="proxima_revisao">Próxima Revisão <b>*</b></label>
                <select class="form-control limpar" name="proxima_revisao" id="proxima_revisao">
                    <option value="">Selecione</option>
                    @foreach ($tipos_revisao as $tipo)
                        <option value="{{ $tipo->id }}" {{ $sel('proxima_revisao', $tipo->id) }}>{{ $tipo->descricao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="horimetro_proxima">Horímetro próx. revisão</label>
                <input class="form-control" type="text" id="horimetro_proxima" value="" readonly tabindex="-1" style="background: #fff4c2; font-weight: 700;">
                <small class="text-muted" id="calculoProxima">Horímetro atual + intervalo de revisão</small>
            </div>
        </div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="hora_inicial">Hora início revisão</label>
                <input class="form-control" type="time" name="hora_inicial" id="hora_inicial" value="{{ $v('hora_inicial') ? substr($v('hora_inicial'), 0, 5) : '' }}">
            </div>
            <div class="info-campo"><label for="hora_final">Hora final revisão</label>
                <input class="form-control" type="time" name="hora_final" id="hora_final" value="{{ $v('hora_final') ? substr($v('hora_final'), 0, 5) : '' }}">
            </div>
            <div class="info-campo"><label for="custo_hora">Custo hora (R$/h)</label>
                <input class="form-control" type="number" step="0.01" min="0" name="custo_hora" id="custo_hora" value="{{ $v('custo_hora') }}" placeholder="0,00">
            </div>
            <div class="info-campo"><label for="custoHorasCalc">Custo das horas trabalhadas</label>
                <input class="form-control" type="text" id="custoHorasCalc" value="" readonly tabindex="-1" style="background: #fff4c2; font-weight: 700;">
                <small class="text-muted" id="custoHorasNota"></small>
            </div>
        </div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="custo_terceiro">Custo de terceiro (R$)</label>
                <input class="form-control" type="number" step="0.01" min="0" name="custo_terceiro" id="custo_terceiro" value="{{ $v('custo_terceiro') }}" placeholder="0,00">
            </div>
            <div class="info-campo"><label for="custo_deslocamento">Custo de deslocamento (R$)</label>
                <input class="form-control" type="number" step="0.01" min="0" name="custo_deslocamento" id="custo_deslocamento" value="{{ $v('custo_deslocamento') }}" placeholder="0,00">
            </div>
        </div>
        <div class="info-grid">
            <div class="info-campo"><label for="obs">Observação</label>
                <input class="form-control" type="text" maxlength="255" name="obs" id="obs" value="{{ $v('obs') }}" placeholder="Serviços executados, peças trocadas, pendências…">
            </div>
        </div>
    </section>

    <section class="info-card">
        <div class="info-card-topo"><h4>Peças e lubrificantes</h4></div>
        <div class="info-grid info-g2">
            @include('partials.itens-quadro', ['grupo' => 'pecas', 'titulo' => 'Peças utilizadas', 'rotulo' => 'Peça', 'botao' => 'Adicionar peça',
                                               'opcoes' => $opcoesPecas, 'linhas' => $linhasPecas ?? []])
            @include('partials.itens-quadro', ['grupo' => 'lubrificantes', 'titulo' => 'Óleo lubrificante', 'rotulo' => 'Lubrificante', 'botao' => 'Adicionar lubrificante',
                                               'opcoes' => $opcoesLubrificantes, 'linhas' => $linhasLubrificantes ?? []])
        </div>
    </section>

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
            window.location.href = "{{ route('revisao.listAll') }}";
        });

        // Prévia do horímetro da próxima revisão (o servidor recalcula ao gravar)
        function numero(valor) {
            return valor.toLocaleString('pt-BR', { maximumFractionDigits: 2 });
        }
        function calculaProxima() {
            var atual = parseFloat($('#horimetro_atual').val());
            var intervalo = parseFloat($('#intervalo_revisao option:selected').data('horas'));

            if (!isNaN(atual) && !isNaN(intervalo)) {
                $('#horimetro_proxima').val(numero(atual + intervalo));
                $('#calculoProxima').text(numero(atual) + ' + ' + numero(intervalo) + ' h de intervalo');
            } else {
                $('#horimetro_proxima').val('');
                $('#calculoProxima').text('Horímetro atual + intervalo de revisão');
            }

            var partes = [];
            if ($('#equipamento').val()) {
                partes.push($('#equipamento option:selected').text().split(' - ')[0]);
            }
            if ($('#tipo_revisao').val()) {
                partes.push($('#tipo_revisao option:selected').text());
            }
            if ($('#proxima_revisao').val() || $('#horimetro_proxima').val()) {
                partes.push('próxima: ' + ($('#proxima_revisao').val() ? $('#proxima_revisao option:selected').text() : '')
                    + ($('#horimetro_proxima').val() ? ' em ' + $('#horimetro_proxima').val() : ''));
            }
            $('#resumoLancamento').text(partes.join(' · '));
        }
        $(document).on('input change', '#horimetro_atual, #tipo_revisao, #intervalo_revisao, #proxima_revisao, #equipamento', calculaProxima);
        calculaProxima();

    });
</script>
@include('partials.custo-horas')
