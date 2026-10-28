@extends('layouts.model')
@section('content')
    @php
        $n = fn($valor, $casas = 1) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
        $escalas = ['dia' => 'por dia', 'semana' => 'por semana', 'mes' => 'por mês'];
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-tachometer-alt"></i> Consumo Médio por Máquina</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.consumo_medio.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                <b>Como é calculado:</b> em cada abastecimento, os litros abastecidos ÷ (horímetro do abastecimento − horímetro do abastecimento anterior).
                Se o abastecimento anterior não tem horímetro, vale o da data anterior a ele, e os litros sem horímetro somam no intervalo seguinte.
                Horímetro menor que o anterior, ou mais de 300 h acima, não é usado e aparece em "a conferir".
            </p>
        </div>

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tachometer-alt"></i> Consumo médio</div>
                <div class="viz-tile-valor" id="kConsumo">–</div>
                <div class="viz-tile-nota">litros ÷ horas entre abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-fill-drip"></i> Abastecimentos medidos</div>
                <div class="viz-tile-valor" id="kIntervalos">–</div>
                <div class="viz-tile-nota">com horímetro anterior para comparar</div>
                @if ($semHorimetro)
                    <a class="viz-tile-alerta" href="{{ route('painel.consumo_medio.sem_horimetro') }}" title="Abrir a tela de correção">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ number_format($semHorimetro, 0, ',', '.') }} {{ $semHorimetro == 1 ? 'lançamento' : 'lançamentos' }} sem horímetro de abastecimento
                        <b>· Corrigir →</b>
                    </a>
                @else
                    <span class="viz-tile-ok"><i class="fas fa-check-circle"></i> Todos os abastecimentos com horímetro</span>
                @endif
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-gas-pump"></i> Litros medidos</div>
                <div class="viz-tile-valor" id="kLitros">–</div>
                <div class="viz-tile-nota">nesses abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-clock"></i> Horas medidas</div>
                <div class="viz-tile-valor" id="kHoras">–</div>
                <div class="viz-tile-nota">entre os abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-arrow-up" style="color: #ec835a;"></i> Acima da faixa</div>
                <div class="viz-tile-valor" id="kAcima">–</div>
                <div class="viz-tile-nota">máquinas acima do consumo máximo</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Consumo médio por máquina</h4>
                <p class="viz-sub">L/h de cada máquina, do maior para o menor. A faixa verde atrás da barra é o consumo mínimo–máximo do cadastro.</p>
                <div class="viz-barras" id="gConsumo"></div>
                <div class="viz-legenda">
                    <span><i class="cor" style="background: #0ca30c;"></i><i class="fas fa-check-circle"></i> Dentro da faixa</span>
                    <span><i class="cor" style="background: #ec835a;"></i><i class="fas fa-arrow-up"></i> Acima da faixa</span>
                    <span><i class="cor" style="background: #fab219;"></i><i class="fas fa-arrow-down"></i> Abaixo da faixa</span>
                    <span><i class="cor" style="background: #9aa69e;"></i><i class="far fa-circle"></i> Sem faixa no cadastro</span>
                </div>
            </div>
            <div class="viz-card">
                <h4>Evolução do consumo médio</h4>
                <p class="viz-sub">L/h {{ $escalas[$periodos['escala']] }}, pelos abastecimentos de cada período. Clique numa coluna para ver só aquele período.</p>
                <div id="gEvolucao"></div>
                <h4 style="margin-top: 22px;">Consumo médio por fazenda</h4>
                <p class="viz-sub">L/h dos abastecimentos feitos em cada fazenda.</p>
                <div class="viz-barras" id="gFazenda"></div>
            </div>
        </div>

        {{-- Planilha por máquina --}}
        <div class="viz-card">
            <h4>Acompanhamento por máquina</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para ver só aquela máquina (inclusive o detalhe abaixo).</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th class="text-right">Abastec. medidos</th>
                            <th class="text-right">Litros</th>
                            <th class="text-right">Horas</th>
                            <th class="text-right">Consumo médio</th>
                            <th class="text-right">Menor</th>
                            <th class="text-right">Maior</th>
                            <th class="text-right">Faixa do cadastro</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody id="tMaquina"></tbody>
                    <tfoot><tr id="tMaquinaTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>

        {{-- Detalhe: cada abastecimento --}}
        <div class="viz-card">
            <h4>Abastecimentos</h4>
            <p class="viz-sub" id="detalheNota">A conta de cada abastecimento, do mais recente para o mais antigo.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th class="text-center">Data</th>
                            <th>Máquina</th>
                            <th>Fazenda</th>
                            <th class="text-right">Horímetro anterior</th>
                            <th class="text-right">Horímetro</th>
                            <th class="text-right">Horas</th>
                            <th class="text-right">Litros</th>
                            <th class="text-right">Consumo</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tDetalhe"></tbody>
                </table>
            </div>
        </div>

        @if (count($conferir))
            <div class="viz-card">
                <h4 style="color: #b3261e;"><i class="fas fa-exclamation-triangle"></i> Horímetros de abastecimento a conferir ({{ count($conferir) }})</h4>
                <p class="viz-sub">O horímetro é menor que o do abastecimento anterior, ou mais de 300 h acima dele: provável erro de digitação. Não entrou na conta.</p>
                <div class="viz-tabela-box">
                    <table class="viz-tabela">
                        <thead>
                            <tr><th class="text-center">Data</th><th>Máquina</th><th class="text-right">Horímetro</th><th class="text-right">Horímetro anterior</th><th class="text-center">Data anterior</th><th class="text-right">Litros</th><th class="text-center">Ação</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($conferir as $c)
                                <tr>
                                    <td class="text-center">{{ date('d/m/Y', strtotime($c['data'])) }}</td>
                                    <td>{{ $c['maquina'] }}</td>
                                    <td class="num"><b>{{ $n($c['horimetro'], 2) }}</b></td>
                                    <td class="num">{{ $n($c['horimetro_ref'], 2) }}</td>
                                    <td class="text-center">{{ date('d/m/Y', strtotime($c['data_ref'])) }}</td>
                                    <td class="num">{{ $n($c['litros'], 0) }} L</td>
                                    <td class="text-center"><a class="btn btn-outline-info btn-sm" href="{{ route('informacao.formEdit', $c['id']) }}"><i class="far fa-edit"></i> Editar</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @include('partials.painel-interativo')
    @include('partials.painel-faixa-consumo')
    <script>
        $(function () {
            var u = PainelInterativo.util, faixa = FaixaConsumo(@json((object) $faixas));
            var l = function (v) { return u.numero(v, 0) + ' L'; };
            var lh = function (v) { return v === null ? '–' : u.numero(v, 2) + ' L/h'; };
            var consumo = function (lista) { return u.razao(u.soma(lista, 'il'), u.soma(lista, 'ih')); };
            var extremos = function (lista) {
                var v = lista.map(function (f) { return f.il / f.ih; });
                return [Math.min.apply(null, v), Math.max.apply(null, v)];
            };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };
            var editar = '{{ url('informacao/editar') }}/';
            var LIMITE_DETALHE = 300;

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { maquina: 'Máquina', fazenda: 'Fazenda', p: 'Período' },
                graficos: [
                    { el: '#gConsumo', dim: 'maquina', valor: consumo, formato: lh, limite: 20,
                      cor: function (c, lista, v) { return faixa.cor(c, v); },
                      referencia: function (c) { return faixa.de(c); },
                      dica: function (c, lista, v) { return c + '\n' + lh(v) + ' · ' + faixa.situacao(c, v).nome + faixa.texto(c)
                                                    + '\n' + l(u.soma(lista, 'il')) + ' em ' + u.numero(u.soma(lista, 'ih')) + ' h (' + lista.length + ' abastecimentos)'; } },
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: consumo, formato: lh, chaves: @json($periodos['chaves']) },
                    { el: '#gFazenda', dim: 'fazenda', valor: consumo, formato: lh, limite: 10 }
                ],
                indicadores: [
                    { el: '#kConsumo', valor: function (lista) { return lh(consumo(lista)); } },
                    { el: '#kIntervalos', valor: function (lista) { return u.numero(lista.length, 0); } },
                    { el: '#kLitros', valor: function (lista) { return l(u.soma(lista, 'il')); } },
                    { el: '#kHoras', valor: function (lista) { return u.numero(u.soma(lista, 'ih')) + ' h'; } },
                    { el: '#kAcima', valor: function (lista) { return faixa.contarAcima(lista, consumo); } },
                    { el: '#detalheNota', valor: function (lista) {
                        return 'A conta de cada abastecimento, do mais recente para o mais antigo'
                             + (lista.length > LIMITE_DETALHE ? ' (mostrando os ' + LIMITE_DETALHE + ' mais recentes de ' + u.numero(lista.length, 0) + '; selecione uma máquina ou exporte a planilha para ver todos).' : '.');
                    } }
                ],
                tabelas: [{
                    el: '#tMaquina', dim: 'maquina', valor: function (lista) { return u.soma(lista, 'il'); }, colunas: 9, vazio: 'Nenhum abastecimento medido para a seleção.',
                    celulas: function (c, lista) {
                        var v = consumo(lista), s = faixa.situacao(c, v), e = extremos(lista);
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' }, num(lista.length), num(l(u.soma(lista, 'il'))), num(u.numero(u.soma(lista, 'ih')) + ' h'),
                            num(lh(v), true), num(lh(e[0])), num(lh(e[1])), num(faixa.texto(c).replace('\nCadastro: ', '') || '–'),
                            { html: '<span class="viz-selo" style="font-weight:600"><i class="' + s.icone + '" style="color:' + s.cor + '"></i> ' + s.nome + '</span>' }
                        ];
                    },
                    rodape: { el: '#tMaquinaTotal', celulas: function (lista) {
                        return [{ texto: 'Total' }, num(u.numero(lista.length, 0)), num(l(u.soma(lista, 'il'))), num(u.numero(u.soma(lista, 'ih')) + ' h'),
                                num(lh(consumo(lista))), { texto: '' }, { texto: '' }, { texto: '' }, { texto: '' }];
                    } }
                }, {
                    el: '#tDetalhe', colunas: 9, vazio: 'Nenhum abastecimento medido para a seleção.',
                    linhas: function (lista) {
                        return lista.slice().sort(function (a, b) { return a.d < b.d ? 1 : (a.d > b.d ? -1 : b.id - a.id); }).slice(0, LIMITE_DETALHE);
                    },
                    celulas: function (f) {
                        var v = f.il / f.ih, s = faixa.situacao(f.maquina, v);
                        return [
                            { texto: u.data(f.d), classe: 'text-center' }, { texto: f.maquina }, { texto: f.fazenda },
                            { html: u.esc(u.numero(f.hr, 1)) + '<span class="viz-origem">em ' + u.data(f.dr) + '</span>', classe: 'num' },
                            num(u.numero(f.h, 1)), num(u.numero(f.ih, 1) + ' h'), num(l(f.il)),
                            { html: '<i class="' + s.icone + '" style="color:' + s.cor + '" title="' + s.nome + '"></i> <b>' + u.esc(lh(v)) + '</b>', classe: 'num' },
                            { html: '<a class="btn btn-outline-info btn-sm" href="' + editar + f.id + '"><i class="far fa-edit"></i> Editar</a>', classe: 'text-center' }
                        ];
                    }
                }]
            });
        });
    </script>
@endsection
