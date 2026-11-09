@extends('layouts.model')
@section('content')
    @php
        $escalas = ['dia' => 'por dia', 'semana' => 'por semana', 'mes' => 'por mês'];
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-tree"></i> Consumo por Produção</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.consumo_producao.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                <b>Como é calculado:</b> litros de diesel abastecidos ÷ <b>Faturamento (m²)</b> lançado na Informação Diária, por máquina.
                Entram só as máquinas que tiveram faturamento no período (e com Controla Média = Sim).
                Como o abastecimento e a produção costumam cair em dias diferentes, a evolução é mostrada no mínimo por semana,
                e o consumo de períodos curtos pode oscilar.
            </p>
        </div>

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tint"></i> Consumo por produção</div>
                <div class="viz-tile-valor" id="kLm">–</div>
                <div class="viz-tile-nota">litros de diesel por m² faturado</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tree"></i> Faturamento</div>
                <div class="viz-tile-valor" id="kProducao">–</div>
                <div class="viz-tile-nota" id="kDias">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-gas-pump"></i> Diesel</div>
                <div class="viz-tile-valor" id="kDiesel">–</div>
                <div class="viz-tile-nota">abastecido nessas máquinas</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-stopwatch"></i> Produtividade</div>
                <div class="viz-tile-valor" id="kProdutividade">–</div>
                <div class="viz-tile-nota">m² por hora de máquina produzindo</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tractor"></i> Máquinas</div>
                <div class="viz-tile-valor" id="kMaquinas">–</div>
                <div class="viz-tile-nota">com faturamento no período</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Consumo por produção por máquina</h4>
                <p class="viz-sub">L/m² de cada máquina, da que mais gasta para a que menos. Menor é melhor.</p>
                <div class="viz-barras" id="gLm"></div>
            </div>
            <div class="viz-card">
                <h4>Faturamento por máquina</h4>
                <p class="viz-sub">m² faturados no período.</p>
                <div class="viz-barras" id="gProducao"></div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Evolução do consumo por produção</h4>
                <p class="viz-sub">L/m² {{ $escalas[$periodos['escala']] }}. Clique numa coluna para ver só aquele período.</p>
                <div id="gEvolucaoLm"></div>
            </div>
            <div class="viz-card">
                <h4>Evolução do faturamento</h4>
                <p class="viz-sub">m² {{ $escalas[$periodos['escala']] }}.</p>
                <div id="gEvolucaoProducao"></div>
            </div>
        </div>

        {{-- Planilha de acompanhamento --}}
        <div class="viz-card">
            <h4>Acompanhamento por máquina</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para filtrar por aquela máquina.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th class="text-right">Dias com produção</th>
                            <th class="text-right">Faturamento</th>
                            <th class="text-right">Diesel</th>
                            <th class="text-right">Consumo por produção</th>
                            <th class="text-right">Horas produzindo</th>
                            <th class="text-right">Produtividade</th>
                            <th class="text-center">Último dia com produção</th>
                        </tr>
                    </thead>
                    <tbody id="tMaquina"></tbody>
                    <tfoot><tr id="tMaquinaTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    @include('partials.painel-interativo')
    <script>
        $(function () {
            var u = PainelInterativo.util;
            var m2 = function (v) { return u.numero(v, 0) + ' m²'; };
            var l = function (v) { return u.numero(v, 0) + ' L'; };
            var lm = function (v) { return v === null ? '–' : u.numero(v, 3) + ' L/m²'; };
            var m2h = function (v) { return v === null ? '–' : u.numero(v, 1) + ' m²/h'; };
            var producao = function (lista) { return u.soma(lista, 'fm'); };
            var diesel = function (lista) { return u.soma(lista, 'di'); };
            var consumo = function (lista) { return u.razao(diesel(lista), producao(lista)); };
            var produtividade = function (lista) { return u.razao(producao(lista), u.soma(lista, 'hm')); };
            var diasProducao = function (lista) { return u.distintos(lista, function (f) { return f.maquina + '|' + f.d; }, function (f) { return f.fm > 0; }); };
            var ultimo = function (lista) { return lista.reduce(function (m, f) { return f.fm > 0 && f.d > m ? f.d : m; }, ''); };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };
            var dicaConsumo = function (c, lista, v) { return c + '\n' + lm(v) + '\n' + l(diesel(lista)) + ' ÷ ' + m2(producao(lista)); };

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { maquina: 'Máquina', p: 'Período' },
                graficos: [
                    { el: '#gLm', dim: 'maquina', valor: consumo, formato: lm, limite: 15, dica: dicaConsumo },
                    { el: '#gProducao', dim: 'maquina', valor: producao, formato: m2, limite: 15,
                      dica: function (c, lista, v) { return c + '\n' + m2(v) + ' em ' + diasProducao(lista) + ' dias com produção'; } },
                    { el: '#gEvolucaoLm', tipo: 'colunas', dim: 'p', valor: consumo, formato: lm, chaves: @json($periodos['chaves']) },
                    { el: '#gEvolucaoProducao', tipo: 'colunas', dim: 'p', valor: producao, formato: m2, chaves: @json($periodos['chaves']) }
                ],
                indicadores: [
                    { el: '#kLm', valor: function (lista) { return lm(consumo(lista)); } },
                    { el: '#kProducao', valor: function (lista) { return m2(producao(lista)); } },
                    { el: '#kDias', valor: function (lista) { var d = diasProducao(lista); return d + (d === 1 ? ' dia-máquina' : ' dias-máquina') + ' com produção'; } },
                    { el: '#kDiesel', valor: function (lista) { return l(diesel(lista)); } },
                    { el: '#kProdutividade', valor: function (lista) { return m2h(produtividade(lista)); } },
                    { el: '#kMaquinas', valor: function (lista) { return u.distintos(lista, 'maquina', function (f) { return f.fm > 0; }); } }
                ],
                tabelas: [{
                    el: '#tMaquina', dim: 'maquina', valor: producao, colunas: 8, vazio: 'Nenhuma máquina com faturamento para a seleção.',
                    celulas: function (c, lista) {
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' }, num(u.numero(diasProducao(lista), 0)), num(m2(producao(lista))), num(l(diesel(lista))),
                            num(lm(consumo(lista)), true), num(u.numero(u.soma(lista, 'hm'), 1) + ' h'), num(m2h(produtividade(lista))),
                            { texto: u.data(ultimo(lista)), classe: 'text-center' }
                        ];
                    },
                    rodape: { el: '#tMaquinaTotal', celulas: function (lista) {
                        return [{ texto: 'Total' }, num(u.numero(diasProducao(lista), 0)), num(m2(producao(lista))), num(l(diesel(lista))),
                                num(lm(consumo(lista))), num(u.numero(u.soma(lista, 'hm'), 1) + ' h'), num(m2h(produtividade(lista))), { texto: '' }];
                    } }
                }]
            });
        });
    </script>
@endsection
