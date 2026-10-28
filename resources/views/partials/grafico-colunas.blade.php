{{--
    Gráfico de colunas para evolução no tempo (Painel de Operações). Precisa de 'partials.painel-estilo' na página.
    Variáveis:
      $itens   [['rotulo' => , 'valor' => , 'dica' => (opcional)], ...]  na ordem do tempo
      $formato fn($valor) => texto do valor
      $cor     (opcional) cor das colunas
    Só o maior valor e o último período ganham número em cima; o resto aparece ao passar o mouse.
--}}
@php
    $cor = $cor ?? 'var(--series-1)';
    $itensGrafico = collect($itens)->values();
    $total = max(1, $itensGrafico->count());
    $maximo = max(0.0001, $itensGrafico->max('valor'));
    $maior = $itensGrafico->search(fn($i) => $i['valor'] == $maximo);
    $passo = (int) ceil($total / 12); // no máximo ~12 rótulos no eixo
    $colunas = 'grid-template-columns: repeat('.$total.', minmax(0, 1fr));';
@endphp
<div class="viz-colunas" style="{{ $colunas }}">
    @foreach ($itensGrafico as $i => $item)
        <div class="viz-coluna" data-tip="{{ ($item['dica'] ?? $item['rotulo']).': '.$formato($item['valor']) }}">
            @if ($item['valor'] > 0 && ($i === $maior || $i === $total - 1))
                <span class="cap">{{ $formato($item['valor']) }}</span>
            @endif
            <div class="fill" style="height: {{ round($item['valor'] / $maximo * 85, 2) }}%; background: {{ $cor }};"></div>
        </div>
    @endforeach
</div>
<div class="viz-meses" style="{{ $colunas }}">
    @foreach ($itensGrafico as $i => $item)
        <span>{{ $i % $passo === 0 ? $item['rotulo'] : '' }}</span>
    @endforeach
</div>
