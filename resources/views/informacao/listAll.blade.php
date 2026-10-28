@extends('layouts.model')
@section('content')
    @php
        $numero = fn($valor) => rtrim(rtrim(number_format((float) $valor, 2, ',', '.'), '0'), ',');
        $motivosConsumo = App\Support\ConsumoAbastecimento::MOTIVOS;
    @endphp
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="60%">
                <h3>
                    <i class="fas fa-tree"></i> Informações Diárias
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('painel.consumo_medio')}}" title="Consumo médio (L/h) de cada máquina pelos abastecimentos">
                        <i class="fas fa-tachometer-alt"></i>&nbsp;&nbsp;&nbsp;
                        <span>Consumo médio</span>
                    </a>
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('informacao.formAdd')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>

    @include('partials.filtros')


    <table class="table table-bordered table-condensed table-striped fonte-10">
        <thead>
            <tr>
                <th width="8%" class="text-center">Data</th>
                <th width="14%">Equipamento</th>
                <th width="11%">Atividade</th>
                <th width="13%">Fazenda</th>
                <th width="14%">Colaborador</th>
                <th width="8%" class="text-right text-nowrap">Horímetro inicial</th>
                <th width="8%" class="text-right text-nowrap" title="Horímetro informado no abastecimento">Horím. abastec.</th>
                <th width="7%" class="text-right text-nowrap" title="Horímetro do abastecimento − horímetro do abastecimento anterior">Variação</th>
                <th width="9%" class="text-right text-nowrap" title="Litros abastecidos ÷ variação do horímetro">Média consumo</th>
                <th width="8%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($informacoes as $informacao)
                <tr>
                    <td align="center" class="align-middle">{{ date('d/m/Y',strtotime($informacao->data)) }}</td>
                    <td class="align-middle">{{ $informacao->equipamento }}</td>
                    <td class="align-middle">{{ $informacao->atividade }}</td>
                    <td class="align-middle">{{ $informacao->fazenda }}</td>
                    <td class="align-middle">{{ $informacao->colaborador }}</td>
                    <td align="right" class="align-middle text-nowrap">
                        @if (!is_null($informacao->horimetro_inicial))
                            {{ rtrim(rtrim(number_format($informacao->horimetro_inicial, 2, ',', '.'), '0'), ',') }}
                        @endif
                    </td>
                    {{-- Só nas linhas com horímetro de abastecimento: variação desde o abastecimento anterior e média (L/h) --}}
                    @php
                        $temHorimetro = $informacao->horimetro_abastecimento > 0 && $informacao->qnt_diesel > 0;
                        $intervalo = $consumo['intervalos'][$informacao->id] ?? null;
                        $motivo = $informacao->placa === App\Support\ConsumoAbastecimento::PLACA_TERCEIROS
                            ? 'terceiro' : ($consumo['sem_intervalo'][$informacao->id] ?? null);
                    @endphp
                    <td align="right" class="align-middle text-nowrap">
                        @if ($temHorimetro)
                            {{ $numero($informacao->horimetro_abastecimento) }}
                        @endif
                    </td>
                    <td align="right" class="align-middle text-nowrap">
                        @if ($temHorimetro && $intervalo)
                            <span title="Desde o abastecimento de {{ date('d/m/Y', strtotime($intervalo['data_ref'])) }} (horímetro {{ $numero($intervalo['horimetro_ref']) }})">{{ $numero($intervalo['horas']) }} h</span>
                        @elseif ($temHorimetro && $motivo === 'terceiro')
                            <span class="text-muted" title="{{ $motivosConsumo['terceiro'] }}">terceiros</span>
                        @elseif ($temHorimetro && $motivo === 'conferir')
                            <button type="button" class="btn btn-link btn-sm p-0 conferir-abrir" data-id="{{ $informacao->id }}"
                                    title="Ver o problema e corrigir" style="color: #b3261e; font-weight: 600; text-decoration: underline;">
                                <i class="fas fa-exclamation-triangle"></i> conferir
                            </button>
                        @elseif ($temHorimetro && $motivo)
                            <span class="text-muted" title="{{ $motivosConsumo[$motivo] }}">–</span>
                        @endif
                    </td>
                    <td align="right" class="align-middle text-nowrap">
                        @if ($temHorimetro && $intervalo)
                            <b title="{{ $numero($intervalo['litros']) }} L ÷ {{ $numero($intervalo['horas']) }} h">{{ number_format($intervalo['litros'] / $intervalo['horas'], 2, ',', '.') }} L/h</b>
                        @endif
                    </td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('informacao.formEdit', $informacao->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">Nenhuma informação encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.corrigir-horimetro', ['problemas' => $problemas])
@endsection


