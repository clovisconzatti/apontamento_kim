@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-gas-pump"></i> Abastecimento
                </h3>
            </td>
            <td width="20%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('apontamento.formAdd')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>

    @if (session()->get('success'))
        <div class="alert alert-success">{{ session()->get('success') }}</div>
    @endif

    @include('partials.filtros')

    <table class="table table-bordered table-condensed table-striped fonte-10">
        <thead>
            <tr>
                <th width="8%">Data</th>
                <th width="20%">Placa</th>
                <th width="7%">Litros</th>
                <th width="7%">KM Atual</th>
                <th width="7%">Hora Atual</th>
                <th width="8%">Combustível</th>
                <th width="7%">Comboio</th>
                <th width="12%">Obs</th>
                <th width="7%">KM Anterior</th>
                <th width="6%" title="Quilômetros por litro desde o abastecimento anterior">Km/L</th>
                <th width="6%" title="Litros por hora desde o abastecimento anterior">L/h</th>
                <th width="5%">Anexo</th>
                <th width="5%">Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($apontamentos as $apontamento)
                @php
                    $ehArla = $apontamento->combustivel == 'Arla';
                    $kmRodado = ($apontamento->km_anterior > 0 && $apontamento->km > $apontamento->km_anterior)
                                ? $apontamento->km - $apontamento->km_anterior : null;
                    $horasTrabalhadas = ($apontamento->hora_anterior > 0 && $apontamento->horas > $apontamento->hora_anterior)
                                ? $apontamento->horas - $apontamento->hora_anterior : null;
                    $kmPorLitro = (!$ehArla && $kmRodado && $apontamento->litros > 0) ? $kmRodado / $apontamento->litros : null;
                    $litrosPorHora = (!$ehArla && $horasTrabalhadas) ? $apontamento->litros / $horasTrabalhadas : null;
                    $kmMenor = $apontamento->km_anterior > 0 && $apontamento->km !== null && $apontamento->km < $apontamento->km_anterior;
                @endphp
                <tr>
                    <td align="center">{{ date('d/m/Y', strtotime($apontamento->data)) }}</td>
                    <td>{{ $apontamento->placa }} - {{ $apontamento->equipamento }}</td>
                    <td align="right">{{ number_format($apontamento->litros, 2, ',', '.') }}</td>
                    <td align="right" class="{{ $kmMenor ? 'text-danger font-weight-bold' : '' }}" title="{{ $kmMenor ? 'Km menor que o abastecimento anterior' : '' }}">
                        {{ $apontamento->km !== null ? number_format($apontamento->km, 0, ',', '.') : '-' }}
                    </td>
                    <td align="right">{{ $apontamento->horas !== null ? number_format($apontamento->horas, 1, ',', '.') : '-' }}</td>
                    <td>{{ $apontamento->combustivel }}</td>
                    <td>{{ $apontamento->origem }}</td>
                    <td>{{ $apontamento->obs }}</td>
                    <td align="right">{{ $apontamento->km_anterior ? number_format($apontamento->km_anterior, 0, ',', '.') : '-' }}</td>
                    <td align="right">{{ $kmPorLitro !== null ? number_format($kmPorLitro, 3, ',', '.') : '-' }}</td>
                    <td align="right">{{ $litrosPorHora !== null ? number_format($litrosPorHora, 2, ',', '.') : '-' }}</td>
                    <td align="center">
                        <a class="btn {{ $apontamento->anexo ? 'btn-success' : 'btn-info' }}" href="{{ route('apontamento.apontamentoAnexo', [$apontamento->id]) }}" target="_blank"
                           title="{{ $apontamento->anexo ? 'Ver / substituir anexo' : 'Enviar anexo' }}">
                            <i class="fa {{ $apontamento->anexo ? 'fa-paperclip' : 'fa-upload' }}"></i>
                        </a>
                    </td>
                    <td align="center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('apontamento.formEdit', $apontamento->id) }}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                                <form action="{{ route('apontamento.destroy', ['apontamento' => $apontamento->id]) }}" method="POST"
                                      onsubmit="return confirm('Excluir o abastecimento de {{ date('d/m/Y', strtotime($apontamento->data)) }} - {{ $apontamento->placa }}?');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="far fa-trash-alt"></i>&nbsp;&nbsp;&nbsp;
                                        <span>Excluir</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" align="center">Nenhum abastecimento encontrado para os filtros informados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @foreach ($totais as $total)
                <tr class="font-12">
                    <td colspan="2">Total {{ $total->combustivel ?: 'sem combustível' }} ({{ $total->qtde }} abastecimentos)</td>
                    <td align="right">{{ number_format($total->litros, 2, ',', '.') }}</td>
                    <td colspan="10"></td>
                </tr>
            @endforeach
            <tr bgColor="#c3c3c3" class="font-12">
                <td colspan="2"><b>Total geral do filtro ({{ $totais->sum('qtde') }} abastecimentos)</b></td>
                <td align="right"><b>{{ number_format($totais->sum('litros'), 2, ',', '.') }}</b></td>
                <td colspan="10"></td>
            </tr>
        </tfoot>
    </table>

    {{ $apontamentos->links() }}
@endsection
