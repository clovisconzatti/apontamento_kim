@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-tree"></i> Informações de Manutencões
                </h3>
            </td>
            <td width="50%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('manutencao.formAdd')}}">
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
                <th width="10%" data-field="name">Data</th>
                <th width="10%" data-field="name">Equipamento</th>
                <th width="10%" data-field="name">Fazenda</th>
                <th width="10%" data-field="name">Colaborador</th>
                <th width="10%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>
            {{-- {{ dd($informacoes) }} --}}
            @foreach ($manutencoes as $manutencao)
                <tr>
                    <td align="center"> {{ date('d/m/Y',strtotime($manutencao->data)) }} </td>
                    <td align="">{{ $manutencao->equipamento }} </td>
                    <td align="">{{ $manutencao->fazenda }}  </td>
                    <td align="">{{ $manutencao->operador }}  </td>
                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('manutencao.formEdit', $manutencao->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                                <a class="dropdown-item" href="#">
                                    {{-- <form action=" {{ route('menu.destroy',['menu'=> $menu->id ]) }} " method="POST">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name='menu' value=" {{ $menu->id }} ">
                                        <i class="far fa-trash-alt"></i>
                                        <input type="submit" class="btn btn-default delete"  value="Eliminar">
                                    </form> --}}
                                </a>
                            </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection


