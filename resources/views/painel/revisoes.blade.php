@extends('layouts.model')
@section('content')
    @php
        // 12.345,5 (sem zeros sobrando à direita)
        $n = fn($valor, $casas = 2) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
        $pct = fn($fracao) => number_format($fracao * 100, 0, ',', '.').'%';

        // Gráfico de uso do intervalo: escala até pelo menos 120% para a faixa e o limite aparecerem
        $escala = max(1.2, min(1.5, collect($linhas)->max('usado') ?? 0));
        $posicao = fn($fracao) => round(min(max($fracao, 0), $escala) / $escala * 100, 2);

        $maxMes = max(1, collect($porMes)->max('quantidade'));
        $mesMaior = collect($porMes)->search(fn($m) => $m['quantidade'] === $maxMes);

        $icones = ['vencida' => 'fas fa-exclamation-circle', 'proxima' => 'fas fa-exclamation-triangle', 'em_dia' => 'fas fa-check-circle'];

        // Linha da planilha: amarelo cada vez mais forte na faixa de atenção; vermelho quando vencida
        $fundoLinha = function ($l) {
            if ($l['situacao'] === 'vencida') {
                return 'background: #d03b3b; color: #fff;';
            }
            if ($l['situacao'] === 'proxima') {
                return 'background: rgba(250, 178, 25, '.round(0.18 + 0.62 * $l['intensidade'], 2).');';
            }
            return '';
        };
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-calendar-check"></i> Revisões</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.revisoes.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        {{-- Indicadores (clique para abrir a lista só daquela situação) --}}
        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-truck"></i> Equipamentos acompanhados</div>
                <div class="viz-tile-valor">{{ $resumo['acompanhados'] }}</div>
                <div class="viz-tile-nota">com revisão lançada</div>
            </div>
            <a class="viz-tile" href="{{ route('painel.revisoes.lista', 'vencidas') }}" title="Ver só as revisões vencidas">
                <div class="viz-tile-label"><i class="{{ $icones['vencida'] }}" style="color: var(--critical);"></i> Vencidas</div>
                <div class="viz-tile-valor">{{ $resumo['vencida'] }}</div>
                <div class="viz-tile-nota">passaram do horímetro da revisão</div>
                <span class="viz-tile-ver">Ver lista →</span>
            </a>
            <a class="viz-tile" href="{{ route('painel.revisoes.lista', 'proximas') }}" title="Ver só as revisões próximas">
                <div class="viz-tile-label"><i class="{{ $icones['proxima'] }}" style="color: #c98500;"></i> Próximas</div>
                <div class="viz-tile-valor">{{ $resumo['proxima'] }}</div>
                <div class="viz-tile-nota">nos últimos 10% do intervalo</div>
                <span class="viz-tile-ver">Ver lista →</span>
            </a>
            <a class="viz-tile" href="{{ route('painel.revisoes.lista', 'em-dia') }}" title="Ver só as revisões em dia">
                <div class="viz-tile-label"><i class="{{ $icones['em_dia'] }}" style="color: var(--good);"></i> Em dia</div>
                <div class="viz-tile-valor">{{ $resumo['em_dia'] }}</div>
                <div class="viz-tile-nota">com folga até a revisão</div>
                <span class="viz-tile-ver">Ver lista →</span>
            </a>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="far fa-question-circle"></i> Sem revisão lançada</div>
                <div class="viz-tile-valor">{{ is_null($resumo['sem_revisao']) ? '–' : $resumo['sem_revisao'] }}</div>
                <div class="viz-tile-nota">{{ is_null($resumo['sem_revisao']) ? 'não se aplica com filtro' : 'equipamentos ativos' }}</div>
            </div>
        </div>

        @if (count($linhas))
            {{-- Uso do intervalo --}}
            <div class="viz-card">
                <h4>Uso do intervalo da revisão</h4>
                <p class="viz-sub">Quanto cada equipamento já rodou do intervalo entre a última revisão e a próxima. A faixa amarela são os últimos 10%; a linha de 100% é a revisão.</p>
                <div class="viz-barras">
                    @foreach ($linhas as $l)
                        @php
                            $cor = ['vencida' => 'var(--critical)', 'proxima' => 'var(--warning)', 'em_dia' => 'var(--good)'][$l['situacao']];
                            $dica = $l['equipamento']."\n".$situacoes[$l['situacao']].' · '.$pct($l['usado']).' do intervalo'
                                  ."\nAtual: ".$n($l['atual']).' · Próxima: '.$n($l['proxima'])
                                  ."\n".($l['falta'] > 0 ? 'Faltam '.$n($l['falta']) : 'Passou '.$n(-$l['falta']));
                        @endphp
                        <div class="rotulo" title="{{ $l['equipamento'] }}">{{ $l['equipamento'] }}</div>
                        <div class="viz-trilho" data-tip="{{ $dica }}">
                            <div class="viz-faixa" style="left: {{ $posicao(1 - 0.10) }}%; width: {{ $posicao(1) - $posicao(1 - 0.10) }}%;"></div>
                            <div class="viz-marca" style="left: {{ $posicao(1) }}%;"></div>
                            <div class="viz-barra" style="width: {{ $posicao($l['usado']) }}%; background: {{ $cor }};"></div>
                        </div>
                        <div class="viz-valor">{{ $pct($l['usado']) }}</div>
                    @endforeach
                    <div></div>
                    <div class="viz-eixo">
                        <span style="left: 0%; transform: none;">0%</span>
                        <span style="left: {{ $posicao(0.5) }}%;">50%</span>
                        <span style="left: {{ $posicao(1) }}%;">100%</span>
                    </div>
                    <div></div>
                </div>
                <div class="viz-legenda">
                    <span><i class="cor" style="background: var(--good);"></i><i class="{{ $icones['em_dia'] }}"></i> Em dia</span>
                    <span><i class="cor" style="background: var(--warning);"></i><i class="{{ $icones['proxima'] }}"></i> Próxima (últimos 10%)</span>
                    <span><i class="cor" style="background: var(--critical);"></i><i class="{{ $icones['vencida'] }}"></i> Vencida</span>
                </div>
            </div>
        @endif

        {{-- Revisões por mês --}}
        <div class="viz-card">
            <h4>Revisões lançadas por mês</h4>
            <p class="viz-sub">Últimos 12 meses{{ $filtros['equipamento'] || $filtros['tipo_revisao'] ? ', com os filtros atuais' : '' }}.</p>
            <div class="viz-colunas">
                @foreach ($porMes as $i => $mes)
                    <div class="viz-coluna" data-tip="{{ $mes['rotulo'] }}: {{ $mes['quantidade'] }} {{ $mes['quantidade'] == 1 ? 'revisão' : 'revisões' }}">
                        @if ($mes['quantidade'] && ($i === $mesMaior || $i === count($porMes) - 1))
                            <span class="cap">{{ $mes['quantidade'] }}</span>
                        @endif
                        <div class="fill" style="height: {{ round($mes['quantidade'] / $maxMes * 85, 2) }}%;"></div>
                    </div>
                @endforeach
            </div>
            <div class="viz-meses">
                @foreach ($porMes as $mes)
                    <span>{{ $mes['rotulo'] }}</span>
                @endforeach
            </div>
        </div>

        {{-- Planilha de acompanhamento --}}
        <div class="viz-card">
            <h4>Acompanhamento das revisões</h4>
            <p class="viz-sub">Uma linha por equipamento, com a última revisão lançada. Amarelo: dentro dos últimos 10% do intervalo, mais forte quanto mais perto. Vermelho: passou da revisão.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Equipamento</th>
                            <th>Tipo da revisão</th>
                            <th class="text-center">Data da última revisão</th>
                            <th class="text-right">Hora/KM atual</th>
                            <th class="text-right">Hora/KM próx. revisão</th>
                            <th class="text-right">Falta</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($linhas as $l)
                            <tr style="{{ $fundoLinha($l) }}">
                                <td><b>{{ $l['equipamento'] }}</b></td>
                                <td>{{ $l['tipo'] }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($l['data_revisao'])) }}</td>
                                <td class="num">
                                    {{ $n($l['atual']) }}
                                    <span class="viz-origem">
                                        {{ $l['origem_atual'] === 'revisao' ? 'da revisão (sem apontamento de horímetro mais novo)' : 'apontado em '.date('d/m/Y', strtotime($l['data_atual'])) }}
                                    </span>
                                </td>
                                <td class="num">{{ $n($l['proxima']) }}</td>
                                <td class="num"><b>{{ $l['falta'] > 0 ? $n($l['falta']) : '−'.$n(-$l['falta']) }}</b></td>
                                <td><span class="viz-selo"><i class="{{ $icones[$l['situacao']] }}"></i> {{ $situacoes[$l['situacao']] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Nenhuma revisão encontrada para os filtros selecionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="viz-tip" id="vizTip" hidden></div>
    <script>
        // Dica ao passar o mouse sobre barras e colunas
        $(function () {
            var dica = $('#vizTip');
            $(document).on('mousemove', '[data-tip]', function (e) {
                dica.text($(this).attr('data-tip')).prop('hidden', false)
                    .css({ left: Math.min(e.clientX + 14, window.innerWidth - 300), top: e.clientY + 14 });
            }).on('mouseleave', '[data-tip]', function () {
                dica.prop('hidden', true);
            });
        });
    </script>
@endsection
