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
                <h3><i class="fas fa-user-clock"></i> Horas Homem</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.horas_homem.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-user-clock"></i> Horas homem</div>
                <div class="viz-tile-valor" id="kHoras">–</div>
                <div class="viz-tile-nota">hora inicial até a final</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-users"></i> Colaboradores</div>
                <div class="viz-tile-valor" id="kColaboradores">–</div>
                <div class="viz-tile-nota">com horas no período</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="far fa-calendar-check"></i> Dias trabalhados</div>
                <div class="viz-tile-valor" id="kDias">–</div>
                <div class="viz-tile-nota">somando todos os colaboradores</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-business-time"></i> Média por dia</div>
                <div class="viz-tile-valor" id="kMedia">–</div>
                <div class="viz-tile-nota">por colaborador</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tachometer-alt"></i> Aproveitamento</div>
                <div class="viz-tile-valor" id="kAproveitamento">–</div>
                <div class="viz-tile-nota">horas de máquina ÷ horas homem</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Horas por colaborador</h4>
                <p class="viz-sub">Horas homem no período, do maior para o menor.</p>
                <div class="viz-barras" id="gColaborador"></div>
            </div>
            <div class="viz-card">
                <h4>Horas por fazenda</h4>
                <p class="viz-sub">Onde as horas homem foram trabalhadas.</p>
                <div class="viz-barras" id="gFazenda"></div>
                <h4 style="margin-top: 22px;">Horas por máquina</h4>
                <p class="viz-sub">Em que máquina os colaboradores trabalharam.</p>
                <div class="viz-barras" id="gMaquina"></div>
            </div>
        </div>

        <div class="viz-card">
            <h4>Evolução das horas homem</h4>
            <p class="viz-sub">Total {{ $escalas[$periodos['escala']] }} no período. Clique numa coluna para ver só aquele período.</p>
            <div id="gEvolucao"></div>
        </div>

        {{-- Planilha de acompanhamento --}}
        <div class="viz-card">
            <h4>Acompanhamento por colaborador</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para filtrar os gráficos por aquele colaborador. Aproveitamento: horas de máquina ÷ horas homem, nos lançamentos que têm as duas.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th class="text-right">Dias</th>
                            <th class="text-right">Horas homem</th>
                            <th class="text-right">Média/dia</th>
                            <th class="text-right">Horas máquina</th>
                            <th class="text-right">Aproveitamento</th>
                            <th class="text-right">Máquinas</th>
                            <th class="text-right">Fazendas</th>
                            <th class="text-center">Último dia</th>
                        </tr>
                    </thead>
                    <tbody id="tColaborador"></tbody>
                    <tfoot><tr id="tColaboradorTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>

        @if ($conferir->count())
            <div class="viz-card">
                <h4 style="color: #b3261e;"><i class="fas fa-exclamation-triangle"></i> Jornadas a conferir ({{ $conferir->count() }})</h4>
                <p class="viz-sub">Mais de 16h (ou zero) num único lançamento: provável erro de digitação na hora. Ficaram fora dos totais.</p>
                <div class="viz-tabela-box">
                    <table class="viz-tabela">
                        <thead>
                            <tr>
                                <th class="text-center">Data</th><th>Colaborador</th><th>Máquina</th>
                                <th class="text-center">Hora inicial</th><th class="text-center">Hora final</th><th class="text-right">Duração</th><th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($conferir as $c)
                                <tr>
                                    <td class="text-center">{{ date('d/m/Y', strtotime($c->data)) }}</td>
                                    <td>{{ $c->colaborador }}</td>
                                    <td>{{ $c->equipamento }}</td>
                                    <td class="text-center">{{ substr($c->hora_inicial, 0, 5) }}</td>
                                    <td class="text-center">{{ substr($c->hora_final, 0, 5) }}</td>
                                    <td class="num">{{ $n($c->duracao) }} h</td>
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
            var horas = function (l) { return u.soma(l, 'hh'); };
            var dias = function (l) { return u.distintos(l, function (f) { return f.colaborador + '|' + f.d; }); };
            var aproveitamento = function (l) { return u.razao(u.soma(l, 'bm'), u.soma(l, 'bh')); };
            var ultimo = function (l) { return l.reduce(function (m, f) { return f.d > m ? f.d : m; }, ''); };
            var num = function (texto, forte) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num' }; };

            new PainelInterativo({
                fatos: @json($fatos),
                selecao: '#painelSelecao',
                rotulos: { colaborador: 'Colaborador', fazenda: 'Fazenda', maquina: 'Máquina', p: 'Período' },
                graficos: [
                    { el: '#gColaborador', dim: 'colaborador', valor: horas, formato: h, limite: 15,
                      dica: function (c, l, v) { var d = dias(l); return c + '\n' + h(v) + ' em ' + d + (d === 1 ? ' dia' : ' dias') + '\nMédia: ' + h(v / d) + ' por dia'; } },
                    { el: '#gFazenda', dim: 'fazenda', valor: horas, formato: h, limite: 10 },
                    { el: '#gMaquina', dim: 'maquina', valor: horas, formato: h, limite: 10 },
                    { el: '#gEvolucao', tipo: 'colunas', dim: 'p', valor: horas, formato: h, chaves: @json($periodos['chaves']) }
                ],
                indicadores: [
                    { el: '#kHoras', valor: function (l) { return h(horas(l)); } },
                    { el: '#kColaboradores', valor: function (l) { return u.distintos(l, 'colaborador'); } },
                    { el: '#kDias', valor: function (l) { return u.numero(dias(l), 0); } },
                    { el: '#kMedia', valor: function (l) { var d = dias(l); return d ? h(horas(l) / d) : '–'; } },
                    { el: '#kAproveitamento', valor: function (l) { return pct(aproveitamento(l)); } }
                ],
                tabelas: [{
                    el: '#tColaborador', dim: 'colaborador', valor: horas, colunas: 9, vazio: 'Nenhum lançamento com horas para a seleção.',
                    celulas: function (c, l) {
                        var d = dias(l);
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' }, num(d), num(h(horas(l)), true), num(h(horas(l) / d)), num(h(u.soma(l, 'hm'))),
                            num(pct(aproveitamento(l))), num(u.distintos(l, 'maquina')), num(u.distintos(l, 'fazenda')),
                            { texto: u.data(ultimo(l)), classe: 'text-center' }
                        ];
                    },
                    rodape: { el: '#tColaboradorTotal', celulas: function (l) {
                        var d = dias(l);
                        return [{ texto: 'Total' }, num(u.numero(d, 0)), num(h(horas(l))), num(d ? h(horas(l) / d) : '–'),
                                num(h(u.soma(l, 'hm'))), num(pct(aproveitamento(l))), { texto: '' }, { texto: '' }, { texto: '' }];
                    } }
                }]
            });
        });
    </script>
@endsection
