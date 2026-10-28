@extends('layouts.model')
@section('content')
    @php
        $n = fn($valor, $casas = 2) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
        $pct = fn($fracao) => number_format($fracao * 100, 0, ',', '.').'%';
        $icones = ['vencida' => 'fas fa-exclamation-circle', 'proxima' => 'fas fa-exclamation-triangle', 'em_dia' => 'fas fa-check-circle'];
        $descricao = [
            'vencida' => 'Equipamentos que já passaram do horímetro da revisão. Programe a revisão o quanto antes.',
            'proxima' => 'Equipamentos nos últimos 10% do intervalo: quanto mais forte o amarelo, mais perto da revisão.',
            'em_dia'  => 'Equipamentos com folga até a próxima revisão.',
        ];

        // Cartão: vermelho quando vencida; amarelo cada vez mais forte na faixa de atenção
        $estiloCartao = function ($l) {
            if ($l['situacao'] === 'vencida') {
                return ['fundo' => 'background: #d03b3b; color: #fff; border-color: #d03b3b;', 'barra' => '#fff', 'trilho' => 'rgba(255,255,255,.3)', 'botao' => 'btn-light'];
            }
            if ($l['situacao'] === 'proxima') {
                return ['fundo' => 'background: rgba(250, 178, 25, '.round(0.18 + 0.62 * $l['intensidade'], 2).'); border-color: #e9b54a;', 'barra' => '#16241b', 'trilho' => 'rgba(22,36,27,.15)', 'botao' => 'btn-dark'];
            }
            return ['fundo' => '', 'barra' => '#0ca30c', 'trilho' => 'rgba(22,36,27,.1)', 'botao' => 'btn-outline-success'];
        };
    @endphp

    @include('partials.painel-estilo')

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações · Revisões</small>
                <h3><i class="{{ $icones[$situacao] }}"></i> {{ ['vencida' => 'Revisões vencidas', 'proxima' => 'Revisões próximas', 'em_dia' => 'Revisões em dia'][$situacao] }}</h3>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('painel.revisoes') }}">
                <span class="fas fa-arrow-left"></span> Voltar ao painel
            </a>
        </div>

        {{-- Abas para trocar de situação --}}
        <nav class="mb-3" aria-label="Situação das revisões">
            <div class="btn-group" role="group">
                @foreach ($abas as $endereco => $aba)
                    <a href="{{ route('painel.revisoes.lista', $endereco) }}"
                       class="btn {{ $aba['chave'] === $situacao ? 'btn-success' : 'btn-outline-success' }}"
                       {!! $aba['chave'] === $situacao ? 'aria-current="page"' : '' !!}>
                        <i class="{{ $icones[$aba['chave']] }}"></i> {{ $aba['nome'] }} <b>({{ $aba['quantidade'] }})</b>
                    </a>
                @endforeach
            </div>
        </nav>

        <p class="viz-sub">{{ $descricao[$situacao] }}</p>

        @if (count($linhas))
            <div class="viz-lista">
                @foreach ($linhas as $l)
                    @php $e = $estiloCartao($l); @endphp
                    <article class="viz-item" style="{{ $e['fundo'] }}">
                        <div class="viz-item-topo">
                            <div>
                                <h4 class="viz-item-nome">{{ $l['equipamento'] }}</h4>
                                <div class="viz-item-tipo">{{ $l['tipo'] }} · última em {{ date('d/m/Y', strtotime($l['data_revisao'])) }}{{ $l['nr_os'] ? ' · OS '.$l['nr_os'] : '' }}</div>
                            </div>
                            <span class="viz-selo"><i class="{{ $icones[$l['situacao']] }}"></i> {{ $situacoes[$l['situacao']] }}</span>
                        </div>

                        <div class="viz-item-destaque">
                            @if ($l['falta'] > 0)
                                <small>Faltam</small>{{ $n($l['falta']) }}
                            @else
                                <small>Passou da revisão</small>{{ $n(-$l['falta']) }}
                            @endif
                        </div>

                        <div>
                            <div class="viz-medidor" style="background: {{ $e['trilho'] }};" role="img" aria-label="{{ $pct($l['usado']) }} do intervalo usado">
                                <div style="width: {{ round(min(max($l['usado'], 0), 1) * 100, 2) }}%; background: {{ $e['barra'] }};"></div>
                            </div>
                            <small>{{ $pct($l['usado']) }} do intervalo de {{ $n($l['intervalo']) }}</small>
                        </div>

                        <div class="viz-item-dados">
                            <div><span>Atual</span><b>{{ $n($l['atual']) }}</b></div>
                            <div><span>Próxima</span><b>{{ $n($l['proxima']) }}</b></div>
                            <div><span>Apontado em</span><b>{{ date('d/m/Y', strtotime($l['data_atual'])) }}</b></div>
                        </div>
                        @if ($l['origem_atual'] === 'revisao')
                            <small>Horímetro da própria revisão: não há apontamento de horímetro mais novo.</small>
                        @endif

                        <a class="btn btn-sm {{ $e['botao'] }}"
                           href="{{ route('revisao.formAdd', ['equipamento' => $l['equipamento_id'], 'tipo_revisao' => $l['tipo_revisao_id'], 'horimetro_atual' => $l['atual']]) }}">
                            <span class="fas fa-plus"></span> Lançar revisão
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="viz-card viz-vazio">
                <i class="fas fa-check-circle"></i>
                Nenhum equipamento nesta situação.
            </div>
        @endif
    </div>
@endsection
