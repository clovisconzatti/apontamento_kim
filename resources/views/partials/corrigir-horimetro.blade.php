{{--
    Janela para corrigir o horímetro de um abastecimento "a conferir" (ConsumoAbastecimento::intervalos()['conferir']).
    Variável: $problemas ([id do lançamento => problema]). Abre com um botão .conferir-abrir[data-id].
    Explica o problema com os números, sugere o valor provável (vírgula no lugar errado, horímetro do dia) e grava só
    o horímetro de abastecimento daquele lançamento (rota painel.consumo_medio.salvar_horimetro); depois recarrega a página.
--}}
<style>
    .corr-fundo { position: fixed; inset: 0; z-index: 2000; background: rgba(13, 43, 24, .45); display: flex; align-items: center; justify-content: center; padding: 16px; }
    .corr-janela { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; max-height: calc(100vh - 32px); overflow: auto; box-shadow: 0 12px 40px rgba(0, 0, 0, .25); color: #16241b; }
    .corr-topo { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px 20px 10px; border-bottom: 1px solid #e3e8e4; }
    .corr-topo h4 { margin: 0; font-size: 18px; font-weight: 700; color: #b3261e; }
    .corr-topo small { display: block; color: #4a5a4f; font-size: 13px; margin-top: 2px; }
    .corr-fechar { border: 0; background: none; font-size: 24px; line-height: 1; color: #4a5a4f; cursor: pointer; padding: 0 4px; }
    .corr-corpo { padding: 14px 20px 18px; display: flex; flex-direction: column; gap: 12px; }
    .corr-problema { background: #fdecea; border-radius: 8px; padding: 10px 12px; font-size: 14px; line-height: 1.45; }
    .corr-dados { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .corr-dado { border: 1px solid #e3e8e4; border-radius: 8px; padding: 8px 10px; }
    .corr-dado span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #4a5a4f; }
    .corr-dado b { font-size: 16px; font-variant-numeric: tabular-nums; }
    .corr-dado.errado { border-color: #d03b3b; background: #fff7f6; }
    .corr-dado small { display: block; font-size: 11px; color: #4a5a4f; }
    .corr-sugestoes { display: flex; flex-wrap: wrap; gap: 6px; }
    .corr-sugestoes .btn { font-size: 13px; }
    .corr-campo { display: flex; gap: 8px; align-items: flex-end; }
    .corr-campo label { flex: 1; margin: 0; font-size: 13px; font-weight: 600; color: #3d4d42; }
    .corr-campo input { display: block; width: 100%; height: 42px; margin-top: 4px; padding: 0 12px; border: 1px solid #cad4cc; border-radius: 8px; font: inherit; font-size: 16px; text-align: right; }
    .corr-rodape { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
    @media (max-width: 520px) { .corr-dados { grid-template-columns: minmax(0, 1fr); } }
</style>

<div class="corr-fundo" id="corrFundo" hidden>
    <div class="corr-janela" role="dialog" aria-modal="true" aria-labelledby="corrTitulo">
        <div class="corr-topo">
            <div>
                <h4 id="corrTitulo"><i class="fas fa-exclamation-triangle"></i> Horímetro do abastecimento a conferir</h4>
                <small id="corrSubtitulo"></small>
            </div>
            <button type="button" class="corr-fechar" id="corrFechar" aria-label="Fechar">&times;</button>
        </div>
        <div class="corr-corpo">
            <div class="corr-problema" id="corrProblema"></div>
            <div class="corr-dados" id="corrDados"></div>
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #3d4d42; margin-bottom: 6px;" id="corrSugestoesTitulo">Valor provável</div>
                <div class="corr-sugestoes" id="corrSugestoes"></div>
            </div>
            <div class="corr-campo">
                <label for="corrValor">Horímetro correto do abastecimento
                    <input type="number" step="any" min="0" id="corrValor">
                </label>
                <button type="button" class="btn btn-success" id="corrSalvar" style="height: 42px;"><i class="fas fa-save"></i> Salvar</button>
            </div>
            <div class="corr-rodape">
                <a href="#" id="corrEditar" class="btn btn-outline-info btn-sm"><i class="far fa-edit"></i> Abrir o lançamento completo</a>
                <small class="text-muted">Grava só o horímetro do abastecimento deste lançamento.</small>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        var problemas = @json((object) $problemas);
        var rotaSalvar = "{{ route('painel.consumo_medio.salvar_horimetro', '__id__') }}";
        var rotaEditar = "{{ url('informacao/editar') }}/";
        var MAXIMO = {{ App\Support\ConsumoAbastecimento::INTERVALO_MAXIMO }}, TOLERANCIA = {{ App\Support\ConsumoAbastecimento::TOLERANCIA_DIA }};
        var fmt = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });
        var n = function (v) { return v === null || v === undefined || v === '' ? '–' : fmt.format(v); };
        var d = function (iso) { if (!iso) { return ''; } var p = String(iso).substr(0, 10).split('-'); return p[2] + '/' + p[1] + '/' + p[0]; };
        var esc = function (t) { return $('<div>').text(t).html(); };
        var atual = null, origemFoco = null;

        // O valor proposto é coerente? (perto do horímetro do dia e, se houver, depois do anterior em até MAXIMO horas)
        function coerente(p, v) {
            var dia = [+p.dia_inicial || 0, +p.dia_final || 0].filter(function (h) { return h > 0; });
            if (dia.length && (v < Math.min.apply(null, dia) - TOLERANCIA || v > Math.max.apply(null, dia) + TOLERANCIA)) { return false; }
            if (p.horimetro_ref && !(v > p.horimetro_ref && v - p.horimetro_ref <= MAXIMO)) { return false; }
            return true;
        }

        function sugestoes(p) {
            var lista = [];
            var add = function (v, rotulo) {
                v = Math.round(v * 100) / 100;
                if (v > 0 && v !== p.horimetro && !lista.some(function (s) { return s.v === v; })) { lista.push({ v: v, rotulo: rotulo }); }
            };
            // Vírgula no lugar errado ou zero a mais/a menos
            [10, 100, 1000].forEach(function (f) {
                if (coerente(p, p.horimetro / f)) { add(p.horimetro / f, 'Vírgula: ' + n(p.horimetro / f)); }
                if (coerente(p, p.horimetro * f)) { add(p.horimetro * f, 'Faltou dígito: ' + n(p.horimetro * f)); }
            });
            if (+p.dia_inicial > 0) { add(+p.dia_inicial, 'Início do dia: ' + n(p.dia_inicial)); }
            if (+p.dia_final > 0) { add(+p.dia_final, 'Fim do dia: ' + n(p.dia_final)); }
            return lista;
        }

        function explicacao(p) {
            if (p.tipo === 'dia') {
                return 'O horímetro informado no abastecimento (<b>' + n(p.horimetro) + '</b>) está longe do horímetro lançado no mesmo dia '
                     + '(<b>' + n(p.dia_inicial) + '</b> a <b>' + n(p.dia_final) + '</b>). Provável erro de digitação.';
            }
            if (p.tipo === 'seguintes') {
                return 'Os abastecimentos seguintes não seguem este horímetro (<b>' + n(p.horimetro) + '</b>): o seguinte foi <b>'
                     + n(p.horimetro_seg) + '</b> em ' + d(p.data_seg) + '. Confira este valor.';
            }
            var dif = p.horimetro - p.horimetro_ref;
            return dif < 0
                ? 'O horímetro (<b>' + n(p.horimetro) + '</b>) é <b>menor</b> que o do abastecimento anterior (<b>' + n(p.horimetro_ref) + '</b> em ' + d(p.data_ref) + ').'
                : 'O horímetro (<b>' + n(p.horimetro) + '</b>) está <b>' + n(dif) + ' h</b> acima do abastecimento anterior (<b>' + n(p.horimetro_ref) + '</b> em '
                  + d(p.data_ref) + '): mais de ' + MAXIMO + ' h entre dois abastecimentos.';
        }

        function abrir(id, botao) {
            var p = problemas[id];
            if (!p) { return; }
            atual = id; origemFoco = botao;
            $('#corrSubtitulo').text(p.maquina + ' · abastecimento de ' + d(p.data) + ' · ' + n(p.litros) + ' L');
            $('#corrProblema').html(explicacao(p));
            $('#corrDados').html(
                '<div class="corr-dado"><span>Abastecimento anterior</span><b>' + n(p.horimetro_ref) + '</b><small>' + (p.data_ref ? d(p.data_ref) : 'sem anterior') + '</small></div>'
              + '<div class="corr-dado errado"><span>Este abastecimento</span><b>' + n(p.horimetro) + '</b><small>' + d(p.data) + '</small></div>'
              + '<div class="corr-dado"><span>Horímetro do dia</span><b>' + (p.dia_inicial || p.dia_final ? n(p.dia_inicial) + ' – ' + n(p.dia_final) : '–') + '</b><small>inicial – final</small></div>'
            );
            var s = sugestoes(p);
            $('#corrSugestoesTitulo').prop('hidden', !s.length);
            $('#corrSugestoes').html(s.map(function (x) {
                return '<button type="button" class="btn btn-outline-success corr-usar" data-valor="' + x.v + '">' + esc(x.rotulo) + '</button>';
            }).join(''));
            $('#corrValor').val(s.length ? s[0].v : '');
            $('#corrEditar').attr('href', rotaEditar + id);
            $('#corrSalvar').prop('disabled', false);
            $('#corrFundo').prop('hidden', false);
            $('#corrValor').focus().select();
        }

        function fechar() {
            $('#corrFundo').prop('hidden', true);
            if (origemFoco) { origemFoco.focus(); }
        }

        $(document).on('click', '.conferir-abrir', function () { abrir($(this).data('id'), this); });
        $(document).on('click', '.corr-usar', function () { $('#corrValor').val($(this).data('valor')).focus(); });
        $('#corrFechar').on('click', fechar);
        $('#corrFundo').on('click', function (e) { if (e.target === this) { fechar(); } });
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && !$('#corrFundo').prop('hidden')) { fechar(); }
        });
        $('#corrValor').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('#corrSalvar').click(); } });

        $('#corrSalvar').on('click', function () {
            var valor = $('#corrValor').val(), botao = $(this);
            if (!valor || parseFloat(valor) <= 0) {
                Swal({ title: 'Informe o horímetro correto do abastecimento', type: 'warning', timer: 2500 });
                return;
            }
            botao.prop('disabled', true);
            $.ajax({
                url: rotaSalvar.replace('__id__', atual),
                type: 'PATCH',
                dataType: 'JSON',
                data: { horimetro_abastecimento: valor },
                success: function () {
                    Swal({ title: 'Horímetro corrigido', type: 'success', timer: 1500 });
                    setTimeout(function () { window.location.reload(); }, 900);
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
