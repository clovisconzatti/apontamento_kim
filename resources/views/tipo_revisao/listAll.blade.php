@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-clipboard-check"></i> Tipo de Revisão
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('tipo_revisao.formAdd')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>

    @include('partials.filtros')


    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th width="40%">Tipo de Revisão</th>
                <th width="15%" class="text-right">KM/Horas Revisão</th>
                <th width="35%">Observação</th>
                <th width="10%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tipos_revisao as $tipo_revisao)
                <tr>
                    <td class="align-middle">{{ $tipo_revisao->descricao }}</td>
                    <td align="right" class="align-middle">
                        @if (!is_null($tipo_revisao->intervalo))
                            {{ rtrim(rtrim(number_format($tipo_revisao->intervalo, 2, ',', '.'), '0'), ',') }}
                        @endif
                    </td>
                    <td class="align-middle">{{ $tipo_revisao->obs }}</td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('tipo_revisao.formEdit', $tipo_revisao->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Nenhum tipo de revisão cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
