{{--
    Prévia do custo das horas trabalhadas: (hora final - hora inicial) × custo hora.
    Lê #hora_inicial, #hora_final e #custo_hora e escreve em #custoHorasCalc (input readonly) e #custoHorasNota.
    O servidor faz a mesma conta (App\Support\CustoHoras); se a final for menor que a inicial, virou o dia.
--}}
<script>
    $(function () {
        function minutos(hora) { return parseInt(hora.substr(0, 2), 10) * 60 + parseInt(hora.substr(3, 2), 10); }
        function reais(valor) { return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }

        function calculaCustoHoras() {
            var ini = $('#hora_inicial').val(), fim = $('#hora_final').val();
            var custoHora = parseFloat($('#custo_hora').val());
            if (!ini || !fim) {
                $('#custoHorasCalc').val('');
                $('#custoHorasNota').text('Informe a hora inicial e a final');
                return;
            }
            var total = minutos(fim) - minutos(ini);
            if (total < 0) { total += 24 * 60; }
            var horas = total / 60, texto = Math.floor(total / 60) + 'h' + ('0' + (total % 60)).slice(-2);
            if (isNaN(custoHora)) {
                $('#custoHorasCalc').val('');
                $('#custoHorasNota').text(texto + ' trabalhadas · informe o custo hora');
                return;
            }
            $('#custoHorasCalc').val(reais(Math.round(horas * custoHora * 100) / 100));
            $('#custoHorasNota').text(texto + ' × ' + reais(custoHora) + '/h');
        }
        $(document).on('input change', '#hora_inicial, #hora_final, #custo_hora', calculaCustoHoras);
        calculaCustoHoras();
    });
</script>
