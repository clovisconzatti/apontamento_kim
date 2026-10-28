@extends('layouts.model')
@section('content')
    @php
        $n = fn($valor) => rtrim(rtrim(number_format((float) $valor, 2, ',', '.'), '0'), ',');
        $faltas = $ajustes->where('tipo', 'falta')->sum('quantidade');
        $sobras = $ajustes->where('tipo', 'sobra')->sum('quantidade');
        $saldo = $sobras - $faltas;
    @endphp
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-balance-scale"></i> Ajustes de Saldo de Combustível
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('ajuste_combustivel.formAdd')}}">
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
                <th width="12%" class="text-center">Data</th>
                <th width="46%">Tanque</th>
                <th width="16%">Tipo do ajuste</th>
                <th width="16%" class="text-right">Quantidade</th>
                <th width="10%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ajustes as $ajuste)
                <tr>
                    <td align="center" class="align-middle">{{ date('d/m/Y', strtotime($ajuste->data)) }}</td>
                    <td class="align-middle">{{ $ajuste->tanque }}</td>
                    <td class="align-middle">
                        @if ($ajuste->tipo === 'falta')
                            <span style="color: #b3261e; font-weight: 600;"><i class="fas fa-minus-circle"></i> {{ $tipos['falta'] }}</span>
                        @else
                            <span style="color: #0b5a2a; font-weight: 600;"><i class="fas fa-plus-circle"></i> {{ $tipos['sobra'] }}</span>
                        @endif
                    </td>
                    <td align="right" class="align-middle text-nowrap"><b>{{ $ajuste->tipo === 'falta' ? '−' : '+' }}{{ $n($ajuste->quantidade) }} L</b></td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('ajuste_combustivel.formEdit', $ajuste->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Nenhum ajuste encontrado para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($ajustes->count())
            <tfoot>
                <tr>
                    <td colspan="3" align="right">Total de faltas</td>
                    <td align="right" class="text-nowrap" style="color: #b3261e;">−{{ $n($faltas) }} L</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" align="right">Total de sobras</td>
                    <td align="right" class="text-nowrap" style="color: #0b5a2a;">+{{ $n($sobras) }} L</td>
                    <td></td>
                </tr>
                <tr style="font-weight: 700;">
                    <td colspan="3" align="right">Saldo dos ajustes ({{ $ajustes->count() }} {{ $ajustes->count() == 1 ? 'ajuste' : 'ajustes' }})</td>
                    <td align="right" class="text-nowrap">{{ $saldo < 0 ? '−' : ($saldo > 0 ? '+' : '') }}{{ $n(abs($saldo)) }} L</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
