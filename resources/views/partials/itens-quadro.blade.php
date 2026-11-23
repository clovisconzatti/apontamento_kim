{{--
    Quadro de itens com quantidade (peças, lubrificantes): linhas lançadas + botão "+" que acrescenta outra.
    Variáveis: $grupo (nome enviado ao servidor, ex.: pecas), $titulo, $rotulo, $botao,
               $opcoes ([id => texto]) e $linhas ([['id' => , 'quantidade' => ], ...]; vazio = uma linha em branco).
    Opcional: $semQuantidade = true para uma lista só de itens (sem a coluna Quantidade);
              $comValores = true para as colunas Valor unitário e Valor total, com o total no rodapé.
    Precisa estar dentro de um elemento .info-form (estilo) e de um form cujo handler use coletaItens() do custom.js.
--}}
@php
    $semQuantidade = $semQuantidade ?? false;
    $comValores = $comValores ?? false;
@endphp
<div class="info-quadro">
    <h5>{{ $titulo }}</h5>
    <table>
        <thead>
            <tr>
                <th>{{ $rotulo }}</th>
                @unless ($semQuantidade)<th style="width: {{ $comValores ? '14%' : '30%' }};">Quantidade</th>@endunless
                @if ($comValores)<th style="width: 15%;">Valor un. (R$)</th><th style="width: 15%;">Valor total (R$)</th>@endif
                <th style="width: 1%;"></th>
            </tr>
        </thead>
        <tbody id="itens-{{ $grupo }}">
            @forelse ($linhas as $linha)
                @include('partials.item-linha', ['id' => $linha['id'], 'quantidade' => $linha['quantidade'], 'linha' => $linha])
            @empty
                @include('partials.item-linha', ['id' => null, 'quantidade' => null, 'linha' => null])
            @endforelse
        </tbody>
    </table>
    <template id="modelo-{{ $grupo }}">
        @include('partials.item-linha', ['id' => null, 'quantidade' => null, 'linha' => null])
    </template>
    <button type="button" class="btn btn-outline-success btn-sm item-adicionar" data-grupo="{{ $grupo }}" style="margin: 6px 8px 4px;">
        <span class="fas fa-plus"></span> {{ $botao }}
    </button>
    @if ($comValores)
        <b style="float: right; margin: 10px 8px 4px;">Total: <span id="total-{{ $grupo }}">–</span></b>
    @endif
    @if (!count($opcoes))
        <small class="text-muted d-block" style="margin: 0 8px;">Nenhum item cadastrado ainda.</small>
    @endif
</div>
