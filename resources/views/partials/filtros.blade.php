{{--
    Painel de filtros padrão das listagens.
    Variáveis esperadas (enviadas pelo controller):
      $camposFiltro   => ['campo' => ['label' => '...', 'tipo' => 'texto|data|select', 'opcoes' => [...], 'col' => 3]]
      $filtros        => valores atuais dos filtros
      $totalRegistros => (opcional) quantidade de registros encontrados
--}}
@php
    $ativos = [];
    foreach ($camposFiltro as $nomeCampo => $campo) {
        $valor = $filtros[$nomeCampo] ?? null;
        if ($valor === null || $valor === '') {
            continue;
        }
        if (($campo['tipo'] ?? 'texto') === 'select') {
            $valor = $campo['opcoes'][$valor] ?? $valor;
        } elseif (($campo['tipo'] ?? 'texto') === 'data') {
            $valor = date('d/m/Y', strtotime($valor));
        }
        $ativos[] = ['label' => $campo['label'], 'valor' => $valor];
    }
@endphp

<div class="row mb-2">
    <div class="col-md-12">
        {{-- Toggle próprio: o layout carrega Bootstrap 3 e 4, e o data-toggle="collapse" abria e fechava na mesma hora --}}
        <button class="btn btn-primary btn-sm" type="button" title="Mostrar / esconder filtros" onclick="alternarFiltros();">
            <span class="fas fa-filter"></span> Filtros
            @if (count($ativos))
                <span class="badge badge-light">{{ count($ativos) }}</span>
            @endif
            <span id="setaFiltros" class="d-inline-block ml-1" style="transition: transform .15s;"><span class="fas fa-chevron-down"></span></span>
        </button>
        @foreach ($ativos as $ativo)
            <span class="badge badge-info" style="font-size: 85%; font-weight: normal;">
                {{ $ativo['label'] }}: <b>{{ $ativo['valor'] }}</b>
            </span>
        @endforeach
        @isset($totalRegistros)
            <small class="text-muted ml-2">{{ number_format($totalRegistros, 0, ',', '.') }} registro(s) encontrado(s)</small>
        @endisset
    </div>
</div>

{{-- Começa escondido; o navegador lembra se o usuário deixou aberto --}}
<div id="painelFiltros" class="card card-body mb-3" style="display: none;">
    <form method="get" action="{{ url()->current() }}">
        <input type="hidden" name="filtrar" value="1">
        <div class="row">
            @foreach ($camposFiltro as $nomeCampo => $campo)
                @php $valorAtual = $filtros[$nomeCampo] ?? null; @endphp
                <div class="form-group col-md-{{ $campo['col'] ?? 3 }}">
                    {{ $campo['label'] }}:
                    @switch($campo['tipo'] ?? 'texto')
                        @case('data')
                            <input class="form-control" type="date" name="{{ $nomeCampo }}" value="{{ $valorAtual }}">
                            @break
                        @case('select')
                            <select class="form-control" name="{{ $nomeCampo }}">
                                <option value="">Todos</option>
                                @foreach ($campo['opcoes'] as $valorOpcao => $textoOpcao)
                                    <option value="{{ $valorOpcao }}" {{ (string) $valorOpcao === (string) $valorAtual ? 'selected' : '' }}>{{ $textoOpcao }}</option>
                                @endforeach
                            </select>
                            @break
                        @default
                            <input class="form-control" type="text" name="{{ $nomeCampo }}" value="{{ $valorAtual }}"
                                   placeholder="{{ $campo['placeholder'] ?? '' }}">
                    @endswitch
                </div>
            @endforeach
        </div>
        <button class="btn btn-primary" type="submit">
            <span class="fas fa-search"></span> Filtrar
        </button>
        <a class="btn btn-secondary" href="{{ url()->current() }}?limpar=1">
            <span class="fas fa-eraser"></span> Limpar filtros
        </a>
    </form>
</div>

<script>
    function alternarFiltros() {
        var aberto = !$('#painelFiltros').is(':visible');
        $('#painelFiltros').slideToggle(150);
        $('#setaFiltros').css('transform', aberto ? 'rotate(180deg)' : '');
        try { localStorage.setItem('filtrosAbertos', aberto ? '1' : '0'); } catch (e) {}
    }

    try {
        if (localStorage.getItem('filtrosAbertos') === '1') {
            document.getElementById('painelFiltros').style.display = '';
            document.getElementById('setaFiltros').style.transform = 'rotate(180deg)';
        }
    } catch (e) {}
</script>
