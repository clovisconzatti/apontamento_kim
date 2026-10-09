@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-tree"></i> Informações Diárias
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
                <th width="9%" class="text-center">Data</th>
                <th width="19%">Equipamento</th>
                <th width="16%">Atividade</th>
                <th width="18%">Fazenda</th>
                <th width="19%">Colaborador</th>
                <th width="10%" class="text-right text-nowrap">Horímetro inicial</th>
                <th width="9%" class="text-center">Ação</th>
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
                    <td colspan="7" class="text-center text-muted">Nenhuma informação encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection


