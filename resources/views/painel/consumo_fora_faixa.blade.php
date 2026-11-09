@extends('layouts.model')
@section('content')
    @php
        $n = fn($valor, $casas = 2) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
        $l2 = fn($valor) => number_format((float) $valor, 2, ',', '.');
        $faixa = fn($min, $max) => $min > 0 && $max > 0 ? $n($min).' a '.$n($max) : ($min > 0 ? 'mín. '.$n($min) : 'máx. '.$n($max));
        $totalMedidos = array_sum(array_column($medidos, 'n'));
        $totalFora = count($fora);
        $acima = count(array_filter($fora, fn($f) => $f['situacao'] === 'acima'));
        $abaixo = $totalFora - $acima;
        $selo = [
            'acima'  => ['cor' => '#c0392b', 'icone' => 'fa-arrow-up', 'texto' => 'Acima do máximo'],
            'abaixo' => ['cor' => '#b06f00', 'icone' => 'fa-arrow-down', 'texto' => 'Abaixo do mínimo'],
        ];
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações</small>
                <h3><i class="fas fa-exclamation-triangle"></i> Consumo Fora da Faixa</h3>
            </div>
            <a class="btn btn-success" href="{{ route('painel.consumo_fora_faixa.exportar') }}">
                <span class="fas fa-file-excel"></span> Exportar planilha
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                Abastecimentos em que a média de consumo (litros ÷ horas desde o abastecimento anterior, a mesma conta das Informações Diárias)
                ficou <b>abaixo do consumo mínimo</b> ou <b>acima do consumo máximo</b> do cadastro do equipamento.
                Entram só os equipamentos com <b>Controla Média = Sim</b> e com o consumo mínimo e/ou máximo preenchido.
            </p>
        </div>

        <div class="viz-tiles">
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-exclamation-triangle" style="color: #c0392b;"></i> Fora da faixa</div>
                <div class="viz-tile-valor">{{ number_format($totalFora, 0, ',', '.') }}</div>
                <div class="viz-tile-nota">
                    @if ($totalMedidos)
                        {{ $n($totalFora / $totalMedidos * 100, 1) }}% dos {{ number_format($totalMedidos, 0, ',', '.') }} abastecimentos medidos
                    @else
                        nenhum abastecimento medido no período
                    @endif
                </div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-arrow-up" style="color: #c0392b;"></i> Acima do máximo</div>
                <div class="viz-tile-valor">{{ number_format($acima, 0, ',', '.') }}</div>
                <div class="viz-tile-nota">abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-arrow-down" style="color: #b06f00;"></i> Abaixo do mínimo</div>
                <div class="viz-tile-valor">{{ number_format($abaixo, 0, ',', '.') }}</div>
                <div class="viz-tile-nota">abastecimentos</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tractor"></i> Máquinas</div>
                <div class="viz-tile-valor">{{ count($maquinas) }}</div>
                <div class="viz-tile-nota">com abastecimento fora da faixa, de {{ count($medidos) }} com consumo medido</div>
            </div>
            <div class="viz-tile">
                <div class="viz-tile-label"><i class="fas fa-tachometer-alt"></i> Média do período fora</div>
                <div class="viz-tile-valor">{{ count(array_filter($maquinas, fn($m) => $m['situacao'])) }}</div>
                <div class="viz-tile-nota">máquinas com a média do período fora da faixa</div>
            </div>
        </div>

        <div class="viz-card">
            <h4>Por máquina</h4>
            <p class="viz-sub">Da máquina que mais saiu da faixa (em % dos abastecimentos) para a que menos. A média do período considera todos os abastecimentos medidos da máquina.</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th class="text-right">Faixa do cadastro (L/h)</th>
                            <th class="text-right">Média do período</th>
                            <th class="text-right">Abastec. medidos</th>
                            <th class="text-right">Fora da faixa</th>
                            <th class="text-right">Acima</th>
                            <th class="text-right">Abaixo</th>
                            <th class="text-right">Menor média</th>
                            <th class="text-right">Maior média</th>
                            <th class="text-center">Último fora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maquinas as $m)
                            <tr>
                                <td><b>{{ $m['maquina'] }}</b></td>
                                <td class="num">{{ $faixa($m['minimo'], $m['maximo']) }}</td>
                                <td class="num">
                                    @if ($m['situacao'])
                                        <span class="viz-selo" style="color: {{ $selo[$m['situacao']]['cor'] }};" title="{{ $selo[$m['situacao']]['texto'] }}">
                                            <i class="fas {{ $selo[$m['situacao']]['icone'] }}"></i> {{ $l2($m['media']) }}
                                        </span>
                                    @else
                                        <span title="Dentro da faixa">{{ $l2($m['media']) }}</span>
                                    @endif
                                </td>
                                <td class="num">{{ $m['medidos'] }}</td>
                                <td class="num"><b>{{ $m['fora'] }}</b> <small class="text-muted">({{ $n($m['fora'] / $m['medidos'] * 100, 0) }}%)</small></td>
                                <td class="num">{{ $m['acima'] ?: '–' }}</td>
                                <td class="num">{{ $m['abaixo'] ?: '–' }}</td>
                                <td class="num">{{ $l2($m['menor']) }}</td>
                                <td class="num">{{ $l2($m['maior']) }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($m['ultimo'])) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted"><i class="fas fa-check-circle" style="color: #0ca30c;"></i> Nenhuma máquina com consumo fora da faixa para os filtros selecionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="viz-card">
            <h4>Abastecimentos fora da faixa</h4>
            <p class="viz-sub">Mais recentes primeiro. A diferença é em relação ao limite ultrapassado (mínimo ou máximo).</p>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th class="text-center">Data</th>
                            <th>Máquina</th>
                            <th>Fazenda</th>
                            <th>Colaborador</th>
                            <th class="text-right">Horímetro</th>
                            <th class="text-right">Horas</th>
                            <th class="text-right">Litros</th>
                            <th class="text-right">Média (L/h)</th>
                            <th class="text-right">Faixa (L/h)</th>
                            <th>Situação</th>
                            <th class="text-right">Diferença</th>
                            <th class="text-center">Formulário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fora as $f)
                            @php $s = $selo[$f['situacao']]; @endphp
                            <tr>
                                <td class="text-center">{{ date('d/m/Y', strtotime($f['data'])) }}</td>
                                <td><b>{{ $f['maquina'] }}</b></td>
                                <td>{{ $f['fazenda'] }}</td>
                                <td>{{ $f['colaborador'] }}</td>
                                <td class="num" title="Abastecimento anterior: {{ date('d/m/Y', strtotime($f['data_ref'])) }}, horímetro {{ $n($f['horimetro_ref']) }}">
                                    {{ $n($f['horimetro_ref']) }} → {{ $n($f['horimetro']) }}
                                </td>
                                <td class="num">{{ $n($f['horas']) }} h</td>
                                <td class="num">{{ $n($f['litros']) }} L</td>
                                <td class="num"><b>{{ $l2($f['media']) }}</b></td>
                                <td class="num">{{ $faixa($f['minimo'], $f['maximo']) }}</td>
                                <td><span class="viz-selo" style="color: {{ $s['cor'] }};"><i class="fas {{ $s['icone'] }}"></i> {{ $s['texto'] }}</span></td>
                                <td class="num" style="color: {{ $s['cor'] }};">
                                    {{ $f['diferenca'] > 0 ? '+' : '' }}{{ $l2($f['diferenca']) }} L/h
                                    <small>({{ $f['percentual'] > 0 ? '+' : '' }}{{ $n($f['percentual'], 0) }}%)</small>
                                </td>
                                <td class="text-center"><a class="btn btn-outline-info btn-sm" href="{{ route('informacao.formEdit', $f['id']) }}"><i class="far fa-edit"></i> Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center text-muted"><i class="fas fa-check-circle" style="color: #0ca30c;"></i> Nenhum abastecimento fora da faixa para os filtros selecionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
