{{--
    Formulário da Revisão (inclusão e alteração).
    Variáveis: $revisao (null na inclusão), $route, $type, $equipamentos, $tipos_revisao.
    Os id/name dos campos são lidos pelo custom.js (form#cadastro-revisao): não renomear.
--}}
@php
    $revisao = $revisao ?? null;
    $v   = fn($campo) => $revisao ? $revisao->$campo : null;
    $sel = fn($campo, $id) => $revisao && $revisao->$campo == $id ? 'selected' : '';

    // Peças e lubrificantes: opções dos selects e linhas já lançadas (uma linha vazia quando não há nenhuma)
    $grupos = [
        'pecas' => [
            'titulo'  => 'Peças utilizadas',
            'rotulo'  => 'Peça',
            'botao'   => 'Adicionar peça',
            'opcoes'  => $pecas->mapWithKeys(fn($p) => [$p->id => $p->cod_cargo ? $p->peca.' ('.$p->cod_cargo.')' : $p->peca])->all(),
            'linhas'  => $revisao ? $revisao->pecas->map(fn($i) => ['id' => $i->peca, 'quantidade' => $i->quantidade])->all() : [],
        ],
        'lubrificantes' => [
            'titulo'  => 'Óleo lubrificante',
            'rotulo'  => 'Lubrificante',
            'botao'   => 'Adicionar lubrificante',
            'opcoes'  => $lubrificantes->pluck('lubrificante', 'id')->all(),
            'linhas'  => $revisao ? $revisao->lubrificantes->map(fn($i) => ['id' => $i->lubrificante, 'quantidade' => $i->quantidade])->all() : [],
        ],
    ];
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
            @foreach ($grupos as $grupo => $g)
                <div class="info-quadro">
                    <h5>{{ $g['titulo'] }}</h5>
                    <table>
                        <thead><tr><th>{{ $g['rotulo'] }}</th><th style="width: 30%;">Quantidade</th><th style="width: 1%;"></th></tr></thead>
                        <tbody id="itens-{{ $grupo }}">
                            @forelse ($g['linhas'] as $linha)
                                @include('revisao._item', ['grupo' => $grupo, 'rotulo' => $g['rotulo'], 'opcoes' => $g['opcoes'], 'id' => $linha['id'], 'quantidade' => $linha['quantidade']])
                            @empty
                                @include('revisao._item', ['grupo' => $grupo, 'rotulo' => $g['rotulo'], 'opcoes' => $g['opcoes'], 'id' => null, 'quantidade' => null])
                            @endforelse
                        </tbody>
                    </table>
                    <template id="modelo-{{ $grupo }}">
                        @include('revisao._item', ['grupo' => $grupo, 'rotulo' => $g['rotulo'], 'opcoes' => $g['opcoes'], 'id' => null, 'quantidade' => null])
                    </template>
                    <button type="button" class="btn btn-outline-success btn-sm item-adicionar" data-grupo="{{ $grupo }}" style="margin: 6px 8px 4px;">
                        <span class="fas fa-plus"></span> {{ $g['botao'] }}
                    </button>
                    @if (!count($g['opcoes']))
                        <small class="text-muted d-block" style="margin: 0 8px;">Nenhum item cadastrado ainda.</small>
                    @endif
                </div>
            @endforeach
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

        // + acrescenta uma linha vazia (cópia do <template>); x remove, mas sempre sobra uma linha
        $('.item-adicionar').on('click', function () {
            var grupo = $(this).data('grupo');
            var linha = $($('#modelo-' + grupo).html());
            $('#itens-' + grupo).append(linha);
            linha.find('select').chosen();
            linha.find('select').trigger('chosen:activate');
        });
        $(document).on('click', '.item-remover', function () {
            var linha = $(this).closest('tr');
            if (linha.siblings('.linha-item').length) {
                linha.remove();
            } else {
                linha.find('select').val('').trigger('chosen:updated');
                linha.find('input').val('');
            }
        });

    });
</script>
