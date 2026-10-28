{{--
    Formulário da Compra de Combustível (inclusão e alteração).
    Variáveis: $compra (null na inclusão), $route, $type, $fornecedores, $tanques, $tipos_combustivel.
    Os id/name dos campos são lidos pelo custom.js (form#cadastro-compra_combustivel): não renomear.
--}}
@php
    $compra = $compra ?? null;
    $v   = fn($campo) => $compra ? $compra->$campo : null;
    $sel = fn($campo, $id) => $compra && $compra->$campo == $id ? 'selected' : '';
@endphp

@include('partials.estilo-formulario')

<form action="" id="cadastro-compra_combustivel" nome="cadastro-compra_combustivel" method="post" class="info-form">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="compra_combustivel">

    <div class="info-topo">
        <div>
            <small>Apontamento · {{ $compra ? 'Alteração' : 'Nova compra' }}</small>
            <h3><i class="fas fa-file-invoice-dollar"></i> Compra de Combustível</h3>
        </div>
        <span class="info-obrig">Campos com <b>*</b> são obrigatórios</span>
    </div>

    <section class="info-card">
        <div class="info-card-topo"><h4>Nota fiscal</h4></div>
        <div class="info-grid info-g3">
            <div class="info-campo"><label for="data">Data <b>*</b></label>
                <input class="form-control" type="date" name="data" id="data" value="{{ $v('data') ?? date('Y-m-d') }}">
            </div>
            <div class="info-campo"><label for="fornecedor">Fornecedor <b>*</b></label>
                <select class="form-control limpar" name="fornecedor" id="fornecedor">
                    <option value="">Selecione</option>
                    @foreach ($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}" {{ $sel('fornecedor', $fornecedor->id) }}>{{ $fornecedor->fornecedor }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="nr_nf">Nº da Nota Fiscal</label>
                <input class="form-control" type="text" maxlength="30" name="nr_nf" id="nr_nf" value="{{ $v('nr_nf') }}">
            </div>
        </div>
    </section>

    <section class="info-card">
        <div class="info-card-topo"><h4>Combustível</h4></div>
        <div class="info-grid info-g2">
            <div class="info-campo"><label for="combustivel">Tipo de combustível <b>*</b></label>
                <select class="form-control limpar" name="combustivel" id="combustivel">
                    <option value="">Selecione</option>
                    @foreach ($tipos_combustivel as $tipo)
                        <option value="{{ $tipo->id }}" {{ $sel('combustivel', $tipo->id) }}>{{ $tipo->combustivel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="info-campo"><label for="tanque">Tanque de destino <b>*</b></label>
                <select class="form-control limpar" name="tanque" id="tanque">
                    <option value="">Selecione</option>
                    @foreach ($tanques as $tanque)
                        <option value="{{ $tanque->id }}" {{ $sel('tanque', $tanque->id) }}>{{ $tanque->tanque }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="info-grid info-g3">
            <div class="info-campo"><label for="litros">Quantidade (litros) <b>*</b></label>
                <input class="form-control" type="number" step="any" min="0" name="litros" id="litros" value="{{ $v('litros') }}">
            </div>
            <div class="info-campo"><label for="valor_total">Valor total da NF (R$) <b>*</b></label>
                <input class="form-control" type="number" step="0.01" min="0" name="valor_total" id="valor_total" value="{{ $v('valor_total') }}" placeholder="Ex.: 29395,00">
            </div>
            <div class="info-campo"><label for="custo_litro">Custo por litro</label>
                <input class="form-control" type="text" id="custo_litro" value="" readonly tabindex="-1" style="background: #fff4c2; font-weight: 700;">
                <small class="text-muted">Valor total da NF ÷ quantidade</small>
            </div>
        </div>
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
            window.location.href = "{{ route('compra_combustivel.listAll') }}";
        });

        // Prévia do custo por litro (o servidor recalcula ao gravar)
        var moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
        var custo = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 4, maximumFractionDigits: 4 });
        var numero = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });
        function calculaCusto() {
            var litros = parseFloat($('#litros').val());
            var total = parseFloat($('#valor_total').val());
            var partes = [];
            if ($('#fornecedor').val()) {
                partes.push($('#fornecedor option:selected').text());
            }
            if (litros > 0 && !isNaN(total)) {
                $('#custo_litro').val(custo.format(total / litros));
                partes.push(moeda.format(total) + ' ÷ ' + numero.format(litros) + ' L = ' + custo.format(total / litros) + ' por litro');
            } else {
                $('#custo_litro').val('');
            }
            $('#resumoLancamento').text(partes.join(' · '));
        }
        $(document).on('input change', '#litros, #valor_total, #fornecedor', calculaCusto);
        calculaCusto();

    });
</script>
