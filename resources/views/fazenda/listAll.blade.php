@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="far fa-map"></i></i> Fazendas
                </h3>
            </td>
            <td width="50%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('fazenda.formAdd')}}">
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
                <th width="30%" data-field="name">Fazenda</th>
                <th width="5%" data-field="name">Estado</th>
                <th width="20%" data-field="name">Apontador</th>
                <th width="5%" data-field="name">Ativa</th>
                <th width="10%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>
            {{-- {{ dd($fazendas) }} --}}
            @foreach ($fazendas as $fazenda)
                <tr>
                    <td align="">{{ $fazenda->fazenda }}  </td>
                    <td align="">{{ $fazenda->uf }}  </td>
                    <td align="">{{ $fazenda->colaborador }}  </td>
                    <td align="">{{ $fazenda->ativa }}  </td>
                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('fazenda.formEdit', $fazenda->id)}}">
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


