{{--
    Uma linha de peça ou lubrificante (Revisão, Manutenção).
    Variáveis: $grupo (nome do grupo, ex.: pecas), $rotulo, $opcoes ([id => texto]), $id e $quantidade (vazios numa linha nova).
    Com $comValores: $linha (valor_unitario, desconto e valor_total; null numa linha nova).
--}}
<tr class="linha-item" data-grupo="{{ $grupo }}">
    <td style="width: auto; white-space: normal; font-weight: 400; padding-right: 8px;">
        <select class="form-control item-id" aria-label="{{ $rotulo }}">
            <option value="">Selecione</option>
            @foreach ($opcoes as $valor => $texto)
                <option value="{{ $valor }}" {{ (string) $valor === (string) $id ? 'selected' : '' }}>{{ $texto }}</option>
            @endforeach
        </select>
    </td>
    @unless ($semQuantidade ?? false)
    <td>
        <input class="form-control item-quantidade" type="number" step="any" min="0" value="{{ $quantidade }}" aria-label="Quantidade de {{ mb_strtolower($rotulo) }}">
    </td>
    @endunless
    @if ($comValores ?? false)
        <td>
            <input class="form-control" data-campo="valor_unitario" type="number" step="any" min="0" value="{{ $linha['valor_unitario'] ?? '' }}" aria-label="Valor unitário">
            <input type="hidden" data-campo="desconto" value="{{ $linha['desconto'] ?? '' }}">
        </td>
        <td>
            <input class="form-control" data-campo="valor_total" type="number" step="0.01" min="0" value="{{ $linha['valor_total'] ?? '' }}" aria-label="Valor total">
        </td>
    @endif
    <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm item-remover" title="Remover linha" aria-label="Remover linha">
            <span class="fas fa-times"></span>
        </button>
    </td>
</tr>
