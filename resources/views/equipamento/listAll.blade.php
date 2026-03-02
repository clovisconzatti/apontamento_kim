@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-truck"></i> Equipamento
                </h3>
            </td>
            <td width="50%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('equipamento.formAdd')}}">
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
                <th width="15%" data-field="name">Placa</th>
                <th width="25%" data-field="name">Equipamento</th>
                <th width="5%" data-field="name">Ano</th>
                <th width="5%" data-field="name">Ativo</th>
                <th width="10%" data-field="name">Data Partida</th>
                <th width="10%" data-field="name">Tipo</th>
                <th width="10%" data-field="name">Atividade</th>
                <th width="5%" data-field="name">Cilindros</th>
                <th width="10%" data-field="name">Operação</th>
                <th width="5%" data-field="name">Cons. Minimo</th>
                <th width="5%" data-field="name">Cons. Máximo</th>
                <th width="2%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>
            {{-- {{ dd($equipamentos) }} --}}
            @foreach ($equipamentos as $equipamento)
                <tr>
                    <td>{{ $equipamento->placa }} </td>
                    <td align="">{{ $equipamento->equipamento }}  </td>
                    <td>{{ $equipamento->ano }} </td>
                    <td>{{ $equipamento->ativo }} </td>
                    <td align="center"> {{ date('d/m/Y',strtotime($equipamento->data_partida)) }} </td>
                    <td>{{ $equipamento->tipo }} </td>
                    <td>{{ $equipamento->atividade }} </td>
                    <td>{{ $equipamento->cilindros }} </td>
                    <td>{{ $equipamento->operacao }} </td>
                     <td align="right"> {{ number_format($equipamento->consumo_minimo,2,',','.') }} </td>
                     <td align="right"> {{ number_format($equipamento->consumo_maximo,2,',','.') }} </td>

                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('equipamento.formEdit', $equipamento->id)}}">
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


