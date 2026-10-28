{{--
    FaixaConsumo(faixas): compara o consumo (L/h) de uma máquina com a faixa mínimo–máximo do cadastro do equipamento.
    faixas = {nome da máquina: [min, max]} (PainelBase::faixasConsumo). Usado nos painéis de Combustível e Consumo Médio.
    Situação sempre com cor + ícone + nome (nunca só a cor).
--}}
<script>
    function FaixaConsumo(faixas) {
        var u = PainelInterativo.util;
        var situacoes = {
            acima:  { nome: 'Acima da faixa', cor: '#ec835a', icone: 'fas fa-arrow-up' },
            dentro: { nome: 'Dentro da faixa', cor: '#0ca30c', icone: 'fas fa-check-circle' },
            abaixo: { nome: 'Abaixo da faixa', cor: '#fab219', icone: 'fas fa-arrow-down' },
            sem:    { nome: 'Sem faixa no cadastro', cor: '#9aa69e', icone: 'far fa-circle' }
        };
        var de = function (maquina) { return faixas[maquina] || null; };
        var chave = function (maquina, consumo) {
            var f = de(maquina);
            if (consumo === null || consumo === undefined || !f) { return 'sem'; }
            return consumo > f[1] ? 'acima' : (consumo < f[0] ? 'abaixo' : 'dentro');
        };
        return {
            de: de,
            situacao: function (maquina, consumo) { return situacoes[chave(maquina, consumo)]; },
            cor: function (maquina, consumo) { return situacoes[chave(maquina, consumo)].cor; },
            texto: function (maquina) { var f = de(maquina); return f ? '\nCadastro: ' + u.numero(f[0], 2) + ' a ' + u.numero(f[1], 2) + ' L/h' : ''; },
            // Quantas máquinas da lista estão acima da faixa
            contarAcima: function (lista, consumo) {
                var porMaquina = {};
                lista.forEach(function (f) { (porMaquina[f.maquina] = porMaquina[f.maquina] || []).push(f); });
                return Object.keys(porMaquina).filter(function (m) { return chave(m, consumo(porMaquina[m])) === 'acima'; }).length;
            }
        };
    }
</script>
