{{--
    Quadro de serviços executados (Manutenção e Revisão), digitados ou importados da OS em PDF.
    Variável: $linhasServicos (registros de manutencao_servico / revisao_servico; vazio = uma linha em branco).
--}}
<div class="info-quadro" style="overflow-x: auto;">
    <h5>Serviços executados</h5>
    <table style="min-width: 900px;">
        <thead>
            <tr>
                <th>Serviço</th><th style="width: 18%;">Mecânico</th><th style="width: 13%;">Data</th><th style="width: 10%;">Início</th>
                <th style="width: 10%;">Fim</th><th style="width: 10%;">R$ hora</th><th style="width: 11%;">R$ total</th><th style="width: 1%;"></th>
            </tr>
        </thead>
        <tbody id="itens-servicos">
            @forelse ($linhasServicos as $s)
                @include('partials.servico-linha', ['s' => $s])
            @empty
                @include('partials.servico-linha', ['s' => null])
            @endforelse
        </tbody>
    </table>
    <template id="modelo-servicos">
        @include('partials.servico-linha', ['s' => null])
    </template>
    <button type="button" class="btn btn-outline-success btn-sm item-adicionar" data-grupo="servicos" style="margin: 6px 8px 4px;">
        <span class="fas fa-plus"></span> Adicionar serviço
    </button>
    <b style="float: right; margin: 10px 8px 4px;">Total: <span id="total-servicos">–</span></b>
</div>
