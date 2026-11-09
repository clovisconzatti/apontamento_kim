@extends('layouts.model')
@section('content')
    @php
        $horimetro = fn($valor) => is_null($valor) ? '' : rtrim(rtrim(number_format($valor, 2, ',', '.'), '0'), ',');
    @endphp
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-calendar-check"></i> Revisões
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('revisao.formAdd')}}">
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
                <th width="12%">Revisão Atual</th>
                <th width="6%">Nº OS</th>
                <th width="8%" class="text-center text-nowrap">Horário</th>
                <th width="7%" class="text-right text-nowrap">Custo terceiro</th>
                <th width="7%" class="text-right text-nowrap">Custo horas</th>
                <th width="7%" class="text-right text-nowrap">Deslocamento</th>
                <th width="10%" class="text-right text-nowrap">Horímetro atual</th>
                <th width="8%" class="text-right text-nowrap">Intervalo</th>
                <th width="15%">Próxima Revisão</th>
                <th width="9%" class="text-right text-nowrap">Horímetro próx. revisão</th>
                <th width="7%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($revisoes as $revisao)
                <tr>
                    <td align="center" class="align-middle">{{ date('d/m/Y',strtotime($revisao->data)) }}</td>
                    <td class="align-middle">{{ $revisao->placa }}{{ $revisao->equipamento ? ' - '.$revisao->equipamento : '' }}</td>
                    <td class="align-middle">{{ $revisao->tipo_revisao }}</td>
                    <td class="align-middle">{{ $revisao->nr_os }}</td>
                    <td align="center" class="align-middle text-nowrap">
                        {{ $revisao->hora_inicial ? substr($revisao->hora_inicial, 0, 5) : '' }}{{ $revisao->hora_inicial || $revisao->hora_final ? ' – ' : '' }}{{ $revisao->hora_final ? substr($revisao->hora_final, 0, 5) : '' }}
                    </td>
                    <td align="right" class="align-middle text-nowrap">{{ is_null($revisao->custo_terceiro) ? '' : 'R$ '.number_format($revisao->custo_terceiro, 2, ',', '.') }}</td>
                    @php
                        $horasRevisao = \App\Support\CustoHoras::horas($revisao->hora_inicial, $revisao->hora_final);
                        $custoHoras = \App\Support\CustoHoras::custo($revisao->hora_inicial, $revisao->hora_final, $revisao->custo_hora);
                    @endphp
                    <td align="right" class="align-middle text-nowrap"
                        title="{{ is_null($custoHoras) ? '' : \App\Support\CustoHoras::formatar($horasRevisao).' × R$ '.number_format($revisao->custo_hora, 2, ',', '.').'/h' }}">
                        {{ is_null($custoHoras) ? '' : 'R$ '.number_format($custoHoras, 2, ',', '.') }}
                    </td>
                    <td align="right" class="align-middle text-nowrap">{{ is_null($revisao->custo_deslocamento) ? '' : 'R$ '.number_format($revisao->custo_deslocamento, 2, ',', '.') }}</td>
                    <td align="right" class="align-middle text-nowrap">{{ $horimetro($revisao->horimetro_atual) }}</td>
                    <td align="right" class="align-middle text-nowrap">{{ is_null($revisao->intervalo_horas) ? '' : $horimetro($revisao->intervalo_horas).' h' }}</td>
                    <td class="align-middle">{{ $revisao->proxima_revisao }}</td>
                    <td align="right" class="align-middle text-nowrap"><b>{{ $horimetro($revisao->horimetro_proxima) }}</b></td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('revisao.formEdit', $revisao->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center text-muted">Nenhuma revisão encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
