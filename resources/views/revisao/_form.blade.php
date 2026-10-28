{{--
    Formulário da Revisão (inclusão e alteração).
    Variáveis: $revisao (null na inclusão), $route, $type, $equipamentos, $tipos_revisao, $opcoesPecas, $opcoesLubrificantes,
               $linhasPecas e $linhasLubrificantes (só na alteração).
    Os id/name dos campos são lidos pelo custom.js (form#cadastro-revisao): não renomear.
--}}
@php
    $revisao = $revisao ?? null;
    // Na inclusão, o Painel de Revisões pode já mandar equipamento, tipo e horímetro (?equipamento=&tipo_revisao=&horimetro_atual=)
    $preenchidos = ['equipamento', 'tipo_revisao', 'horimetro_atual'];
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
            <div class="info-campo"><label for="horimetro_atual">Horímetro atual <b>*</b></label>
                <input class="form-control" type="number" step="any" min="0" name="horimetro_atual" id="horimetro_atual" value="{{ $v('horimetro_atual') }}">
            </div>
            <div class="info-campo"><label for="tipo_revisao">Tipo da revisão <b>*</b></label>
                <select class="form-control limpar" name="tipo_revisao" id="tipo_revisao">
                    <option value="" data-intervalo="">Selecione</option>
                    @foreach ($tipos_revisao as $tipo)
                        <option value="{{ $tipo->id }}" data-intervalo="{{ $tipo->intervalo }}" {{ $sel('tipo_revisao', $tipo->id) }}>{{ $tipo->descricao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="horimetro_proxima">Horímetro próx. revisão</label>
                <input class="form-control" type="text" id="horimetro_proxima" value="" readonly tabindex="-1" style="background: #fff4c2; font-weight: 700;">
                <small class="text-muted" id="calculoProxima">Horímetro atual + KM/Horas do tipo da revisão</small>
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
            var intervalo = parseFloat($('#tipo_revisao option:selected').data('intervalo'));

            if (!isNaN(atual) && !isNaN(intervalo)) {
                $('#horimetro_proxima').val(numero(atual + intervalo));
                $('#calculoProxima').text(numero(atual) + ' + ' + numero(intervalo) + ' do tipo da revisão');
            } else {
                $('#horimetro_proxima').val('');
                $('#calculoProxima').text('Horímetro atual + KM/Horas do tipo da revisão');
            }

            var partes = [];
            if ($('#equipamento').val()) {
                partes.push($('#equipamento option:selected').text().split(' - ')[0]);
            }
            if ($('#tipo_revisao').val()) {
                partes.push($('#tipo_revisao option:selected').text());
            }
            if ($('#horimetro_proxima').val()) {
                partes.push('próxima em ' + $('#horimetro_proxima').val());
            }
            $('#resumoLancamento').text(partes.join(' · '));
        }
        $(document).on('input change', '#horimetro_atual, #tipo_revisao, #equipamento', calculaProxima);
        calculaProxima();

    });
</script>
