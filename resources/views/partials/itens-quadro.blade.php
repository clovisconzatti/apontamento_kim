{{--
    Quadro de itens com quantidade (peças, lubrificantes): linhas lançadas + botão "+" que acrescenta outra.
    Variáveis: $grupo (nome enviado ao servidor, ex.: pecas), $titulo, $rotulo, $botao,
               $opcoes ([id => texto]) e $linhas ([['id' => , 'quantidade' => ], ...]; vazio = uma linha em branco).
    Precisa estar dentro de um elemento .info-form (estilo) e de um form cujo handler use coletaItens() do custom.js.
--}}
<div class="info-quadro">
    <h5>{{ $titulo }}</h5>
    <table>
        <thead><tr><th>{{ $rotulo }}</th><th style="width: 30%;">Quantidade</th><th style="width: 1%;"></th></tr></thead>
        <tbody id="itens-{{ $grupo }}">
            @forelse ($linhas as $linha)
                @include('partials.item-linha', ['id' => $linha['id'], 'quantidade' => $linha['quantidade']])
            @empty
                @include('partials.item-linha', ['id' => null, 'quantidade' => null])
            @endforelse
        </tbody>
    </table>
    <template id="modelo-{{ $grupo }}">
        @include('partials.item-linha', ['id' => null, 'quantidade' => null])
    </template>
    <button type="button" class="btn btn-outline-success btn-sm item-adicionar" data-grupo="{{ $grupo }}" style="margin: 6px 8px 4px;">
        <span class="fas fa-plus"></span> {{ $botao }}
    </button>
    @if (!count($opcoes))
        <small class="text-muted d-block" style="margin: 0 8px;">Nenhum item cadastrado ainda.</small>
    @endif
</div>
