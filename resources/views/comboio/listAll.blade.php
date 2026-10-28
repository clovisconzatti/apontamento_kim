@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-gas-pump"></i></i> Comboio
                </h3>
            </td>
            <td width="50%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('comboio.formAdd')}}">
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
                <th width="15%" data-field="name">Tanque</th>
                <th width="5%" data-field="name">Capacidade</th>
                <th width="10%" data-field="name">Fazenda</th>
                <th width="2%" data-field="name">Estado</th>
                <th width="6%" data-field="name" class="text-center">Ativo</th>
                <th width="9%" data-field="name">Tipo</th>
                <th width="11%" data-field="name" class="text-right">Saldo inicial</th>
                <th width="20%" data-field="name">Observação</th>
                <th width="10%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comboios as $comboio)
                <tr>
                    <td align="">{{ $comboio->tanque }}  </td>
                    <td align="">{{ $comboio->capacidade }}  </td>
                    <td align="">{{ $comboio->fazenda }}  </td>
                    <td align="">{{ $comboio->uf }}  </td>
                    <td align="center">
                        @if ($comboio->ativo === 'Nao')
                            <span style="color: #b3261e; font-weight: 600;"><i class="fas fa-times-circle"></i> Não</span>
                        @else
                            <span style="color: #0b5a2a; font-weight: 600;"><i class="fas fa-check-circle"></i> Sim</span>
                        @endif
                    </td>
                    <td>{{ $comboio->tipo === 'posto' ? 'Posto externo' : 'Tanque próprio' }}</td>
                    <td align="right" class="text-nowrap">
                        @if (!is_null($comboio->saldo_inicial))
                            {{ rtrim(rtrim(number_format($comboio->saldo_inicial, 2, ',', '.'), '0'), ',') }} L
                            <small class="d-block text-muted">em {{ date('d/m/Y', strtotime($comboio->data_saldo_inicial)) }}</small>
                        @elseif ($comboio->tipo !== 'posto')
                            <small class="text-muted">não informado</small>
                        @endif
                    </td>
                    <td align="">{{ $comboio->obs }}  </td>
                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('comboio.formEdit', $comboio->id)}}">
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


