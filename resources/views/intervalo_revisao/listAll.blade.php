@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-history"></i> Intervalo de Revisão
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('intervalo_revisao.formAdd')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th width="85%">Horas de Intervalo</th>
                <th width="15%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($intervalos as $intervalo)
                <tr>
                    <td class="align-middle">{{ rtrim(rtrim(number_format($intervalo->horas, 2, ',', '.'), '0'), ',') }} h</td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('intervalo_revisao.formEdit', $intervalo->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">Nenhum intervalo de revisão cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
