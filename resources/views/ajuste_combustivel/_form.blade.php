{{--
    Formulário do Ajuste de Saldo de Combustível (inclusão e alteração).
    Variáveis: $ajuste (null na inclusão), $route, $type, $tanques, $tipos.
    Os id/name dos campos são lidos pelo custom.js (form#cadastro-ajuste_combustivel): não renomear.
--}}
@php
    $ajuste = $ajuste ?? null;
    $v = fn($campo) => $ajuste ? $ajuste->$campo : null;
    $iconesTipo = ['falta' => 'fas fa-minus-circle', 'sobra' => 'fas fa-plus-circle'];
@endphp

@include('partials.estilo-formulario')
<style>
    .info-form .ajuste-tipos { display: flex; gap: 10px; margin-top: 6px; }
    .info-form .ajuste-tipo { flex: 1 1 0; position: relative; margin: 0; }
    .info-form .ajuste-tipo input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .info-form .ajuste-tipo span { display: flex; align-items: center; justify-content: center; gap: 8px; height: 42px; border: 1px solid #cad4cc; border-radius: 8px;
                                   background: #fff; font-size: 15px; font-weight: 600; color: #3d4d42; cursor: pointer; }
    .info-form .ajuste-tipo input:focus-visible + span { outline: 2px solid #0b5a2a; outline-offset: 2px; }
    .info-form .ajuste-tipo input:checked + span.falta { background: #fdecea; border-color: #b3261e; color: #8a1c15; }
    .info-form .ajuste-tipo input:checked + span.sobra { background: #e3f1e7; border-color: #0b5a2a; color: #0b5a2a; }
</style>

<form action="" id="cadastro-ajuste_combustivel" nome="cadastro-ajuste_combustivel" method="post" class="info-form">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="ajuste_combustivel">

    <div class="info-topo">
        <div>
            <small>Apontamento · {{ $ajuste ? 'Alteração' : 'Novo ajuste' }}</small>
            <h3><i class="fas fa-balance-scale"></i> Ajuste de Saldo de Combustível</h3>
        </div>
        <span class="info-obrig">Campos com <b>*</b> são obrigatórios</span>
    </div>

    <section class="info-card">
        <div class="info-card-topo"><h4>Ajuste</h4></div>
        <div class="info-grid info-g4">
            <div class="info-campo"><label for="data">Data do ajuste <b>*</b></label>
                <input class="form-control" type="date" name="data" id="data" value="{{ $v('data') ?? date('Y-m-d') }}">
            </div>
            <div class="info-campo"><label for="tanque">Tanque a ajustar <b>*</b></label>
                <select class="form-control limpar" name="tanque" id="tanque">
                    <option value="">Selecione</option>
                    @foreach ($tanques as $tanque)
                        <option value="{{ $tanque->id }}" {{ ($ajuste ? $ajuste->tanque : request('tanque')) == $tanque->id ? 'selected' : '' }}
                                data-saldo="{{ $saldos[$tanque->id] ?? '' }}">{{ $tanque->tanque }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="quantidade">Quantidade ajustada (litros) <b>*</b></label>
                <input class="form-control" type="number" step="any" min="0" name="quantidade" id="quantidade" value="{{ $v('quantidade') }}">
            </div>
            <fieldset class="info-campo" style="border: 0; padding: 0; margin: 0;">
                <legend style="font-size: 13px; font-weight: 600; color: #3d4d42; margin: 0; width: auto;">Tipo do ajuste <b style="color: #b3261e;">*</b></legend>
                <div class="ajuste-tipos">
                    @foreach ($tipos as $chave => $nome)
                        <label class="ajuste-tipo">
                            <input type="radio" name="tipo" value="{{ $chave }}" {{ $v('tipo') === $chave ? 'checked' : '' }}>
                            <span class="{{ $chave }}"><i class="{{ $iconesTipo[$chave] }}"></i> {{ $nome }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>
        @if (!$ajuste)
            {{-- Na inclusão: compara a medição do tanque com o saldo do sistema e preenche falta/sobra --}}
            <div class="info-grid info-g3" style="margin-top: 6px; padding-top: 14px; border-top: 1px dashed #dde4de;">
                <div class="info-campo"><label for="saldo_sistema">Saldo no sistema</label>
                    <input class="form-control" type="text" id="saldo_sistema" value="" readonly tabindex="-1" style="background: #f3f5f2; font-weight: 700;">
                    <small class="text-muted">Com todos os lançamentos até hoje</small>
                </div>
                <div class="info-campo"><label for="medicao">Medição do tanque (litros) — opcional</label>
                    <input class="form-control" type="number" step="any" min="0" id="medicao" placeholder="O que a régua mediu">
                    <small class="text-muted">Preenche sozinho a quantidade e o tipo (falta ou sobra)</small>
                </div>
                <div class="info-campo" style="align-self: center;">
                    <span id="medicaoResultado" class="info-horas" hidden></span>
                </div>
            </div>
        @endif
    </section>

    <div class="info-rodape">
        <span id="resumoLancamento"></span>
        <div>
            <button type="button" name="sair" id="sair" value="" class="btn btn-outline-danger">
                <span class="fa fa-door-open"></span> Sair
            </button>
            <button type="submit" name="salvar" value="" id="salvar" class="btn btn-success">
                <span class="fas fa-save"></span> Salvar
            </button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function () {

        $('#sair').on('click', function () {
            window.location.href = "{{ route('ajuste_combustivel.listAll') }}";
        });

        // Resumo do rodapé: "COMB.01 · Falta de 120 L"
        var numero = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });
        function resumo() {
            var partes = [], tipo = $('input[name="tipo"]:checked'), qtd = parseFloat($('#quantidade').val());
            if ($('#tanque').val()) {
                partes.push($('#tanque option:selected').text());
            }
            if (tipo.length && qtd > 0) {
                partes.push(tipo.closest('label').text().trim() + ' de ' + numero.format(qtd) + ' L');
            }
            $('#resumoLancamento').text(partes.join(' · '));
        }
        $(document).on('input change', '#tanque, #quantidade, input[name="tipo"]', resumo);
        resumo();

        // Saldo do sistema do tanque escolhido e cálculo pela medição: medição − saldo = sobra (+) ou falta (−)
        function saldoSistema() {
            var s = $('#tanque option:selected').data('saldo');
            return s === '' || s === undefined ? null : parseFloat(s);
        }
        function mostraSaldo() {
            var s = saldoSistema();
            $('#saldo_sistema').val($('#tanque').val() ? (s === null ? 'Posto externo: sem saldo' : numero.format(s) + ' L') : '');
            calculaMedicao();
        }
        function calculaMedicao() {
            var s = saldoSistema(), medida = parseFloat($('#medicao').val()), aviso = $('#medicaoResultado');
            if (s === null || isNaN(medida)) {
                aviso.prop('hidden', true);
                return;
            }
            var diferenca = Math.round((medida - s) * 100) / 100;
            if (diferenca === 0) {
                aviso.text('Medição igual ao sistema: não há ajuste a fazer').prop('hidden', false);
                $('#quantidade').val('');
                $('input[name="tipo"]').prop('checked', false);
            } else {
                var tipo = diferenca > 0 ? 'sobra' : 'falta';
                $('#quantidade').val(Math.abs(diferenca));
                $('input[name="tipo"][value="' + tipo + '"]').prop('checked', true);
                aviso.text((tipo === 'sobra' ? 'Sobra' : 'Falta') + ' de ' + numero.format(Math.abs(diferenca)) + ' L').prop('hidden', false);
            }
            resumo();
        }
        $(document).on('change', '#tanque', mostraSaldo);
        $(document).on('input', '#medicao', calculaMedicao);
        mostraSaldo();

    });
</script>
