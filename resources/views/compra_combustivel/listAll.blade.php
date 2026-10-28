@extends('layouts.model')
@section('content')
    @php
        $n = fn($valor, $casas = 2) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
        $moeda = fn($valor) => 'R$ '.number_format((float) $valor, 2, ',', '.');
        $totalLitros = $compras->sum('litros');
        $totalValor = $compras->sum('valor_total');
    @endphp
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-file-invoice-dollar"></i> Compras de Combustível
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('compra_combustivel.formAdd')}}">
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
                <th width="9%" class="text-center">Data</th>
                <th width="20%">Fornecedor</th>
                <th width="9%">Nº NF</th>
                <th width="11%">Combustível</th>
                <th width="17%">Tanque de destino</th>
                <th width="10%" class="text-right">Quantidade</th>
                <th width="9%" class="text-right text-nowrap">Valor total NF</th>
                <th width="8%" class="text-right text-nowrap">Custo/litro</th>
                <th width="7%" class="text-center">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($compras as $compra)
                <tr>
                    <td align="center" class="align-middle">{{ date('d/m/Y', strtotime($compra->data)) }}</td>
                    <td class="align-middle">{{ $compra->fornecedor }}</td>
                    <td class="align-middle">{{ $compra->nr_nf }}</td>
                    <td class="align-middle">{{ $compra->combustivel }}</td>
                    <td class="align-middle">{{ $compra->tanque }}</td>
                    <td align="right" class="align-middle text-nowrap">{{ $n($compra->litros) }} L</td>
                    <td align="right" class="align-middle text-nowrap"><b>{{ $moeda($compra->valor_total) }}</b></td>
                    <td align="right" class="align-middle text-nowrap">R$ {{ number_format((float) $compra->valor_litro, 4, ',', '.') }}</td>
                    <td align="center" class="align-middle">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{route('compra_combustivel.formEdit', $compra->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Nenhuma compra encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($compras->count())
            <tfoot>
                <tr style="font-weight: 700;">
                    <td colspan="5">Total ({{ $compras->count() }} {{ $compras->count() == 1 ? 'compra' : 'compras' }})</td>
                    <td align="right" class="text-nowrap">{{ $n($totalLitros) }} L</td>
                    <td align="right" class="text-nowrap">{{ $moeda($totalValor) }}</td>
                    <td align="right" class="text-nowrap" title="Preço médio por litro">{{ $totalLitros > 0 ? 'R$ '.number_format($totalValor / $totalLitros, 4, ',', '.') : '–' }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
