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
                <h3><i class="fas fa-oil-can"></i> Consumo de Óleo Lubrificante</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.lubrificante.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-oil-can"></i> Lubrificante</div>
                <div class="viz-tile-valor" id="kQuantidade">–</div>
                <div class="viz-tile-nota">diária, manutenção e revisão</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-list-ul"></i> Lançamentos</div>
                <div class="viz-tile-valor" id="kLancamentos">–</div>
                <div class="viz-tile-nota">no período</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-truck"></i> Máquinas</div>
                <div class="viz-tile-valor" id="kMaquinas">–</div>
                <div class="viz-tile-nota">com lubrificante lançado</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tachometer-alt"></i> A cada 100 h</div>
                <div class="viz-tile-valor" id="kPor100">–</div>
                <div class="viz-tile-nota">por 100 horas de máquina</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tint"></i> Mais usado</div>
                <div class="viz-tile-valor" id="kMaisUsado" style="font-size: 20px; padding-top: 6px;">–</div>
                <div class="viz-tile-nota" id="kMaisUsadoQtd">&nbsp;</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Quantidade por máquina</h4>
                <p class="viz-sub">Dividida por tipo de lubrificante, da maior para a menor.</p>
                <div class="viz-barras" id="gMaquina"></div>
                <div class="viz-legenda">
                    @foreach ($cores as $nome => $cor)
                        <span><i class="cor" style="background: {{ $cor }};"></i>{{ $nome }}</span>
                    @endforeach
                </div>
            </div>
            <div class="viz-card">
                <h4>Quantidade por tipo</h4>
                <p class="viz-sub">Total de cada lubrificante. Clique num tipo para ver só ele nos outros gráficos.</p>
                <div class="viz-barras" id="gTipo"></div>
                <h4 style="margin-top: 22px;">Onde foi lançado</h4>
                <p class="viz-sub">Informação Diária, Manutenção ou Revisão.</p>
                <div class="viz-barras" id="gOrigem"></div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Evolução do consumo</h4>
                <p class="viz-sub">Quantidade {{ $escalas[$periodos['escala']] }} no período.</p>
                <div id="gEvolucao"></div>
            </div>
            <div class="viz-card">
                <h4>Quantidade por fazenda</h4>
                <p class="viz-sub">A revisão não tem fazenda: aparece como "Sem fazenda (revisão)".</p>
                <div class="viz-barras" id="gFazenda"></div>
            </div>
        </div>

        {{-- Planilha de acompanhamento --}}
        <div class="viz-card">
            <h4>Acompanhamento por máquina</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para filtrar por aquela máquina. A cada 100 h = quantidade ÷ horas de máquina do período × 100.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            @foreach ($cores as $nome => $cor)
                                <th class="text-right"><i style="display: inline-block; width: 10px; height: 10px; border-radius: 2px; background: {{ $cor }};"></i> {{ $nome }}</th>
                            @endforeach
                            <th class="text-right">Total</th>
                            <th class="text-right">Lançamentos</th>
                            <th class="text-right">Horas máquina</th>
                            <th class="text-right">A cada 100 h</th>
                            <th class="text-center">Último lançamento</th>
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
            var u = PainelInterativo.util, cores = @json((object) $cores), tipos = Object.keys(cores);
            var q = function (v) { return u.numero(v) + ' L'; };
            var quantidade = function (l) { return u.soma(l, 'q'); };
            var lancamentos = function (l) { return l.filter(function (f) { return f.q > 0; }); };
            var por100 = function (l) { var h = u.soma(l, 'hm'); return h > 0 ? quantidade(l) / h * 100 : null; };
            var porTipo = function (l) {
                var t = {};
                l.forEach(function (f) { if (f.q > 0) { t[f.tipo] = (t[f.tipo] || 0) + f.q; } });
                return t;
            };
            var ultimo = function (l) { return lancamentos(l).reduce(function (m, f) { return f.d > m ? f.d : m; }, ''); };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { maquina: 'Máquina', tipo: 'Lubrificante', origem: 'Lançado em', fazenda: 'Fazenda', p: 'Período' },
                graficos: [
                    { el: '#gMaquina', dim: 'maquina', valor: quantidade, formato: q, limite: 15,
                      partes: function (l) { var t = porTipo(l); return tipos.map(function (n) { return { valor: t[n] || 0, cor: cores[n] }; }); },
                      dica: function (c, l) { var t = porTipo(l); return c + '\n' + Object.keys(t).map(function (n) { return n + ': ' + q(t[n]); }).join('\n'); } },
                    { el: '#gTipo', dim: 'tipo', valor: quantidade, formato: q, cor: function (c) { return cores[c] || '#9aa69e'; },
                      dica: function (c, l, v) { return c + '\n' + q(v) + ' em ' + lancamentos(l).length + ' lançamentos'; } },
                    { el: '#gOrigem', dim: 'origem', valor: quantidade, formato: q, cor: function () { return '#5b6b60'; } },
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: quantidade, formato: q, chaves: @json($periodos['chaves']) },
                    { el: '#gFazenda', dim: 'fazenda', valor: quantidade, formato: q, limite: 10 }
                ],
                indicadores: [
                    { el: '#kQuantidade', valor: function (l) { return q(quantidade(l)); } },
                    { el: '#kLancamentos', valor: function (l) { return u.numero(lancamentos(l).length, 0); } },
                    { el: '#kMaquinas', valor: function (l) { return u.distintos(l, 'maquina', function (f) { return f.q > 0; }); } },
                    { el: '#kPor100', valor: function (l) { var v = por100(l); return v === null ? '–' : q(v); } },
                    { el: '#kMaisUsado', valor: function (l) { var t = porTipo(l), m = Object.keys(t).sort(function (a, b) { return t[b] - t[a]; })[0]; return m || '–'; } },
                    { el: '#kMaisUsadoQtd', valor: function (l) { var t = porTipo(l), m = Object.keys(t).sort(function (a, b) { return t[b] - t[a]; })[0]; return m ? q(t[m]) : 'sem lançamentos'; } }
                ],
                tabelas: [{
                    el: '#tMaquina', dim: 'maquina', valor: quantidade, colunas: tipos.length + 6, vazio: 'Nenhum lubrificante lançado para a seleção.',
                    celulas: function (c, l) {
                        var t = porTipo(l), v = por100(l);
                        return [{ html: '<b>' + u.esc(c) + '</b>' }]
                            .concat(tipos.map(function (n) { return num(t[n] ? q(t[n]) : '–'); }))
                            .concat([num(q(quantidade(l)), true), num(lancamentos(l).length), num(u.numero(u.soma(l, 'hm')) + ' h'),
                                     num(v === null ? '–' : q(v)), { texto: u.data(ultimo(l)), classe: 'text-center' }]);
                    },
                    rodape: { el: '#tMaquinaTotal', celulas: function (l) {
                        var t = porTipo(l), v = por100(l);
                        return [{ texto: 'Total' }]
                            .concat(tipos.map(function (n) { return num(t[n] ? q(t[n]) : '–'); }))
                            .concat([num(q(quantidade(l))), num(u.numero(lancamentos(l).length, 0)), num(u.numero(u.soma(l, 'hm')) + ' h'),
                                     num(v === null ? '–' : q(v)), { texto: '' }]);
                    } }
                }]
            });
        });
    </script>
@endsection
