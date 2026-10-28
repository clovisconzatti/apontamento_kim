@extends('layouts.model')
@section('content')
    @php
        // 1.234,5 (sem zeros sobrando à direita)
        $num = function ($valor, $casas = 2) {
            return rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
        };
        $totalHoras  = $maquinas->sum('horas');
        $totalDiesel = $maquinas->sum('diesel');
    @endphp
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-chart-bar"></i> Consumo por Máquina
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('informacao.listAll')}}">
                        <i class="fas fa-arrow-left"></i>&nbsp;&nbsp;&nbsp;
                        <span>Voltar</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>

    @include('partials.filtros')

    <h5 class="mt-3">Diesel e lubrificantes por máquina</h5>
    <table class="table table-bordered table-condensed table-striped fonte-10">
        <thead>
            <tr>
                <th width="34%">Equipamento</th>
                <th width="11%" class="text-right">Lançamentos</th>
                <th width="14%" class="text-right">Horas trabalhadas</th>
                <th width="14%" class="text-right">Diesel (L)</th>
                <th width="13%" class="text-right">Diesel (L/h)</th>
                <th width="14%" class="text-right">Lubrificante</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($maquinas as $maquina)
                <tr>
                    <td class="align-middle">{{ $maquina->equipamento ?? 'Sem equipamento' }}</td>
                    <td align="right" class="align-middle">{{ $num($maquina->registros, 0) }}</td>
                    <td align="right" class="align-middle">{{ $num($maquina->horas) }}</td>
                    <td align="right" class="align-middle">{{ $num($maquina->diesel) }}</td>
                    <td align="right" class="align-middle">{{ $maquina->horas > 0 ? $num($maquina->diesel / $maquina->horas) : '-' }}</td>
                    <td align="right" class="align-middle">{{ $num($maquina->lubrificante) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Nenhuma informação encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($maquinas->count())
            <tfoot>
                <tr class="font-weight-bold">
                    <td>Total</td>
                    <td align="right">{{ $num($totalRegistros, 0) }}</td>
                    <td align="right">{{ $num($totalHoras) }}</td>
                    <td align="right">{{ $num($totalDiesel) }}</td>
                    <td align="right">{{ $totalHoras > 0 ? $num($totalDiesel / $totalHoras) : '-' }}</td>
                    <td align="right">{{ $num($maquinas->sum('lubrificante')) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
    <small class="text-muted">Horas trabalhadas = horímetro final − horímetro inicial. Lançamentos sem os dois horímetros, ou com mais de 24h no dia, não somam horas.</small>

    @if ($suspeitos->count())
        <h5 class="mt-4 text-danger">Horímetros a conferir ({{ $suspeitos->count() }})</h5>
        <p class="text-muted mb-1">Mais de 24h num único lançamento: provável erro de digitação. Esses lançamentos ficaram fora das horas trabalhadas.</p>
        <table class="table table-bordered table-condensed table-striped fonte-10">
            <thead>
                <tr>
                    <th width="12%" class="text-center">Data</th>
                    <th width="40%">Equipamento</th>
                    <th width="15%" class="text-right">Horímetro inicial</th>
                    <th width="15%" class="text-right">Horímetro final</th>
                    <th width="10%" class="text-right">Diferença</th>
                    <th width="8%" class="text-center">Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suspeitos as $suspeito)
                    <tr>
                        <td align="center" class="align-middle">{{ date('d/m/Y',strtotime($suspeito->data)) }}</td>
                        <td class="align-middle">{{ $suspeito->equipamento }}</td>
                        <td align="right" class="align-middle">{{ $num($suspeito->horimetro_inicial) }}</td>
                        <td align="right" class="align-middle">{{ $num($suspeito->horimetro_final) }}</td>
                        <td align="right" class="align-middle">{{ $num($suspeito->horimetro_final - $suspeito->horimetro_inicial) }}</td>
                        <td align="center" class="align-middle">
                            <a class="btn btn-outline-info btn-sm" href="{{route('informacao.formEdit', $suspeito->id)}}">
                                <i class="far fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h5 class="mt-4">Lubrificantes por máquina e tipo</h5>
    <table class="table table-bordered table-condensed table-striped fonte-10">
        <thead>
            <tr>
                <th width="40%">Equipamento</th>
                <th width="34%">Lubrificante</th>
                <th width="12%" class="text-right">Lançamentos</th>
                <th width="14%" class="text-right">Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lubrificantes as $linha)
                <tr>
                    <td class="align-middle">{{ $linha->equipamento ?? 'Sem equipamento' }}</td>
                    <td class="align-middle">{{ $linha->lubrificante }}</td>
                    <td align="right" class="align-middle">{{ $num($linha->registros, 0) }}</td>
                    <td align="right" class="align-middle">{{ $num($linha->quantidade) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Nenhum lubrificante lançado para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($lubrificantes->count())
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="2">Total</td>
                    <td align="right">{{ $num($lubrificantes->sum('registros'), 0) }}</td>
                    <td align="right">{{ $num($lubrificantes->sum('quantidade')) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
