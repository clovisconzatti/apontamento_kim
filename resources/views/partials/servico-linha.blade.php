{{-- Uma linha de serviço executado (partials.servicos-quadro). Variável: $s (linha gravada ou null) --}}
<tr class="linha-item" data-grupo="servicos">
    <td style="width: auto; white-space: normal; padding-right: 8px;"><input class="form-control" data-campo="servico" type="text" maxlength="150" value="{{ $s->servico ?? '' }}" aria-label="Serviço"></td>
    <td><input class="form-control" data-campo="mecanico" type="text" maxlength="100" value="{{ $s->mecanico ?? '' }}" aria-label="Mecânico"></td>
    <td><input class="form-control" data-campo="data" type="date" value="{{ $s->data ?? '' }}" aria-label="Data do serviço"></td>
    <td><input class="form-control" data-campo="hora_inicial" type="time" value="{{ substr($s->hora_inicial ?? '', 0, 5) }}" aria-label="Hora de início do serviço"></td>
    <td><input class="form-control" data-campo="hora_final" type="time" value="{{ substr($s->hora_final ?? '', 0, 5) }}" aria-label="Hora de fim do serviço"></td>
    <td><input class="form-control" data-campo="valor_hora" type="number" step="0.01" min="0" value="{{ $s->valor_hora ?? '' }}" aria-label="Valor da hora"></td>
    <td><input class="form-control" data-campo="valor_total" type="number" step="0.01" min="0" value="{{ $s->valor_total ?? '' }}" aria-label="Valor total do serviço"></td>
    <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm item-remover" title="Remover linha" aria-label="Remover linha">
            <span class="fas fa-times"></span>
        </button>
    </td>
</tr>
