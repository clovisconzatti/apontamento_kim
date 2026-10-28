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
                <h3><i class="fas fa-tractor"></i> Horas Máquina</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.horas_maquina.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tractor"></i> Horas de máquina</div>
                <div class="viz-tile-valor" id="kHoras">–</div>
                <div class="viz-tile-nota">horímetro final − inicial</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-truck"></i> Máquinas</div>
                <div class="viz-tile-valor" id="kMaquinas">–</div>
                <div class="viz-tile-nota">com horas no período</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-business-time"></i> Média por dia</div>
                <div class="viz-tile-valor" id="kMedia">–</div>
                <div class="viz-tile-nota">por máquina, nos dias trabalhados</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tachometer-alt"></i> Aproveitamento</div>
                <div class="viz-tile-valor" id="kAproveitamento">–</div>
                <div class="viz-tile-nota">horas de máquina ÷ horas homem</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-gas-pump"></i> Consumo médio</div>
                <div class="viz-tile-valor" id="kConsumo">–</div>
                <div class="viz-tile-nota">pelos abastecimentos</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Horas por máquina</h4>
                <p class="viz-sub">Horas de máquina no período, da maior para a menor.</p>
                <div class="viz-barras" id="gMaquina"></div>
            </div>
            <div class="viz-card">
                <h4>Horas por atividade</h4>
                <p class="viz-sub">Em que as máquinas trabalharam.</p>
                <div class="viz-barras" id="gAtividade"></div>
                <h4 style="margin-top: 22px;">Horas por fazenda</h4>
                <p class="viz-sub">Onde as máquinas trabalharam.</p>
                <div class="viz-barras" id="gFazenda"></div>
            </div>
        </div>

        <div class="viz-card">
            <h4>Evolução das horas de máquina</h4>
            <p class="viz-sub">Total {{ $escalas[$periodos['escala']] }} no período. Clique numa coluna para ver só aquele período.</p>
            <div id="gEvolucao"></div>
        </div>

        {{-- Planilha de acompanhamento --}}
        <div class="viz-card">
            <h4>Acompanhamento por máquina</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para filtrar por aquela máquina. Consumo: pelos abastecimentos (litros ÷ horas desde o abastecimento anterior).</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th class="text-right">Dias</th>
                            <th class="text-right">Horas máquina</th>
                            <th class="text-right">Média/dia</th>
                            <th class="text-right">Horas homem</th>
                            <th class="text-right">Aproveitamento</th>
                            <th class="text-right">Diesel</th>
                            <th class="text-right">Consumo</th>
                            <th class="text-right">Último horímetro</th>
                            <th class="text-center">Último dia</th>
                        </tr>
                    </thead>
                    <tbody id="tMaquina"></tbody>
                    <tfoot><tr id="tMaquinaTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>

        @if ($conferir->count())
            <div class="viz-card">
                <h4 style="color: #b3261e;"><i class="fas fa-exclamation-triangle"></i> Horímetros a conferir ({{ $conferir->count() }})</h4>
                <p class="viz-sub">Diferença negativa ou acima de 24h num único lançamento: provável erro de digitação. Ficaram fora dos totais.</p>
                <div class="viz-tabela-box">
                    <table class="viz-tabela">
                        <thead>
                            <tr>
                                <th class="text-center">Data</th><th>Máquina</th><th class="text-right">Horímetro inicial</th>
                                <th class="text-right">Horímetro final</th><th class="text-right">Diferença</th><th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($conferir as $c)
                                <tr>
                                    <td class="text-center">{{ date('d/m/Y', strtotime($c->data)) }}</td>
                                    <td>{{ $c->maquina }}</td>
                                    <td class="num">{{ $n($c->horimetro_inicial, 2) }}</td>
                                    <td class="num">{{ $n($c->horimetro_final, 2) }}</td>
                                    <td class="num">{{ $n($c->diferenca, 2) }}</td>
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
    <script>
        $(function () {
            var u = PainelInterativo.util;
            var h = function (v) { return u.numero(v) + ' h'; };
            var pct = function (v) { return v === null ? '–' : u.numero(v * 100, 0) + '%'; };
            var lh = function (v) { return v === null ? '–' : u.numero(v, 2) + ' L/h'; };
            var horas = function (l) { return u.soma(l, 'hm'); };
            var dias = function (l) { return u.distintos(l, function (f) { return f.maquina + '|' + f.d; }, function (f) { return f.hm > 0; }); };
            var aproveitamento = function (l) { return u.razao(u.soma(l, 'bm'), u.soma(l, 'bh')); };
            var consumo = function (l) { return u.razao(u.soma(l, 'il'), u.soma(l, 'ih')); };
            var ultimo = function (l, campo) { return l.reduce(function (m, f) { return f[campo] !== null && f[campo] > m ? f[campo] : m; }, ''); };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { maquina: 'Máquina', atividade: 'Atividade', fazenda: 'Fazenda', p: 'Período' },
                graficos: [
                    { el: '#gMaquina', dim: 'maquina', valor: horas, formato: h, limite: 15,
                      dica: function (c, l, v) { var d = dias(l); return c + '\n' + h(v) + ' em ' + d + (d === 1 ? ' dia' : ' dias') + '\nMédia: ' + h(d ? v / d : 0) + ' por dia\nConsumo: ' + lh(consumo(l)); } },
                    { el: '#gAtividade', dim: 'atividade', valor: horas, formato: h, limite: 10 },
                    { el: '#gFazenda', dim: 'fazenda', valor: horas, formato: h, limite: 10 },
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: horas, formato: h, chaves: @json($periodos['chaves']) }
                ],
                indicadores: [
                    { el: '#kHoras', valor: function (l) { return h(horas(l)); } },
                    { el: '#kMaquinas', valor: function (l) { return u.distintos(l, 'maquina', function (f) { return f.hm > 0; }); } },
                    { el: '#kMedia', valor: function (l) { var d = dias(l); return d ? h(horas(l) / d) : '–'; } },
                    { el: '#kAproveitamento', valor: function (l) { return pct(aproveitamento(l)); } },
                    { el: '#kConsumo', valor: function (l) { return lh(consumo(l)); } }
                ],
                tabelas: [{
                    el: '#tMaquina', dim: 'maquina', valor: horas, colunas: 10, vazio: 'Nenhum lançamento com horímetro para a seleção.',
                    celulas: function (c, l) {
                        var d = dias(l);
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' }, num(d), num(h(horas(l)), true), num(h(d ? horas(l) / d : 0)), num(h(u.soma(l, 'hh'))),
                            num(pct(aproveitamento(l))), num(u.numero(u.soma(l, 'di'), 0) + ' L'), num(lh(consumo(l))),
                            num(ultimo(l, 'hf') === '' ? '–' : u.numero(ultimo(l, 'hf'), 1)), { texto: u.data(ultimo(l, 'd')), classe: 'text-center' }
                        ];
                    },
                    rodape: { el: '#tMaquinaTotal', celulas: function (l) {
                        var d = dias(l);
                        return [{ texto: 'Total' }, num(u.numero(d, 0)), num(h(horas(l))), num(d ? h(horas(l) / d) : '–'), num(h(u.soma(l, 'hh'))),
                                num(pct(aproveitamento(l))), num(u.numero(u.soma(l, 'di'), 0) + ' L'), num(lh(consumo(l))), { texto: '' }, { texto: '' }];
                    } }
                }]
            });
        });
    </script>
@endsection
