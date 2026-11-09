@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-tools"></i> Serviços de Revisão
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('servico_revisao.formAdd')}}">
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
                <th width="85%">Serviço</th>
                <th width="15%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servicos as $servico)
                <tr>
                    <td class="align-middle">{{ $servico->servico }}</td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('servico_revisao.formEdit', $servico->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">Nenhum serviço de revisão cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
