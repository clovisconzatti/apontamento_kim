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
                <th width="10%" class="text-center">Data</th>
                <th width="24%">Equipamento</th>
                <th width="22%">Tipo da Revisão</th>
                <th width="10%">Nº OS</th>
                <th width="12%" class="text-right text-nowrap">Horímetro atual</th>
                <th width="13%" class="text-right text-nowrap">Próx. revisão</th>
                <th width="9%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($revisoes as $revisao)
                <tr>
                    <td align="center" class="align-middle">{{ date('d/m/Y',strtotime($revisao->data)) }}</td>
                    <td class="align-middle">{{ $revisao->placa }}{{ $revisao->equipamento ? ' - '.$revisao->equipamento : '' }}</td>
                    <td class="align-middle">{{ $revisao->tipo_revisao }}</td>
                    <td class="align-middle">{{ $revisao->nr_os }}</td>
                    <td align="right" class="align-middle text-nowrap">{{ $horimetro($revisao->horimetro_atual) }}</td>
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
                    <td colspan="7" class="text-center text-muted">Nenhuma revisão encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
