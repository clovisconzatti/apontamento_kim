@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-gas-pump"></i> Abastecimento
                </h3>
            </td>
            <td width="50%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('apontamento.formAdd')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>
    <div class="row">
        <div class="form-group col-md-2">
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                <span class="fas fa-filter"></span> Filtros
            </button>
        </div>
    </div>

    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('apontamento.listAll') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-5">
                        Placa:
                        <input class="form-control" type="text" name="placa" value="{{ $filtroPlaca }}">
                    </div>
                    <div class="form-group col-md-3">
                        Data inicial:
                        <input class="form-control" type="date" name="dtInicial" value="{{ $filtroDtInicial }}">
                    </div>
                    <div class="form-group col-md-3">
                        Data final:
                        <input class="form-control" type="date" name="dtFinal" value="{{ $filtroDtFinal }}">
                    </div>
                </div>
                <button class="btn btn-primary" type="submit" >
                    <span class="fas fa-play"></span> Filtrar
                </button>
            </form >
        </div>
    </div>
    <p>

    <table class="table table-bordered table-condensed table-striped fonte-10">
        <thead>
            <tr>
                <th width="10%" data-field="name">Data</th>
                <th width="30%" data-field="name">Placa</th>
                <th width="10%" data-field="name">Litros</th>
                <th width="10%" data-field="name">KM Atual</th>
                <th width="10%" data-field="name">Hora Atual</th>
                <th width="10%" data-field="name">Combustivel</th>
                <th width="10%" data-field="name">Origem</th>
                <th width="10%" data-field="name">Obs</th>
                <th width="10%" data-field="name">Ultimo KM</th>
                <th width="10%" data-field="name">Media</th>
                <th width="5%" data-field="name">Upload</th>
                <th width="5%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($apontamentos as $apontamento)
                <tr>
                    <td align="center"> {{ date('d/m/Y',strtotime($apontamento->data)) }} </td>
                    <td align="">{{ $apontamento->placa }} - {{ $apontamento->equipamento }}  </td>
                    <td align="right"> {{ number_format($apontamento->litros,2,',','.') }} </td>
                    <td align="right"> {{ number_format($apontamento->km,0,',','.') }} </td>
                    <td align="right"> {{ number_format($apontamento->horas,0,',','.') }} </td>
                    <td align="">{{ $apontamento->combustivel }}  </td>
                    <td align="">{{ $apontamento->origem }}  </td>
                    <td align="">{{ $apontamento->obs }}  </td>
                    <td align="">{{ number_format($apontamento->ultimo_km,0,',','.') }}  </td>
                    <td align="right"> @if ($apontamento->ultimo_km>0)
                        {{ number_format(($apontamento->km-$apontamento->ultimo_km)/($apontamento->litros),3,',','.') }}
                    @else
                        0
                    @endif </td>
                    <td align="">
                        <a class="btn btn-info" href="{{route('apontamento.apontamentoAnexo',[$apontamento->id])}}" target="_blank">
                            <i class="fa fa-upload"></i>
                        </a>
                    </td>
                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('apontamento.formEdit', $apontamento->id)}}">
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
        <tfoot>
            <tr bgColor="#c3c3c3" class="font-12">
                <td colspan="2">Total de litros de combustivel</td>
                <td align="right">
                    {{number_format($apontamentos->sum('litros'),2,',','.')}}
                </td>
                <td colspan="8"></td>
            </tr>
        </tfoot>
    </table>
@endsection


