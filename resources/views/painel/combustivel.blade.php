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
                <h3><i class="fas fa-gas-pump"></i> Consumo de Combustível</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.combustivel.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-gas-pump"></i> Diesel</div>
                <div class="viz-tile-valor" id="kLitros">–</div>
                <div class="viz-tile-nota">lançado na Informação Diária</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-fill-drip"></i> Abastecimentos</div>
                <div class="viz-tile-valor" id="kAbastecimentos">–</div>
                <div class="viz-tile-nota" id="kMediaAbastecimento">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-truck"></i> Máquinas</div>
                <div class="viz-tile-valor" id="kMaquinas">–</div>
                <div class="viz-tile-nota">abastecidas no período</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tachometer-alt"></i> Consumo médio</div>
                <div class="viz-tile-valor" id="kConsumo">–</div>
                <div class="viz-tile-nota">pelos abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-arrow-up" style="color: #ec835a;"></i> Acima da faixa</div>
                <div class="viz-tile-valor" id="kAcima">–</div>
                <div class="viz-tile-nota">máquinas acima do consumo máximo</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Litros por máquina</h4>
                <p class="viz-sub">Diesel abastecido no período, do maior para o menor.</p>
                <div class="viz-barras" id="gLitros"></div>
            </div>
            <div class="viz-card">
                <h4>Consumo por hora</h4>
                <p class="viz-sub">L/h pelos abastecimentos. A faixa verde atrás da barra é o consumo mínimo–máximo do cadastro.</p>
                <div class="viz-barras" id="gConsumo"></div>
                <div class="viz-legenda">
                    <span><i class="cor" style="background: #0ca30c;"></i><i class="fas fa-check-circle"></i> Dentro da faixa</span>
                    <span><i class="cor" style="background: #ec835a;"></i><i class="fas fa-arrow-up"></i> Acima da faixa</span>
                    <span><i class="cor" style="background: #fab219;"></i><i class="fas fa-arrow-down"></i> Abaixo da faixa</span>
                    <span><i class="cor" style="background: #9aa69e;"></i><i class="far fa-circle"></i> Sem faixa no cadastro</span>
                </div>
            </div>
        </div>

        <div class="viz-card">
            <h4>Evolução do consumo</h4>
            <p class="viz-sub">Litros {{ $escalas[$periodos['escala']] }} no período. Clique numa coluna para ver só aquele período.</p>
            <div id="gEvolucao"></div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Litros por fazenda</h4>
                <p class="viz-sub">Onde o diesel foi consumido.</p>
                <div class="viz-barras" id="gFazenda"></div>
            </div>
            <div class="viz-card">
                <h4>Litros por tanque de origem</h4>
                <p class="viz-sub">De onde saiu o diesel (comboio, tanque ou posto).</p>
                <div class="viz-barras" id="gTanque"></div>
            </div>
        </div>

        {{-- Planilha de acompanhamento --}}
        <div class="viz-card">
            <h4>Acompanhamento por máquina</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para filtrar por aquela máquina. Faixa = consumo mínimo e máximo do cadastro do equipamento.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th class="text-right">Abastecimentos</th>
                            <th class="text-right">Litros</th>
                            <th class="text-right">Média/abastec.</th>
                            <th class="text-right">Consumo</th>
                            <th class="text-right">Faixa do cadastro</th>
                            <th>Situação</th>
                            <th class="text-center">Último abastec.</th>
                        </tr>
                    </thead>
                    <tbody id="tMaquina"></tbody>
                    <tfoot><tr id="tMaquinaTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>

        @if ($conferir->count())
            <div class="viz-card">
                <h4 style="color: #b3261e;"><i class="fas fa-exclamation-triangle"></i> Abastecimentos a conferir ({{ $conferir->count() }})</h4>
                <p class="viz-sub">Mais de 1.000 L num único abastecimento. Estão nos totais; confira se não foi erro de digitação.</p>
                <div class="viz-tabela-box">
                    <table class="viz-tabela">
                        <thead>
                            <tr><th class="text-center">Data</th><th>Máquina</th><th>Tanque de origem</th><th>Nº NF</th><th class="text-right">Litros</th><th class="text-center">Ação</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($conferir as $c)
                                <tr>
                                    <td class="text-center">{{ date('d/m/Y', strtotime($c->data)) }}</td>
                                    <td>{{ $c->maquina }}</td>
                                    <td>{{ $c->tanque }}</td>
                                    <td>{{ $c->nr_nf }}</td>
                                    <td class="num"><b>{{ $n($c->qnt_diesel, 0) }} L</b></td>
                                    <td class="text-center"><a class="btn btn-outline-info btn-sm" href="{{ route('informacao.formEdit', $c->id) }}"><i class="far fa-edit"></i> Editar</a></td>
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
            var litros = function (lista) { return u.soma(lista, 'di'); };
            var consumo = function (lista) { return u.razao(u.soma(lista, 'il'), u.soma(lista, 'ih')); };
            var ultimo = function (lista) { return lista.reduce(function (m, f) { return f.d > m ? f.d : m; }, ''); };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { maquina: 'Máquina', fazenda: 'Fazenda', tanque: 'Tanque', p: 'Período' },
                graficos: [
                    { el: '#gLitros', dim: 'maquina', valor: litros, formato: l, limite: 15,
                      dica: function (c, lista, v) { return c + '\n' + l(v) + ' em ' + lista.length + (lista.length === 1 ? ' abastecimento' : ' abastecimentos'); } },
                    { el: '#gConsumo', dim: 'maquina', valor: consumo, formato: lh, limite: 15,
                      cor: function (c, lista, v) { return faixa.cor(c, v); },
                      referencia: function (c) { return faixa.de(c); },
                      dica: function (c, lista, v) { return c + '\n' + lh(v) + ' · ' + faixa.situacao(c, v).nome + faixa.texto(c)
                                                    + '\n' + l(u.soma(lista, 'il')) + ' em ' + u.numero(u.soma(lista, 'ih')) + ' h'; } },
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: litros, formato: l, chaves: @json($periodos['chaves']) },
                    { el: '#gFazenda', dim: 'fazenda', valor: litros, formato: l, limite: 10 },
                    { el: '#gTanque', dim: 'tanque', valor: litros, formato: l, limite: 10 }
                ],
                indicadores: [
                    { el: '#kLitros', valor: function (lista) { return l(litros(lista)); } },
                    { el: '#kAbastecimentos', valor: function (lista) { return u.numero(lista.length, 0); } },
                    { el: '#kMediaAbastecimento', valor: function (lista) { return lista.length ? 'média de ' + l(litros(lista) / lista.length) + ' cada' : ''; } },
                    { el: '#kMaquinas', valor: function (lista) { return u.distintos(lista, 'maquina'); } },
                    { el: '#kConsumo', valor: function (lista) { return lh(consumo(lista)); } },
                    { el: '#kAcima', valor: function (lista) { return faixa.contarAcima(lista, consumo); } }
                ],
                tabelas: [{
                    el: '#tMaquina', dim: 'maquina', valor: litros, colunas: 8, vazio: 'Nenhum abastecimento para a seleção.',
                    celulas: function (c, lista) {
                        var v = consumo(lista), s = faixa.situacao(c, v);
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' }, num(lista.length), num(l(litros(lista)), true), num(l(litros(lista) / lista.length)),
                            num(lh(v)), num(faixa.texto(c).replace('\nCadastro: ', '') || '–'),
                            { html: '<span class="viz-selo" style="font-weight:600"><i class="' + s.icone + '" style="color:' + s.cor + '"></i> ' + s.nome + '</span>' },
                            { texto: u.data(ultimo(lista)), classe: 'text-center' }
                        ];
                    },
                    rodape: { el: '#tMaquinaTotal', celulas: function (lista) {
                        return [{ texto: 'Total' }, num(u.numero(lista.length, 0)), num(l(litros(lista))), num(lista.length ? l(litros(lista) / lista.length) : '–'),
                                num(lh(consumo(lista))), { texto: '' }, { texto: '' }, { texto: '' }];
                    } }
                }]
            });
        });
    </script>
@endsection
