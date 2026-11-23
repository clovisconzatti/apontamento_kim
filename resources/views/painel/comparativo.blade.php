@extends('layouts.model')
@section('content')
    @php
        $escalas = ['dia' => 'por dia', 'semana' => 'por semana', 'mes' => 'por mês'];
        // Cor fixa por quantidade de cilindros (segue a quantidade, nunca a posição); cinza = sem cilindros no cadastro
        $paleta = [4 => '#2a78d6', 6 => '#eb6834'];
        $coresCil = ['' => '#9aa69e'];
        foreach ($cilindros as $c) {
            $coresCil[(string) $c] = $paleta[$c] ?? '#1baf7a';
        }
        $graficos = [
            ['gConsumo', 'Consumo médio', 'L/h entre abastecimentos. Menor é melhor.'],
            ['gHoras', 'Horas por máquina', 'Média de horas de cada máquina do grupo no período.'],
            ['gAproveitamento', 'Aproveitamento', 'Horas de máquina ÷ horas homem. Maior é melhor.'],
            ['gCusto', 'Custo de manutenção por hora', 'R$ por hora de máquina. Menor é melhor.'],
            ['gProdutividade', 'Produtividade', 'm² faturados por hora de máquina produzindo. Maior é melhor.'],
            ['gLm', 'Consumo por produção', 'Litros de diesel por m² faturado. Menor é melhor.'],
        ];
        $planilhas = [
            ['tGrupo', 'Comparativo por grupo', 'Grupo', 'Clique numa linha para ver só aquele grupo nos outros gráficos.'],
            ['tMaquina', 'Máquinas', 'Máquina', 'Máquinas da seleção atual, em ordem de grupo.'],
        ];
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-balance-scale"></i> Comparativo por Tipo e Cilindros</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.comparativo.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                Cada grupo reúne as máquinas do mesmo <b>tipo</b> e <b>cilindros</b> do cadastro de equipamentos. As contas são as mesmas dos outros
                painéis; as médias do grupo são ponderadas (total ÷ total). Cadastros de terceiros (placa TERCEIRO) ficam de fora.
                O custo de manutenção soma peças, serviços, terceiros, mão de obra e deslocamento (óleos lubrificantes não têm valor e não entram).
            </p>
        </div>

        @if ($semCilindros)
            <div class="viz-card" style="padding: 10px 18px; background: #fff4d6; border-color: #f0d995; font-size: 13px; color: #6b4a00;">
                <i class="fas fa-exclamation-triangle" style="color: #c98500;"></i>
                {{ count($semCilindros) == 1 ? '1 máquina está' : count($semCilindros).' máquinas estão' }} sem cilindros no cadastro:
                entram no grupo "sem cilindros" do seu tipo.
                <a href="{{ route('equipamento.listAll') }}"><b>Completar no cadastro →</b></a>
                <details style="margin-top: 4px;"><summary style="cursor: pointer;">Ver máquinas</summary>{{ implode(', ', $semCilindros) }}</details>
            </div>
        @endif

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-layer-group"></i> Grupos</div>
                <div class="viz-tile-valor" id="kGrupos">–</div>
                <div class="viz-tile-nota" id="kMaquinas">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-gas-pump"></i> Consumo médio</div>
                <div class="viz-tile-valor" id="kConsumo">–</div>
                <div class="viz-tile-nota">pelos abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tractor"></i> Horas por máquina</div>
                <div class="viz-tile-valor" id="kHoras">–</div>
                <div class="viz-tile-nota">média das máquinas com horas</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-stopwatch"></i> Produtividade</div>
                <div class="viz-tile-valor" id="kProdutividade">–</div>
                <div class="viz-tile-nota">m² por hora produzindo</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-wrench"></i> Manutenção por hora</div>
                <div class="viz-tile-valor" id="kCusto">–</div>
                <div class="viz-tile-nota">custo ÷ horas de máquina</div>
            </div>
        </div>

        <div class="viz-legenda" style="margin: 0 0 12px;">
            @foreach ($cilindros as $c)
                <span><i class="cor" style="background: {{ $coresCil[(string) $c] }};"></i>{{ $c }} cilindros</span>
            @endforeach
            @if ($semCilindros)
                <span><i class="cor" style="background: {{ $coresCil[''] }};"></i>Sem cilindros no cadastro</span>
            @endif
        </div>

        @foreach (array_chunk($graficos, 2) as $par)
            <div class="viz-grid2">
                @foreach ($par as [$id, $titulo, $sub])
                    <div class="viz-card">
                        <h4>{{ $titulo }}</h4>
                        <p class="viz-sub">{{ $sub }}</p>
                        <div class="viz-barras" id="{{ $id }}"></div>
                    </div>
                @endforeach
            </div>
        @endforeach

        {{-- Mesmo tipo, cilindros lado a lado --}}
        <div class="viz-card">
            <h4>Consumo (L/h) por tipo: cilindros lado a lado</h4>
            <p class="viz-sub">A diferença é da maior quantidade de cilindros em relação à menor, no mesmo tipo.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela" data-pdf-ignora="grupo,maquina">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            @foreach ($cilindros as $c)
                                <th class="text-right">{{ $c }} cilindros</th>
                            @endforeach
                            <th class="text-right">Sem cilindros</th>
                            <th class="text-right">Média do tipo</th>
                            <th class="text-right">Diferença</th>
                        </tr>
                    </thead>
                    <tbody id="tTipo"></tbody>
                </table>
            </div>
        </div>

        <div class="viz-card">
            <h4>Evolução do consumo médio</h4>
            <p class="viz-sub">L/h {{ $escalas[$periodos['escala']] }}. Selecione um grupo acima para ver só a evolução dele.</p>
            <div id="gEvolucao"></div>
        </div>

        @foreach ($planilhas as [$id, $titulo, $coluna, $sub])
            <div class="viz-card">
                <h4>{{ $titulo }}</h4>
                <p class="viz-sub">{{ $sub }}</p>
                <div class="viz-tabela-box">
                    <table class="viz-tabela">
                        <thead>
                            <tr>
                                <th>{{ $coluna }}</th>
                                <th>{{ $id === 'tGrupo' ? 'Máquinas' : 'Grupo' }}</th>
                                <th class="text-right">Horas máquina</th>
                                <th class="text-right">{{ $id === 'tGrupo' ? 'Horas/máquina' : 'Horas/dia' }}</th>
                                <th class="text-right">Aproveitamento</th>
                                <th class="text-right">Diesel</th>
                                <th class="text-right">Consumo</th>
                                <th class="text-right">Faturamento</th>
                                <th class="text-right">Produtividade</th>
                                <th class="text-right">L/m²</th>
                                <th class="text-right">Manutenção</th>
                                <th class="text-right">Manut./hora</th>
                            </tr>
                        </thead>
                        <tbody id="{{ $id }}"></tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @include('partials.painel-interativo')
    <script>
        $(function () {
            var u = PainelInterativo.util;
            var cores = @json($coresCil), cilindros = @json(array_map('strval', $cilindros));
            var sufixo = function (texto, casas) { return function (v) { return v === null ? '–' : u.numero(v, casas) + texto; }; };
            var h = sufixo(' h', 1), lh = sufixo(' L/h', 2), lm = sufixo(' L/m²', 3), m2h = sufixo(' m²/h', 1), m2 = sufixo(' m²', 0), litros = sufixo(' L', 0);
            var pct = function (v) { return v === null ? '–' : u.numero(v * 100, 0) + '%'; };
            var reais = function (v) { return v === null ? '–' : v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); };
            var reaisHora = function (v) { return v === null ? '–' : reais(v) + '/h'; };

            var horas = function (l) { return u.soma(l, 'hm'); };
            var maquinas = function (l) { return u.distintos(l, 'eq'); };
            var maquinasComHoras = function (l) { return u.distintos(l, 'eq', function (f) { return f.hm > 0; }); };
            var horasMaquina = function (l) { return u.razao(horas(l), maquinasComHoras(l)); };
            var dias = function (l) { return u.distintos(l, function (f) { return f.eq + '|' + f.d; }, function (f) { return f.hm > 0; }); };
            var consumo = function (l) { return u.razao(u.soma(l, 'il'), u.soma(l, 'ih')); };
            var aproveitamento = function (l) { return u.razao(u.soma(l, 'bm'), u.soma(l, 'bh')); };
            var produtividade = function (l) { return u.razao(u.soma(l, 'fm'), u.soma(l, 'hp')); };
            var consumoM2 = function (l) { return u.razao(u.soma(l, 'dp'), u.soma(l, 'fm')); };
            var custoHora = function (l) { return u.razao(u.soma(l, 'cr'), horas(l)); };

            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };
            var marca = function (l) { return '<i style="display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px;background:' + cores[l[0].cil] + '"></i>'; };
            var nMaq = function (n) { return n + (n === 1 ? ' máquina' : ' máquinas'); };

            // Um gráfico por medida, sempre na mesma ordem de grupos (tipo e cilindros), para comparar lado a lado
            var grafico = function (el, valor, formato, detalhe) {
                return { el: el, dim: 'grupo', valor: valor, formato: formato, ordem: 'chave',
                         cor: function (c, l) { return cores[l[0].cil]; },
                         dica: function (c, l, v) { return c + ' (' + nMaq(maquinas(l)) + ')\n' + formato(v) + '\n' + detalhe(l); } };
            };
            var medidas = function (l) {
                return [num(pct(aproveitamento(l))), num(litros(u.soma(l, 'di'))), num(lh(consumo(l)), true), num(m2(u.soma(l, 'fm'))),
                        num(m2h(produtividade(l))), num(lm(consumoM2(l))), num(reais(u.soma(l, 'c'))), num(reaisHora(custoHora(l)))];
            };

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { grupo: 'Grupo', tipo: 'Tipo', maquina: 'Máquina', p: 'Período' },
                graficos: [
                    grafico('#gConsumo', consumo, lh, function (l) { return litros(u.soma(l, 'il')) + ' ÷ ' + h(u.soma(l, 'ih')); }),
                    grafico('#gHoras', horasMaquina, h, function (l) { return h(horas(l)) + ' ÷ ' + nMaq(maquinasComHoras(l)); }),
                    grafico('#gAproveitamento', aproveitamento, pct, function (l) { return h(u.soma(l, 'bm')) + ' ÷ ' + h(u.soma(l, 'bh')); }),
                    grafico('#gCusto', custoHora, reaisHora, function (l) { return reais(u.soma(l, 'cr')) + ' ÷ ' + h(horas(l)); }),
                    grafico('#gProdutividade', produtividade, m2h, function (l) { return m2(u.soma(l, 'fm')) + ' ÷ ' + h(u.soma(l, 'hp')); }),
                    grafico('#gLm', consumoM2, lm, function (l) { return litros(u.soma(l, 'dp')) + ' ÷ ' + m2(u.soma(l, 'fm')); }),
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: consumo, formato: lh, chaves: @json($periodos['chaves']) }
                ],
                indicadores: [
                    { el: '#kGrupos', valor: function (l) { return u.distintos(l, 'grupo'); } },
                    { el: '#kMaquinas', valor: function (l) { return nMaq(maquinas(l)); } },
                    { el: '#kConsumo', valor: function (l) { return lh(consumo(l)); } },
                    { el: '#kHoras', valor: function (l) { return h(horasMaquina(l)); } },
                    { el: '#kProdutividade', valor: function (l) { return m2h(produtividade(l)); } },
                    { el: '#kCusto', valor: function (l) { return reaisHora(custoHora(l)); } }
                ],
                tabelas: [{
                    el: '#tTipo', dim: 'tipo', valor: consumo, ordem: 'chave', ignorar: ['grupo', 'maquina'], colunas: cilindros.length + 4,
                    vazio: 'Nenhum consumo medido para a seleção.',
                    celulas: function (c, l) {
                        var de = function (cil) { return consumo(l.filter(function (f) { return f.cil === cil; })); };
                        var valores = cilindros.map(de), com = valores.filter(function (v) { return v !== null; });
                        var diferenca = '–';
                        if (com.length > 1) {
                            var d = com[com.length - 1] - com[0], sinal = d > 0 ? '+' : '';
                            diferenca = sinal + u.numero(d, 2) + ' L/h (' + sinal + u.numero(d / com[0] * 100, 0) + '%)';
                        }
                        return [{ html: '<b>' + u.esc(c) + '</b>' }].concat(valores.map(function (v) { return num(lh(v)); }),
                               [num(lh(de(''))), num(lh(consumo(l))), num(diferenca, true)]);
                    }
                }, {
                    el: '#tGrupo', dim: 'grupo', colunas: 12, ordem: 'chave', vazio: 'Nenhum lançamento para a seleção.',
                    celulas: function (c, l) {
                        return [{ html: marca(l) + '<b>' + u.esc(c) + '</b>' }, { texto: maquinas(l) }, num(h(horas(l))), num(h(horasMaquina(l)))].concat(medidas(l));
                    }
                }, {
                    el: '#tMaquina', dim: 'maquina', colunas: 12, ordem: function (l) { return l[0].grupo + '|' + l[0].maquina; },
                    vazio: 'Nenhum lançamento para a seleção.',
                    celulas: function (c, l) {
                        return [{ html: marca(l) + '<b>' + u.esc(c) + '</b>' }, { texto: l[0].grupo }, num(h(horas(l))), num(h(u.razao(horas(l), dias(l))))].concat(medidas(l));
                    }
                }]
            });
        });
    </script>
@endsection
