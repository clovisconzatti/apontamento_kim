{{--
    Uma linha de peça ou lubrificante da revisão.
    Variáveis: $grupo ('pecas' | 'lubrificantes'), $rotulo, $opcoes ([id => texto]), $id e $quantidade (vazios numa linha nova).
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
    <td>
        <input class="form-control item-quantidade" type="number" step="any" min="0" value="{{ $quantidade }}" aria-label="Quantidade de {{ mb_strtolower($rotulo) }}">
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm item-remover" title="Remover linha" aria-label="Remover linha">
            <span class="fas fa-times"></span>
        </button>
    </td>
</tr>
