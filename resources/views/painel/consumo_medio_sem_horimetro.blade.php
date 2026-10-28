@extends('layouts.model')
@section('content')
    @php
        $n = fn($valor, $casas = 1) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
    @endphp

    @include('partials.painel-estilo')
    <style>
        .viz-root .corr-horimetro { display: flex; gap: 6px; align-items: center; min-width: 230px; }
        .viz-root .corr-horimetro input { width: 120px; height: 38px; padding: 0 10px; border: 1px solid #cad4cc; border-radius: 6px; font: inherit; text-align: right; }
        .viz-root .corr-sugestao { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; }
        .viz-root .corr-sugestao button { font-size: 11px; padding: 1px 6px; }
        .viz-root tr.corr-salva td { background: #e3f1e7; transition: background .3s; }
    </style>

    <div class="viz-root">
        <div class="viz-topo">
            <div>
                <small>Painel de Operações · Consumo Médio</small>
                <h3><i class="fas fa-wrench"></i> Abastecimentos sem horímetro</h3>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('painel.consumo_medio') }}">
                <span class="fas fa-arrow-left"></span> Voltar ao Consumo Médio
            </a>
        </div>

        @include('partials.filtros')

        <div class="viz-card" style="padding: 12px 18px;">
            <p class="viz-sub" style="margin: 0;">
                <i class="fas fa-info-circle"></i>
                Abastecimentos com diesel lançado mas sem <b>horímetro de abastecimento</b>: sem ele o consumo médio não pode ser calculado.
                Digite o horímetro e clique em <b>Salvar</b> (ou Enter). Os botões "Início" e "Fim" usam o horímetro inicial ou final lançado no mesmo dia.
                Os filtros são os mesmos do Consumo Médio.
            </p>
        </div>

        <div class="viz-card">
            <h4>Faltam <span id="faltam">{{ $lancamentos->count() }}</span> {{ $lancamentos->count() == 1 ? 'lançamento' : 'lançamentos' }}</h4>
            <div class="viz-tabela-box">
                <table class="viz-tabela">
                    <thead>
                        <tr>
                            <th class="text-center">Data</th>
                            <th>Máquina</th>
                            <th>Fazenda</th>
                            <th>Colaborador</th>
                            <th>Tanque de origem</th>
                            <th class="text-right">Nº NF</th>
                            <th class="text-right">Litros</th>
                            <th>Horímetro do abastecimento</th>
                            <th class="text-center">Formulário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lancamentos as $l)
                            <tr data-id="{{ $l->id }}">
                                <td class="text-center">{{ date('d/m/Y', strtotime($l->data)) }}</td>
                                <td><b>{{ $l->maquina }}</b></td>
                                <td>{{ $l->fazenda }}</td>
                                <td>{{ $l->colaborador }}</td>
                                <td>{{ $l->tanque }}</td>
                                <td class="num">{{ $l->nr_nf }}</td>
                                <td class="num">{{ $n($l->qnt_diesel, 0) }} L</td>
                                <td>
                                    <div class="corr-horimetro">
                                        <label class="sr-only" for="horimetro{{ $l->id }}">Horímetro do abastecimento de {{ $l->maquina }} em {{ date('d/m/Y', strtotime($l->data)) }}</label>
                                        <input type="number" step="any" min="0" id="horimetro{{ $l->id }}" class="corr-valor">
                                        <button type="button" class="btn btn-success btn-sm corr-salvar"><i class="fas fa-save"></i> Salvar</button>
                                    </div>
                                    @if ($l->horimetro_inicial || $l->horimetro_final)
                                        <div class="corr-sugestao">
                                            @if ($l->horimetro_inicial)
                                                <button type="button" class="btn btn-outline-secondary corr-usar" data-valor="{{ $l->horimetro_inicial }}">Início {{ $n($l->horimetro_inicial, 2) }}</button>
                                            @endif
                                            @if ($l->horimetro_final)
                                                <button type="button" class="btn btn-outline-secondary corr-usar" data-valor="{{ $l->horimetro_final }}">Fim {{ $n($l->horimetro_final, 2) }}</button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center"><a class="btn btn-outline-info btn-sm" href="{{ route('informacao.formEdit', $l->id) }}"><i class="far fa-edit"></i> Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted"><i class="fas fa-check-circle" style="color: #0ca30c;"></i> Nenhum abastecimento sem horímetro para os filtros selecionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            var rota = "{{ route('painel.consumo_medio.salvar_horimetro', '__id__') }}";

            $(document).on('click', '.corr-usar', function () {
                $(this).closest('td').find('.corr-valor').val($(this).data('valor')).focus();
            });
            $(document).on('keydown', '.corr-valor', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); $(this).closest('tr').find('.corr-salvar').click(); }
            });

            // Grava só o horímetro daquele lançamento; a linha some quando dá certo
            $(document).on('click', '.corr-salvar', function () {
                var linha = $(this).closest('tr'), botao = $(this), valor = linha.find('.corr-valor').val();
                if (!valor || parseFloat(valor) <= 0) {
                    Swal({ title: 'Informe o horímetro do abastecimento', type: 'warning', timer: 2500 });
                    return;
                }
                botao.prop('disabled', true);
                $.ajax({
                    url: rota.replace('__id__', linha.data('id')),
                    type: 'PATCH',
                    dataType: 'JSON',
                    data: { horimetro_abastecimento: valor },
                    success: function () {
                        linha.addClass('corr-salva');
                        setTimeout(function () {
                            linha.fadeOut(250, function () {
                                linha.remove();
                                $('#faltam').text($('tr[data-id]').length);
                            });
                        }, 400);
                    },
                    error: function (xhr) {
                        botao.prop('disabled', false);
                        var mensagem = 'Não foi possível gravar. Tente novamente.';
                        try {
                            var r = JSON.parse(xhr.responseText);
                            mensagem = r.errors ? $.map(r.errors, function (e) { return e[0]; }).join(' ') : (r.message || mensagem);
                        } catch (e) {}
                        Swal({ title: 'Não foi possível gravar', text: mensagem, type: 'error' });
                    }
                });
            });
        });
    </script>
@endsection
