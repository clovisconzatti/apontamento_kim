@extends('layouts.model')
@section('content')
    @php
        $escalas = ['dia' => 'por dia', 'semana' => 'por semana', 'mes' => 'por mês'];
        // Mesmas cores dos outros painéis: azul = preventiva (programada), laranja = corretiva
        $cores = ['Preventiva' => '#2a78d6', 'Corretiva' => '#eb6834'];
        $coresComponentes = ['Terceiros' => '#2a78d6', 'Mão de obra' => '#1baf7a', 'Deslocamento' => '#eda100', 'Manutenção diária' => '#6250d6'];
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-wrench"></i> Manutenções</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.manutencao.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                <b>Como é calculado:</b> junta as <b>Manutenções</b> e as <b>Revisões</b>. Revisão é sempre preventiva; a manutenção segue o
                "Tipo da Manutenção" lançado (sem tipo conta como corretiva). Custo = terceiros + mão de obra (horas trabalhadas × custo hora)
                + deslocamento + manutenção diária (campo antigo da manutenção). <b>Peças e óleos lubrificantes ainda não entram.</b>
            </p>
        </div>

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-dollar-sign"></i> Custo total</div>
                <div class="viz-tile-valor" id="kTotal">–</div>
                <div class="viz-tile-nota" id="kQuantidade">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="cor" style="display: inline-block; width: 10px; height: 10px; border-radius: 3px; background: {{ $cores['Preventiva'] }};"></i> Preventiva</div>
                <div class="viz-tile-valor" id="kPreventiva">–</div>
                <div class="viz-tile-nota" id="kPreventivaNota">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="cor" style="display: inline-block; width: 10px; height: 10px; border-radius: 3px; background: {{ $cores['Corretiva'] }};"></i> Corretiva</div>
                <div class="viz-tile-valor" id="kCorretiva">–</div>
                <div class="viz-tile-nota" id="kCorretivaNota">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-stopwatch"></i> Horas em manutenção</div>
                <div class="viz-tile-valor" id="kHoras">–</div>
                <div class="viz-tile-nota">pela hora inicial e final lançadas</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-calculator"></i> Custo médio</div>
                <div class="viz-tile-valor" id="kMedio">–</div>
                <div class="viz-tile-nota">por lançamento</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Custo por máquina</h4>
                <p class="viz-sub">Da que mais custou para a que menos, dividido em preventiva e corretiva.</p>
                <div class="viz-barras" id="gMaquina"></div>
                <div class="viz-legenda">
                    @foreach ($cores as $nome => $cor)
                        <span><i class="cor" style="background: {{ $cor }};"></i>{{ $nome }}</span>
                    @endforeach
                </div>
            </div>
            <div class="viz-card">
                <h4>Preventiva × corretiva</h4>
                <p class="viz-sub">Custo de cada uma. Clique para ver só uma delas nos outros gráficos.</p>
                <div class="viz-barras" id="gTipo"></div>

                <h4 style="margin-top: 22px;">Composição do custo</h4>
                <p class="viz-sub">De onde vem o valor da seleção atual.</p>
                <div class="viz-barras" id="gComposicao"></div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Evolução do custo</h4>
                <p class="viz-sub">Custo {{ $escalas[$periodos['escala']] }}. Clique numa coluna para ver só aquele período.</p>
                <div id="gEvolucao"></div>
            </div>
            <div class="viz-card">
                <h4>Custo por tipo de manutenção / revisão</h4>
                <p class="viz-sub">Tipo lançado na manutenção ou tipo da revisão, dividido pelas partes do custo.</p>
                <div class="viz-barras" id="gMotivo"></div>
                <div class="viz-legenda">
                    @foreach ($coresComponentes as $nome => $cor)
                        <span><i class="cor" style="background: {{ $cor }};"></i>{{ $nome }}</span>
                    @endforeach
                </div>
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
                            <th class="text-right">Preventivas</th>
                            <th class="text-right">Custo preventiva</th>
                            <th class="text-right">Corretivas</th>
                            <th class="text-right">Custo corretiva</th>
                            <th class="text-right">% corretiva</th>
                            <th class="text-right">Horas</th>
                            <th class="text-right">Custo total</th>
                            <th class="text-center">Último lançamento</th>
                        </tr>
                    </thead>
                    <tbody id="tMaquina"></tbody>
                    <tfoot><tr id="tMaquinaTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>

        {{-- Lançamentos --}}
        <div class="viz-card">
            <h4>Lançamentos</h4>
            <p class="viz-sub">Cada manutenção e revisão da seleção atual, da mais recente para a mais antiga.</p>
            <div class="viz-tabela-box" style="max-height: 420px; overflow: auto;">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th class="text-center">Data</th>
                            <th>Origem</th>
                            <th>Tipo</th>
                            <th>Máquina</th>
                            <th>Tipo de manutenção / revisão</th>
                            <th>OS</th>
                            <th class="text-right">Horas</th>
                            <th class="text-right">Terceiros</th>
                            <th class="text-right">Mão de obra</th>
                            <th class="text-right">Deslocamento</th>
                            <th class="text-right">Manut. diária</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tLancamentos"></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('partials.painel-interativo')
    <script>
        $(function () {
            var u = PainelInterativo.util;
            var cores = @json($cores), coresComp = @json($coresComponentes);
            var campos = { 'Terceiros': 'ct', 'Mão de obra': 'ch', 'Deslocamento': 'cd', 'Manutenção diária': 'cm' };
            var fatos = @json($fatos);

            // A composição precisa de uma dimensão própria: cada lançamento vira uma linha por parte do custo com valor
            // (lançamento sem nenhum custo fica com uma linha de valor zero). Contagens e horas usam o lançamento ("k") uma vez só.
            var linhas = [];
            fatos.forEach(function (f) {
                var partes = Object.keys(campos).filter(function (c) { return f[campos[c]] > 0; });
                (partes.length ? partes : ['Terceiros']).forEach(function (c) {
                    linhas.push($.extend({}, f, { comp: c, v: f[campos[c]] || 0 }));
                });
            });
            var unicos = function (l) {
                var vistos = {};
                return l.filter(function (f) { if (vistos[f.k]) { return false; } vistos[f.k] = true; return true; });
            };

            var reais = function (v) { return v === null ? '–' : v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 }); };
            var reais2 = function (v) { return v === null ? '–' : v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); };
            var custo = function (l) { return u.soma(l, 'v'); };
            var qtd = function (l) { return unicos(l).length; };
            var deTipo = function (l, t) { return l.filter(function (f) { return f.tipo === t; }); };
            var pct = function (a, b) { return b > 0 ? u.numero(a / b * 100, 0) + '%' : '–'; };
            var porTipo = function (l) { return ['Preventiva', 'Corretiva'].map(function (t) { return { valor: custo(deTipo(l, t)), cor: cores[t] }; }); };
            var porComp = function (l) { return Object.keys(campos).map(function (c) { return { valor: custo(l.filter(function (f) { return f.comp === c; })), cor: coresComp[c] }; }); };
            var ultimo = function (l) { return l.reduce(function (m, f) { return f.d > m ? f.d : m; }, ''); };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };
            var lanc = function (n) { return u.numero(n, 0) + (n === 1 ? ' lançamento' : ' lançamentos'); };
            var dicaTipos = function (c, l, v) {
                var p = deTipo(l, 'Preventiva'), k = deTipo(l, 'Corretiva');
                return c + '\n' + reais2(v) + '\nPreventiva: ' + reais2(custo(p)) + ' (' + lanc(qtd(p)) + ')\nCorretiva: ' + reais2(custo(k)) + ' (' + lanc(qtd(k)) + ')';
            };
            var horas = function (l) { var h = u.soma(unicos(l), 'h'); var m = Math.round(h * 60); return Math.floor(m / 60) + 'h' + ('0' + (m % 60)).slice(-2); };

            new PainelInterativo({
                fatos: linhas,
                selecao: '#painelSelecao',
                rotulos: { maquina: 'Máquina', tipo: 'Tipo', comp: 'Parte do custo', motivo: 'Tipo de manutenção / revisão', p: 'Período' },
                graficos: [
                    { el: '#gMaquina', dim: 'maquina', valor: custo, formato: reais, limite: 15, partes: porTipo, dica: dicaTipos },
                    { el: '#gTipo', dim: 'tipo', valor: custo, formato: reais, cor: function (c) { return cores[c]; },
                      dica: function (c, l, v) { return c + '\n' + reais2(v) + '\n' + lanc(qtd(l)); } },
                    { el: '#gComposicao', dim: 'comp', valor: custo, formato: reais, cor: function (c) { return coresComp[c]; },
                      dica: function (c, l, v) { return c + '\n' + reais2(v); } },
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: custo, formato: reais, chaves: @json($periodos['chaves']) },
                    { el: '#gMotivo', dim: 'motivo', valor: custo, formato: reais, limite: 12, partes: porComp,
                      dica: function (c, l, v) { return c + '\n' + reais2(v) + ' · ' + lanc(qtd(l)); } }
                ],
                indicadores: [
                    { el: '#kTotal', valor: function (l) { return reais(custo(l)); } },
                    { el: '#kQuantidade', valor: function (l) { return lanc(qtd(l)); } },
                    { el: '#kPreventiva', valor: function (l) { return reais(custo(deTipo(l, 'Preventiva'))); } },
                    { el: '#kPreventivaNota', valor: function (l) { var p = deTipo(l, 'Preventiva'); return pct(custo(p), custo(l)) + ' do custo · ' + lanc(qtd(p)); } },
                    { el: '#kCorretiva', valor: function (l) { return reais(custo(deTipo(l, 'Corretiva'))); } },
                    { el: '#kCorretivaNota', valor: function (l) { var k = deTipo(l, 'Corretiva'); return pct(custo(k), custo(l)) + ' do custo · ' + lanc(qtd(k)); } },
                    { el: '#kHoras', valor: horas },
                    { el: '#kMedio', valor: function (l) { return reais(u.razao(custo(l), qtd(l))); } }
                ],
                tabelas: [{
                    el: '#tMaquina', dim: 'maquina', valor: function (l) { return custo(l) || qtd(l) * 0.0001; }, colunas: 9,
                    vazio: 'Nenhuma manutenção ou revisão para a seleção.',
                    celulas: function (c, l) {
                        var p = deTipo(l, 'Preventiva'), k = deTipo(l, 'Corretiva');
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' }, num(u.numero(qtd(p), 0)), num(reais2(custo(p))), num(u.numero(qtd(k), 0)), num(reais2(custo(k))),
                            num(pct(custo(k), custo(l))), num(horas(l)), num(reais2(custo(l)), true), { texto: u.data(ultimo(l)), classe: 'text-center' }
                        ];
                    },
                    rodape: { el: '#tMaquinaTotal', celulas: function (l) {
                        var p = deTipo(l, 'Preventiva'), k = deTipo(l, 'Corretiva');
                        return [{ texto: 'Total' }, num(u.numero(qtd(p), 0)), num(reais2(custo(p))), num(u.numero(qtd(k), 0)), num(reais2(custo(k))),
                                num(pct(custo(k), custo(l))), num(horas(l)), num(reais2(custo(l))), { texto: '' }];
                    } }
                }, {
                    el: '#tLancamentos', dim: null, colunas: 12, vazio: 'Nenhum lançamento para a seleção.',
                    // Uma linha por lançamento, mais recente primeiro
                    linhas: function (l) { return unicos(l).sort(function (a, b) { return a.d < b.d ? 1 : a.d > b.d ? -1 : 0; }); },
                    celulas: function (f) {
                        var r = function (v) { return num(v > 0 ? reais2(v) : ''); };
                        return [
                            { texto: u.data(f.d), classe: 'text-center' }, { texto: f.origem }, { html: '<i class="cor" style="display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px;background:' + cores[f.tipo] + '"></i>' + u.esc(f.tipo) },
                            { texto: f.maquina }, { texto: f.motivo }, { texto: f.os }, num(f.h > 0 ? horas([f]) : ''),
                            r(f.ct), r(f.ch), r(f.cd), r(f.cm), num(reais2(f.total), true)
                        ];
                    }
                }]
            });
        });
    </script>
@endsection
