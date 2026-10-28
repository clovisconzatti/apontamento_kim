@extends('layouts.model')
@section('content')
    @php
        $nl = fn($v) => number_format((float) $v, 0, ',', '.');
        $escalas = ['dia' => 'por dia', 'semana' => 'por semana', 'mes' => 'por mês'];
        $fim = $filtros['dtFinal'] ?: date('Y-m-d');
        $semInicial = $tanques->where('sem_saldo_inicial', true)->count();
    @endphp

    @include('partials.painel-estilo')
    <style>
        /* Tanques desenhados: o nível de combustível na cor da situação */
        .viz-root .tq-grade { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 14px; }
        .viz-root .tq { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border: 1px solid #e3e8e4; border-radius: 12px;
                        background: #fff; color: #16241b; text-decoration: none; text-align: center; }
        .viz-root .tq:hover { border-color: #9fb3a5; text-decoration: none; color: #16241b; box-shadow: 0 2px 10px rgba(13, 43, 24, .08); }
        .viz-root .tq-corpo { position: relative; width: 74px; height: 104px; border: 3px solid #5b6b60; border-radius: 16px 16px 10px 10px; overflow: hidden; background: #f3f5f2; }
        .viz-root .tq-corpo::before { content: ''; position: absolute; top: -9px; left: 50%; width: 22px; height: 9px; margin-left: -11px; background: #5b6b60; border-radius: 3px 3px 0 0; }
        .viz-root .tq-nivel { position: absolute; left: 0; right: 0; bottom: 0; transition: height .4s; }
        .viz-root .tq-nivel::after { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: rgba(255, 255, 255, .35); }
        .viz-root .tq-marca { position: absolute; left: 0; width: 10px; height: 1px; background: #9aa69e; }
        .viz-root .tq-pct { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: #16241b;
                            text-shadow: 0 0 3px #fff, 0 0 3px #fff, 0 0 3px #fff; }
        .viz-root .tq-nome { font-size: 13px; font-weight: 700; line-height: 1.2; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .viz-root .tq-saldo { font-size: 20px; font-weight: 700; line-height: 1.1; }
        .viz-root .tq-info { font-size: 11px; color: #4a5a4f; line-height: 1.35; }
        .viz-root .tq-aviso { font-size: 11px; font-weight: 700; color: #8a5a00; background: #fff4d6; border-radius: 4px; padding: 1px 6px; text-decoration: none; }
        .viz-root a.tq-aviso:hover { background: #ffe9ad; color: #4a3300; text-decoration: none; }
        .viz-root .tq-filtro { display: flex; flex-direction: column; align-items: center; gap: 6px; width: 100%; color: inherit; text-decoration: none; }
        .viz-root .tq-filtro:hover { text-decoration: none; color: inherit; }
        .viz-root .tq-acoes { display: flex; gap: 6px; margin-top: 2px; }
        .viz-root .tq-acoes .btn { font-size: 12px; padding: 2px 8px; }
    </style>

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-battery-three-quarters"></i> Saldo de Combustível</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.saldo_combustivel.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar extrato
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                <b>Como é calculado:</b> saldo inicial do cadastro do comboio + compras + transferências recebidas + sobras
                − transferências enviadas − abastecimentos (Informação Diária) − faltas, somando todos os lançamentos do tanque.
                Postos externos não entram.
                @if ($semInicial)
                    <br><span style="color: #8a5a00;"><i class="fas fa-exclamation-triangle"></i>
                    <b>{{ $semInicial }} {{ $semInicial == 1 ? 'tanque sem' : 'tanques sem' }} saldo inicial</b>: o saldo deles é só a soma dos lançamentos.
                    Informe a medição no <a href="{{ route('comboio.listAll') }}">cadastro do Comboio</a>.</span>
                @endif
            </p>
        </div>

        {{-- Tanques: clique para filtrar os gráficos e o extrato --}}
        <div class="viz-card">
            <h4>Saldo em {{ date('d/m/Y', strtotime($fim)) }}</h4>
            <p class="viz-sub">Nível de cada tanque em relação à capacidade. Clique num tanque para ver só ele nos gráficos e no extrato.</p>
            <div class="tq-grade">
                @foreach ($tanques as $t)
                    @php
                        $s = $situacoes[$t->situacao];
                        $nivel = is_null($t->ocupacao) ? 0 : min(max($t->ocupacao, 0), 1) * 100;
                    @endphp
                    <div class="tq">
                    <a href="#" class="tq-filtro" data-pi-dim="tanque" data-pi-chave="{{ $t->tanque }}" data-pi-rotulo="{{ $t->tanque }}"
                       title="{{ $t->tanque }}: {{ $s['nome'] }} — clique para filtrar">
                        <div class="tq-corpo" role="img" aria-label="{{ is_null($t->ocupacao) ? 'Sem capacidade cadastrada' : round(max($t->ocupacao, 0) * 100).'% cheio' }}">
                            <div class="tq-nivel" style="height: {{ round($nivel, 1) }}%; background: {{ $s['cor'] }};"></div>
                            <div class="tq-marca" style="bottom: 20%;" title="Nível baixo (20%)"></div>
                            <div class="tq-pct">{{ is_null($t->ocupacao) ? '–' : round($t->ocupacao * 100).'%' }}</div>
                        </div>
                        <div class="tq-nome">{{ $t->tanque }}</div>
                        <div class="tq-saldo" style="{{ $t->saldo < 0 ? 'color: #b3261e;' : '' }}">{{ $nl($t->saldo) }} L</div>
                        <div class="tq-info">
                            <i class="{{ $s['icone'] }}" style="color: {{ $s['cor'] }};"></i> {{ $s['nome'] }}<br>
                            {{ $t->capacidade ? 'capacidade '.$nl($t->capacidade).' L' : 'sem capacidade no cadastro' }}<br>
                            {{ $t->ultimo_lancamento ? 'último lançamento '.date('d/m/Y', strtotime($t->ultimo_lancamento)) : 'sem lançamentos' }}
                        </div>
                    </a>
                            <a class="tq-aviso" href="{{ route('comboio.formEdit', ['comboio' => $t->id, 'foco' => 'saldo_inicial']) }}">Ajustar o Saldo</a>
                        {{-- Atalhos: ajuste de saldo deste tanque e saldo inicial no cadastro do comboio --}}
                        <div class="tq-acoes">
                            <a class="btn btn-outline-success btn-sm" href="{{ route('ajuste_combustivel.formAdd', ['tanque' => $t->id]) }}"
                               title="Lançar ajuste de saldo (falta ou sobra) deste tanque" aria-label="Ajustar saldo de {{ $t->tanque }}">
                                <i class="fas fa-balance-scale"></i> Ajustar
                            </a>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('comboio.formEdit', ['comboio' => $t->id, 'foco' => 'saldo_inicial']) }}"
                               title="Informar o saldo inicial no cadastro do comboio" aria-label="Saldo inicial de {{ $t->tanque }}">
                                <i class="fas fa-flag"></i> Inicial
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="viz-selecao" id="painelSelecao" aria-live="polite"></div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-battery-three-quarters"></i> Saldo</div>
                <div class="viz-tile-valor" id="kSaldo">–</div>
                <div class="viz-tile-nota" id="kOcupacao">&nbsp;</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-arrow-down" style="color: #2a78d6;"></i> Entradas</div>
                <div class="viz-tile-valor" id="kEntradas">–</div>
                <div class="viz-tile-nota">compras, transferências recebidas e sobras</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-arrow-up" style="color: #eb6834;"></i> Saídas</div>
                <div class="viz-tile-valor" id="kSaidas">–</div>
                <div class="viz-tile-nota">abastecimentos, transferências enviadas e faltas</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tint"></i> Saída média por dia</div>
                <div class="viz-tile-valor" id="kMedia">–</div>
                <div class="viz-tile-nota">nos {{ $dias }} {{ $dias == 1 ? 'dia' : 'dias' }} do período</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-hourglass-half"></i> Autonomia</div>
                <div class="viz-tile-valor" id="kAutonomia">–</div>
                <div class="viz-tile-nota">dias que o saldo dura nesse ritmo</div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Evolução do saldo</h4>
                <p class="viz-sub">Saldo no fim de cada período ({{ $escalas[$periodos['escala']] }}), somando os tanques da seleção.</p>
                <div id="gSaldo"></div>
            </div>
            <div class="viz-card">
                <h4>Entradas e saídas por tipo</h4>
                <p class="viz-sub">Litros de cada tipo de movimento no período. Clique num tipo para ver só ele no extrato.</p>
                <div class="viz-barras" id="gTipo"></div>
                <div class="viz-legenda">
                    <span><i class="cor" style="background: #2a78d6;"></i><i class="fas fa-arrow-down"></i> Entrada</span>
                    <span><i class="cor" style="background: #eb6834;"></i><i class="fas fa-arrow-up"></i> Saída</span>
                </div>
            </div>
        </div>

        <div class="viz-grid2">
            <div class="viz-card">
                <h4>Movimento por tanque</h4>
                <p class="viz-sub">Entradas (azul) e saídas (laranja) de cada tanque no período.</p>
                <div class="viz-barras" id="gTanque"></div>
            </div>
            <div class="viz-card">
                <h4>Saídas no período</h4>
                <p class="viz-sub">Litros que saíram dos tanques {{ $escalas[$periodos['escala']] }}.</p>
                <div id="gSaidas"></div>
            </div>
        </div>

        {{-- Planilha por tanque --}}
        <div class="viz-card">
            <h4>Acompanhamento por tanque</h4>
            <p class="viz-sub">Segue a seleção dos gráficos; clique numa linha para filtrar por aquele tanque.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Tanque</th>
                            <th class="text-right">Saldo no início</th>
                            <th class="text-right">Entradas</th>
                            <th class="text-right">Saídas</th>
                            <th class="text-right">Saldo</th>
                            <th class="text-right">Capacidade</th>
                            <th class="text-right">Ocupação</th>
                            <th class="text-right">Saída/dia</th>
                            <th class="text-right">Autonomia</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody id="tTanque"></tbody>
                    <tfoot><tr id="tTanqueTotal" style="font-weight: 700; background: #eef2ee;"></tr></tfoot>
                </table>
            </div>
        </div>

        {{-- Extrato --}}
        <div class="viz-card">
            <h4>Extrato</h4>
            <p class="viz-sub" id="extratoNota">Cada movimento do período, do mais recente para o mais antigo, com o saldo do tanque logo depois dele.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th class="text-center">Data</th>
                            <th>Tanque</th>
                            <th>Movimento</th>
                            <th>Descrição</th>
                            <th class="text-right">Entrada</th>
                            <th class="text-right">Saída</th>
                            <th class="text-right">Saldo do tanque</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tExtrato"></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('partials.painel-interativo')
    <script>
        $(function () {
            var u = PainelInterativo.util;
            var cores = { Entrada: '#2a78d6', 'Saída': '#eb6834' };
            var tanques = @json($tanques->keyBy('tanque'));
            var situacoes = @json($situacoes);
            var dias = {{ $dias }};
            var editar = {
                informacao: '{{ url('informacao/editar') }}/', transferencia: '{{ url('transferencia/editar') }}/',
                compra_combustivel: '{{ url('compra_combustivel/editar') }}/', ajuste_combustivel: '{{ url('ajuste_combustivel/editar') }}/'
            };
            var LIMITE_EXTRATO = 300;

            var L = function (v) { return u.numero(v, 0) + ' L'; };
            var movs = function (l) { return l.filter(function (f) { return f.k === 'mov'; }); };
            var entradas = function (l) { return movs(l).reduce(function (t, f) { return t + (f.l > 0 ? f.l : 0); }, 0); };
            var saidas = function (l) { return movs(l).reduce(function (t, f) { return t + (f.l < 0 ? -f.l : 0); }, 0); };
            var fatos = @json($fatos);
            var painel = null;
            // Saldo = abertura + saldo inicial + movimentos. Não depende do tipo/sentido selecionado, e com um período
            // selecionado é o saldo no fim dele (inclui tudo antes). $tanque limita a um tanque (planilha).
            var baseSaldo = function (tanque) {
                var ignorar = tanque ? ['tipo', 'dir', 'p', 'tanque'] : ['tipo', 'dir', 'p'];
                var lista = painel ? painel.filtrarSem(ignorar) : fatos;
                var ate = painel && painel.sel.p;
                return lista.filter(function (f) { return (!ate || String(f.p) <= ate) && (!tanque || f.tanque === tanque); });
            };
            var saldo = function (tanque) { return u.soma(baseSaldo(tanque), 'l'); };
            var abertura = function (l) { return u.soma(l.filter(function (f) { return f.k === 'abertura'; }), 'l'); };
            var nomesTanques = function () { var n = {}; baseSaldo().forEach(function (f) { if (f.k === 'abertura') { n[f.tanque] = 1; } }); return Object.keys(n); };
            var capacidade = function () { return nomesTanques().reduce(function (t, n) { return t + (+tanques[n].capacidade || 0); }, 0); };
            var autonomia = function (l, tanque) { var m = saidas(l) / dias; return m > 0 ? saldo(tanque) / m : null; };
            var dAut = function (v) { return v === null ? '–' : (v < 1 ? '0 dias' : u.numero(v, 0) + (Math.round(v) === 1 ? ' dia' : ' dias')); };
            var num = function (texto, forte, estilo) { return { html: forte ? '<b>' + u.esc(texto) + '</b>' : u.esc(texto), classe: 'num', estilo: estilo }; };

            painel = new PainelInterativo({
                fatos: fatos,
                selecao: '#painelSelecao',
                rotulos: { tanque: 'Tanque', tipo: 'Movimento', dir: 'Sentido', p: 'Período' },
                graficos: [
                    { el: '#gSaldo', tipo: 'colunas', dim: 'p', valor: function (l) { return u.soma(l, 'l'); }, formato: L, acumular: true, ignorar: ['tipo', 'dir'], chaves: @json($periodos['chaves']) },
                    { el: '#gTipo', dim: 'tipo', valor: function (l) { return entradas(l) + saidas(l); }, formato: L,
                      cor: function (c, l) { var m = movs(l); return m.length ? cores[m[0].dir] : '#9aa69e'; },
                      dica: function (c, l, v) { var m = movs(l); return c + (m.length ? ' (' + m[0].dir.toLowerCase() + ')' : '') + '\n' + L(v) + ' em ' + m.length + ' lançamentos'; } },
                    { el: '#gTanque', dim: 'tanque', valor: function (l) { return entradas(l) + saidas(l); }, formato: L, limite: 20,
                      partes: function (l) { return [{ valor: entradas(l), cor: cores.Entrada }, { valor: saidas(l), cor: cores['Saída'] }]; },
                      dica: function (c, l) { return c + '\nEntradas: ' + L(entradas(l)) + '\nSaídas: ' + L(saidas(l)) + '\nSaldo: ' + L(saldo(c)); } },
                    { el: '#gSaidas', tipo: 'colunas', dim: 'p', valor: saidas, formato: L, corFixa: '#eb6834', chaves: @json($periodos['chaves']) }
                ],
                indicadores: [
                    { el: '#kSaldo', valor: function (l) { return L(saldo()); } },
                    { el: '#kOcupacao', valor: function (l) { var c = capacidade(); return c > 0 ? u.numero(Math.max(saldo(), 0) / c * 100, 0) + '% de ' + L(c) + ' de capacidade' : 'sem capacidade cadastrada'; } },
                    { el: '#kEntradas', valor: function (l) { return L(entradas(l)); } },
                    { el: '#kSaidas', valor: function (l) { return L(saidas(l)); } },
                    { el: '#kMedia', valor: function (l) { return L(saidas(l) / dias); } },
                    { el: '#kAutonomia', valor: function (l) { return dAut(autonomia(l)); } },
                    { el: '#extratoNota', valor: function (l) {
                        var n = movs(l).length;
                        return 'Cada movimento do período, do mais recente para o mais antigo, com o saldo do tanque logo depois dele'
                             + (n > LIMITE_EXTRATO ? ' (mostrando os ' + LIMITE_EXTRATO + ' mais recentes de ' + u.numero(n, 0) + '; selecione um tanque ou exporte o extrato para ver todos).' : '.');
                    } }
                ],
                tabelas: [{
                    el: '#tTanque', dim: 'tanque', colunas: 10, vazio: 'Nenhum tanque para a seleção.',
                    celulas: function (c, l) {
                        var t = tanques[c] || {}, sd = saldo(c), s = situacoes[t.situacao] || situacoes.ok;
                        var oc = t.capacidade > 0 ? u.numero(Math.max(sd, 0) / t.capacidade * 100, 0) + '%' : '–';
                        return [
                            { html: '<b>' + u.esc(c) + '</b>' + (t.sem_saldo_inicial ? '<span class="viz-origem" style="color:#8a5a00">sem saldo inicial</span>' : '') },
                            num(L(abertura(l))), num(L(entradas(l))), num(L(saidas(l))), num(L(sd), true, sd < 0 ? 'color:#b3261e' : ''),
                            num(t.capacidade ? L(t.capacidade) : '–'), num(oc), num(L(saidas(l) / dias)), num(dAut(autonomia(l, c))),
                            { html: '<span class="viz-selo" style="font-weight:600"><i class="' + s.icone + '" style="color:' + s.cor + '"></i> ' + s.nome + '</span>' }
                        ];
                    },
                    rodape: { el: '#tTanqueTotal', celulas: function (l) {
                        var c = capacidade();
                        return [{ texto: 'Total' }, num(L(abertura(l))), num(L(entradas(l))), num(L(saidas(l))), num(L(saldo())),
                                num(c ? L(c) : '–'), num(c ? u.numero(Math.max(saldo(), 0) / c * 100, 0) + '%' : '–'), num(L(saidas(l) / dias)),
                                num(dAut(autonomia(l))), { texto: '' }];
                    } }
                }, {
                    el: '#tExtrato', colunas: 8, vazio: 'Nenhum movimento para a seleção.',
                    linhas: function (l) {
                        return l.filter(function (f) { return f.k !== 'abertura'; })
                                .sort(function (a, b) { return a.d < b.d ? 1 : (a.d > b.d ? -1 : b.o - a.o); })
                                .slice(0, LIMITE_EXTRATO);
                    },
                    celulas: function (f) {
                        var entrada = f.l > 0 && f.k === 'mov', saida = f.l < 0;
                        var icone = entrada ? 'fas fa-arrow-down' : 'fas fa-arrow-up';
                        var cor = entrada ? cores.Entrada : cores['Saída'];
                        return [
                            { texto: u.data(f.d), classe: 'text-center' }, { texto: f.tanque },
                            { html: '<i class="' + icone + '" style="color:' + cor + '"></i> ' + u.esc(f.tipo) }, { texto: f.desc },
                            num(entrada ? L(f.l) : ''), num(saida ? L(-f.l) : ''),
                            num(L(f.s), true, f.s < 0 ? 'color:#b3261e' : ''),
                            { html: f.org && editar[f.org] ? '<a class="btn btn-outline-info btn-sm" href="' + editar[f.org] + f.id + '"><i class="far fa-edit"></i> Editar</a>' : '', classe: 'text-center' }
                        ];
                    }
                }]
            });
        });
    </script>
@endsection
