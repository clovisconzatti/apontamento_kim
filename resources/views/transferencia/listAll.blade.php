@extends('layouts.model')
@section('content')
    <table class="table table-bordered table-striped table-sm">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-gas-pump"></i>
                    <i class="fas fa-arrow-right-arrow-left"></i> Transferência entre Reservatórios/Comboios
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('transferencia.formAdd')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>


<div class="table-responsive">
        <table class="table table-bordered table-striped table-sm tabela-transferencia">
            <thead class="thead-light">

            <tr>
                <th width="10%" data-field="name">Data</th>
                <th width="10%" data-field="name">Origem</th>
                <th width="10%" data-field="name">Destino</th>
                <th width="10%" data-field="name">Equipamento/Caminhão</th>
                <th width="5%" data-field="name">Litros</th>
                <th width="10%" data-field="name">Documento</th>
                <th width="10%" data-field="name">Combustivel</th>
                <th width="10%" data-field="name">Horimetro</th>
                <th width="10%" data-field="name">Operador</th>
                <th width="10%" data-field="name">Fazenda</th>
                <th width="10%" data-field="name">Hori.Inicial</th>
                <th width="10%" data-field="name">Hori.Final</th>
                <th width="10%" data-field="name">Obs</th>
                <th width="5%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($transferencias as $transferencia)
                <tr>
                    <td align="center"> {{ date('d/m/Y',strtotime($transferencia->data)) }} </td>
                    <td>{{ $transferencia->origem }} </td>
                    <td>{{ $transferencia->tanque }} </td>
                    <td>{{ $transferencia->destino }} </td>
                    <td align="right"> {{ number_format($transferencia->litros,2,',','.') }} </td>
                    <td>{{ $transferencia->nr_doc }} </td>
                    <td>{{ $transferencia->combustivel }} </td>
                    <td align="right"> {{ number_format($transferencia->horimetro,2,',','.') }} </td>
                    <td>{{ $transferencia->operador }} </td>
                    <td>{{ $transferencia->fazenda }} </td>
                    <td align="right"> {{ number_format($transferencia->horimetro_inicial,2,',','.') }} </td>
                    <td align="right"> {{ number_format($transferencia->horimetro_final,2,',','.') }} </td>
                    <td>{{ $transferencia->obs }} </td>
                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('transferencia.formEdit', $transferencia->id)}}">
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





