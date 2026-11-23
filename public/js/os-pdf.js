/*
 * Importação da Ordem de Serviço em PDF (partials/importar-os): lê o PDF no navegador (pdf.js), liga as peças ao cadastro
 * pelo código (rota importacao_os.conferir) e preenche o formulário. Nada é gravado até o usuário salvar.
 *
 * Reimportar não duplica: a linha da mesma peça (ou do mesmo serviço, data e início) é atualizada e as outras ficam como estão.
 */
(function (window, $) {
    'use strict';

    var VALOR = '(-?[\\d.]*\\d,\\d+)';
    var RE_PRODUTO = new RegExp('^(\\S+) - (.+?) ' + [VALOR, VALOR, VALOR, VALOR].join(' ') + '$');
    var RE_SERVICO = new RegExp('(\\d{2}/\\d{2}/\\d{4})(?: (\\d{1,2}:\\d{2}))?(?: (\\d{1,2}:\\d{2}))? ' + VALOR + ' ' + VALOR + '$');

    var numero = function (t) { return parseFloat(String(t).replace(/\./g, '').replace(',', '.')); };
    var dataIso = function (t) { var p = t.split('/'); return p[2] + '-' + p[1] + '-' + p[0]; };
    var hora = function (t) { return t ? ('0' + t).slice(-5) : ''; };
    var centavos = function (v) { return Math.round(v * 100) / 100; };
    var reais = function (v) { return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); };
    var limpo = function (t) { return t.normalize('NFC').replace(/\s+/g, ' ').trim(); };
    var esc = function (t) { return $('<i>').text(t === null || t === undefined ? '' : t).html(); };
    var soma = function (lista) { return centavos(lista.reduce(function (t, i) { return t + i.valor_total; }, 0)); };

    // Textos soltos da página -> linhas, de cima para baixo: [{texto, partes: [{x, t}]}]
    function linhasDaPagina(itens) {
        var linhas = [];
        itens.filter(function (i) { return i.str && i.str.trim() !== ''; }).forEach(function (i) {
            var x = i.transform[4], y = i.transform[5];
            var linha = linhas.filter(function (l) { return Math.abs(l.y - y) <= 2.5; })[0];
            if (!linha) { linha = { y: y, partes: [] }; linhas.push(linha); }
            linha.partes.push({ x: x, fim: x + (i.width || 0), t: i.str });
        });
        linhas.sort(function (a, b) { return b.y - a.y; });
        linhas.forEach(function (l) {
            l.partes.sort(function (a, b) { return a.x - b.x; });
            // Pedaços colados são a mesma palavra (alguns geradores quebram o texto no meio)
            var texto = '';
            l.partes.forEach(function (p, n) {
                var ant = l.partes[n - 1];
                texto += (ant && p.x - ant.fim > 1 ? ' ' : '') + p.t;
            });
            l.texto = limpo(texto);
        });
        return linhas;
    }

    // Linhas do PDF -> dados da OS
    function interpretar(linhas) {
        var os = { numero: '', data: '', veiculo: '', odometro: null, executar: '', servicos: [], produtos: [], totalServicos: null, totalProdutos: null, naoLidas: [] };
        var secao = 'cabecalho', xServico = null;

        for (var n = 0; n < linhas.length; n++) {
            var l = linhas[n], t = l.texto, m;

            if (secao === 'cabecalho' || /^(DATA|VE.CULO|OD.METRO|HOR.METRO):/i.test(t)) {
                if ((m = t.match(/Ordem de Servi.o\s*N\S*\s*0*(\d+)/i))) { os.numero = os.numero || m[1]; }
                else if ((m = t.match(/^DATA:\s*(\d{2}\/\d{2}\/\d{4})/i))) { os.data = os.data || dataIso(m[1]); }
                else if ((m = t.match(/^VE.CULO:\s*(.+)$/i))) { os.veiculo = os.veiculo || m[1].replace(/\s+-(\s.*)?$/, '').trim(); }
                else if ((m = t.match(/^(?:OD.METRO|HOR.METRO):\s*([\d.,]+)/i))) { if (os.odometro === null) { os.odometro = numero(m[1]); } }
            }
            if (/^Servi.os (a )?Executad[oa]s/i.test(t)) { secao = 'servicos'; continue; }
            if (/^Servi.os a Executar/i.test(t)) { secao = 'executar'; continue; }
            if (/^Produtos Utilizados/i.test(t)) { secao = 'produtos'; continue; }
            if ((m = t.match(new RegExp('^Total Servi.os:\\s*' + VALOR, 'i')))) { os.totalServicos = numero(m[1]); secao = ''; continue; }
            if ((m = t.match(new RegExp('^Total Produtos:\\s*' + VALOR, 'i')))) { os.totalProdutos = numero(m[1]); secao = ''; continue; }
            if (/^Classifica..o de Produtos/i.test(t)) { break; } // repete os mesmos produtos

            if (secao === 'executar') {
                // Cabeçalho repetido numa quebra de página não é texto do serviço
                if (!/^(Ordem de Servi|DATA:|VE.CULO:|OD.METRO:|SOLICITANTE:|OS Pr.pria)/i.test(t)) { os.executar = limpo(os.executar + ' ' + t); }
            } else if (secao === 'servicos') {
                if (/^Mec.nico\b/i.test(t)) {
                    var cab = l.partes.filter(function (p) { return /^Servi.o/i.test(limpo(p.t)); })[0];
                    if (cab) { xServico = cab.x; }
                } else if ((m = t.match(RE_SERVICO))) {
                    // Mecânico e serviço são separados pela posição da coluna "Serviço" (os dois são texto livre)
                    var texto = limpo(t.slice(0, m.index)), mecanico = '';
                    if (xServico !== null) {
                        var parte = function (filtro) { return limpo(l.partes.filter(filtro).map(function (p) { return p.t; }).join(' ')); };
                        mecanico = parte(function (p) { return p.x < xServico - 3; });
                        if (mecanico && texto.indexOf(mecanico) === 0) { texto = limpo(texto.slice(mecanico.length)); } else { mecanico = ''; }
                    }
                    os.servicos.push({ servico: texto || 'Serviço', mecanico: mecanico, data: dataIso(m[1]), hora_inicial: hora(m[2]), hora_final: hora(m[3]),
                                       valor_hora: numero(m[4]), valor_total: numero(m[5]) });
                } else { os.naoLidas.push(t); }
            } else if (secao === 'produtos') {
                if ((m = t.match(RE_PRODUTO))) {
                    os.produtos.push({ codigo: m[1], descricao: m[2], quantidade: numero(m[3]), valor_unitario: numero(m[4]), desconto: numero(m[5]), valor_total: numero(m[6]) });
                } else if (!/^Descri..o Qtd/i.test(t)) { os.naoLidas.push(t); }
            }
        }
        // Cabeçalho de página repetido no meio da lista não é item
        os.naoLidas = os.naoLidas.filter(function (t) {
            return !/^(Ordem de Servi|DATA:|VE.CULO:|OD.METRO:|HOR.METRO:|SOLICITANTE:|OS Pr.pria|KIM LOG)|Pag: \d+$/i.test(t);
        });
        return os;
    }

    function carregarPdfjs(caixa) {
        if (window.pdfjsLib) { return Promise.resolve(window.pdfjsLib); }
        return new Promise(function (ok, erro) {
            var s = document.createElement('script');
            s.src = caixa.attr('data-pdfjs');
            s.onload = function () {
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = caixa.attr('data-worker');
                ok(window.pdfjsLib);
            };
            s.onerror = function () { erro(new Error('Não foi possível carregar o leitor de PDF.')); };
            document.head.appendChild(s);
        });
    }

    function lerPdf(pdfjs, dados) {
        return pdfjs.getDocument({ data: dados, isEvalSupported: false }).promise.then(function (doc) {
            var linhas = [], cadeia = Promise.resolve();
            for (var p = 1; p <= doc.numPages; p++) {
                (function (p) {
                    cadeia = cadeia.then(function () { return doc.getPage(p); })
                                   .then(function (pg) { return pg.getTextContent(); })
                                   .then(function (c) { linhas = linhas.concat(linhasDaPagina(c.items)); });
                })(p);
            }
            return cadeia.then(function () { return interpretar(linhas); });
        });
    }

    /********************************************* formulário ***************************************************/

    function atualizarTotais() {
        ['pecas', 'lubrificantes', 'servicos'].forEach(function (grupo) {
            var total = 0, algum = false;
            $('#itens-' + grupo + ' [data-campo=valor_total]').each(function () {
                var v = parseFloat($(this).val());
                if (!isNaN(v)) { total += v; algum = true; }
            });
            $('#total-' + grupo).text(algum ? reais(centavos(total)) : '–');
        });
    }

    // Linha vazia do quadro (reaproveitada) ou uma nova
    function novaLinha(grupo) {
        var vazia = $('#itens-' + grupo + ' tr.linha-item').filter(function () {
            return !$(this).find('select.item-id').val() && !$(this).find('input').filter(function () { return $(this).val() !== ''; }).length;
        }).first();
        if (vazia.length) { return vazia; }
        var linha = $($.trim($('#modelo-' + grupo).html()));
        $('#itens-' + grupo).append(linha);
        if (linha.find('select').chosen) { linha.find('select').chosen(); }
        return linha;
    }

    // Guarda na linha o produto da OS (para mover entre peças e óleo)
    function marcarOs(linha, p) {
        linha.data('os', p);
        linha.find('.item-mover').show();
    }

    // Código da OS que o servidor deve lembrar (vai no envio pelo data-campo)
    function lembrarCodigo(linha, codigo) {
        linha.find('[data-campo=codigo_os]').remove();
        if (codigo) { $('<input type="hidden" data-campo="codigo_os">').val(codigo).appendTo(linha.find('td').first()); }
    }

    function preencherValores(linha, p) {
        linha.find('.item-quantidade').val(p.quantidade);
        ['valor_unitario', 'desconto', 'valor_total'].forEach(function (c) { linha.find('[data-campo=' + c + ']').val(p[c]); });
    }

    // Produto da OS na lista de óleo: com o lubrificante já conhecido ou para o usuário escolher
    function preencherLubrificante(p, usadas) {
        var linha = $('#itens-lubrificantes tr.linha-item').filter(function () {
            if (usadas.indexOf(this) >= 0) { return false; }
            var tr = $(this);
            if (tr.find('[data-campo=codigo_os]').val()) { return tr.find('[data-campo=codigo_os]').val() === p.codigo; }
            return p.lubrificante && tr.find('select.item-id').val() === String(p.lubrificante);
        }).first();
        if (!linha.length) { linha = novaLinha('lubrificantes'); }
        usadas.push(linha[0]);

        var select = linha.find('select.item-id');
        select.find('option[value=novo]').remove();
        if (p.codigo) {
            $('<option value="novo">').attr({ 'data-codigo': p.codigo, 'data-descricao': p.descricao })
                                      .text('Cadastrar: ' + p.descricao).appendTo(select);
        }
        if (p.lubrificante) { select.val(p.lubrificante); }
        select.trigger('chosen:updated');
        preencherValores(linha, p);
        lembrarCodigo(linha, p.codigo);
        linha.find('.os-descricao').remove();
        $('<small class="text-muted os-descricao">').text('Na OS: ' + p.descricao + (p.codigo ? ' (' + p.codigo + ')' : '')).appendTo(linha.find('td').first());
        marcarOs(linha, p);
        return linha;
    }

    // Limpa ou tira a linha (sempre sobra uma)
    function tirarLinha(linha) {
        linha.removeData('os').find('.os-descricao, [data-campo=codigo_os]').remove();
        linha.find('.item-remover').trigger('click');
    }

    function preencherPeca(p, usadas) {
        var linha = $('#itens-pecas tr.linha-item').filter(function () {
            var op = $(this).find('select.item-id option:selected');
            if (usadas.indexOf(this) >= 0) { return false; }
            return p.id ? op.val() === String(p.id) : op.val() === 'novo' && op.attr('data-codigo') === p.codigo;
        }).first();
        if (!linha.length) { linha = novaLinha('pecas'); }
        usadas.push(linha[0]);

        var select = linha.find('select.item-id');
        if (!p.id) {
            select.find('option[value=novo]').remove();
            $('<option value="novo">').attr({ 'data-codigo': p.codigo, 'data-descricao': p.descricao })
                                      .text(p.descricao + ' (' + p.codigo + ') · NOVA').appendTo(select);
        }
        select.val(p.id || 'novo').trigger('chosen:updated');
        preencherValores(linha, p);
        marcarOs(linha, p);
        return linha;
    }

    function preencherServico(s) {
        var campo = function (linha, c) { return linha.find('[data-campo=' + c + ']'); };
        var linha = $('#itens-servicos tr.linha-item').filter(function () {
            var tr = $(this);
            return campo(tr, 'servico').val() === s.servico && campo(tr, 'mecanico').val() === s.mecanico
                && campo(tr, 'data').val() === s.data && campo(tr, 'hora_inicial').val() === s.hora_inicial;
        }).first();
        if (!linha.length) { linha = novaLinha('servicos'); }
        $.each(s, function (c, v) { campo(linha, c).val(v); });
    }

    function vazio(id, valor) {
        var el = $('#' + id), atual = el.val();
        if (valor && el.length && (!atual || atual === '0')) { el.val(valor).trigger('change').trigger('chosen:updated'); }
    }

    function preencher(os, cadastro, tela, novo) {
        // Cabeçalho: só o que ainda está em branco (a data de um lançamento novo vem com a de hoje, então vale a da OS)
        if (novo && os.data) { $('#data').val(os.data); }
        vazio('equipamento', cadastro.equipamento);
        vazio(tela === 'revisao' ? 'nr_os' : 'ord_servico', os.numero);
        vazio(tela === 'revisao' ? 'horimetro_atual' : 'horimetro', cadastro.horimetro);
        vazio('obs', os.executar.substr(0, 255));
        var inicios = os.servicos.map(function (s) { return s.hora_inicial; }).filter(Boolean).sort();
        var fins = os.servicos.map(function (s) { return s.hora_final; }).filter(Boolean).sort();
        // Com serviços em dias diferentes, início e fim não formam um período só
        var mesmoDia = os.servicos.every(function (s) { return s.data === os.servicos[0].data; });
        if (mesmoDia && !$('#hora_inicial').val() && !$('#hora_final').val()) {
            vazio('hora_inicial', inicios[0]);
            vazio('hora_final', fins[fins.length - 1]);
        }

        var usadas = [], usadasOleo = [], tiradas = 0;
        os.produtos.forEach(function (p, n) {
            var c = cadastro.pecas[n];
            p = $.extend({}, p, { id: c.id, lubrificante: c.lubrificante });
            if (c.lubrificante === null) { preencherPeca(p, usadas); return; }
            preencherLubrificante(p, usadasOleo);
            // Óleo que já estava lançado como peça (importação antiga): sai das peças para não contar duas vezes
            $('#itens-pecas tr.linha-item').filter(function () {
                var op = $(this).find('select.item-id option:selected');
                return c.id ? op.val() === String(c.id) : op.val() === 'novo' && op.attr('data-codigo') === p.codigo;
            }).first().each(function () { tirarLinha($(this)); tiradas++; });
        });
        cadastro.tiradas = tiradas;
        os.servicos.forEach(preencherServico);
        atualizarTotais();
    }

    function resumo(os, cadastro, tela) {
        var oleos = cadastro.pecas.filter(function (p) { return p.lubrificante !== null; }), avisos = [];
        var novas = cadastro.pecas.filter(function (p) { return p.lubrificante === null && !p.id; }).length;
        var aEscolher = oleos.filter(function (p) { return !p.lubrificante; }).length;
        if (oleos.length) { avisos.push(oleos.length + (oleos.length === 1 ? ' óleo foi separado' : ' óleos foram separados') + ' das peças. Confira o lubrificante e a quantidade (na OS pode estar em baldes ou tambores, e aqui é em litros).'); }
        if (aEscolher) { avisos.push('Escolha o lubrificante em ' + aEscolher + (aEscolher === 1 ? ' linha' : ' linhas') + ' do quadro Óleo lubrificante: o sistema lembra na próxima OS.'); }
        if (cadastro.tiradas) { avisos.push(cadastro.tiradas + (cadastro.tiradas === 1 ? ' linha que estava' : ' linhas que estavam') + ' nas peças passou para o óleo.'); }
        if (novas) { avisos.push(novas + (novas === 1 ? ' peça ainda não está' : ' peças ainda não estão') + ' no cadastro (marcadas com NOVA) e ' + (novas === 1 ? 'será cadastrada' : 'serão cadastradas') + ' ao salvar.'); }
        if (os.totalProdutos === null) { avisos.push('O "Total Produtos" não foi encontrado no PDF: confira as peças.'); }
        else if (Math.abs(soma(os.produtos) - os.totalProdutos) > 0.011) { avisos.push('A soma das peças lidas (' + reais(soma(os.produtos)) + ') é diferente do total da OS (' + reais(os.totalProdutos) + '): confira.'); }
        if (os.totalServicos === null) { avisos.push('O "Total Serviços" não foi encontrado no PDF: confira os serviços.'); }
        else if (Math.abs(soma(os.servicos) - os.totalServicos) > 0.011) { avisos.push('A soma dos serviços lidos (' + reais(soma(os.servicos)) + ') é diferente do total da OS (' + reais(os.totalServicos) + '): confira.'); }
        if (os.naoLidas.length) { avisos.push('Linhas do PDF que não foram lidas: ' + os.naoLidas.join(' | ')); }
        if (!cadastro.equipamento) { avisos.push('O veículo "' + os.veiculo + '" não foi encontrado no cadastro: selecione.'); }
        else if ($('#equipamento').val() && $('#equipamento').val() !== String(cadastro.equipamento)) { avisos.push('O equipamento do formulário é diferente do veículo da OS (' + os.veiculo + ').'); }
        if (os.odometro && cadastro.horimetro && cadastro.horimetro !== os.odometro) {
            avisos.push('O odômetro da OS (' + os.odometro + ') veio sem a vírgula e foi lido como ' + cadastro.horimetro.toLocaleString('pt-BR') + ': confira o horímetro.');
        }
        if (cadastro.ja_lancada.length) { avisos.push('A OS ' + os.numero + ' já está lançada em: ' + cadastro.ja_lancada.join(', ') + '.'); }

        return '<div style="margin-top: 10px;"><b style="color: #0b5a2a;"><span class="fas fa-check-circle"></span> OS ' + esc(os.numero) + ' importada:</b> '
             + (os.produtos.length - oleos.length) + ' peças' + (oleos.length ? ' e ' + oleos.length + ' de óleo' : '') + ' (' + reais(soma(os.produtos)) + ') e ' + os.servicos.length + (os.servicos.length === 1 ? ' serviço (' : ' serviços (')
             + reais(soma(os.servicos)) + '). Confira e clique em <b>Salvar</b>.</div>'
             + avisos.map(function (a) { return '<div style="color: #8a5a00; margin-top: 4px;"><span class="fas fa-exclamation-triangle"></span> ' + esc(a) + '</div>'; }).join('');
    }

    function importar(arquivo) {
        var caixa = $('#importarOs'), saida = $('#osResultado'), tela = caixa.attr('data-tela'), id = caixa.attr('data-id');
        var erro = function (msg) { saida.html('<div style="color: #b3261e; margin-top: 10px;"><span class="fas fa-times-circle"></span> ' + esc(msg) + '</div>'); };
        saida.html('<div class="text-muted" style="margin-top: 10px;">Lendo o PDF…</div>');

        return carregarPdfjs(caixa).then(function (pdfjs) {
            return arquivo.arrayBuffer().then(function (b) { return lerPdf(pdfjs, new Uint8Array(b)); });
        }).then(function (os) {
            if (!os.numero || (!os.produtos.length && !os.servicos.length)) {
                throw new Error('Este PDF não parece ser o relatório de Ordem de Serviço (não encontrei o número da OS, peças ou serviços).');
            }
            var atual = String($(tela === 'revisao' ? '#nr_os' : '#ord_servico').val() || '').replace(/^0+/, '');
            if (atual && atual !== os.numero && !window.confirm('O formulário é da OS ' + atual + ' e o PDF é da OS ' + os.numero + '. Importar mesmo assim?')) {
                saida.html('');
                return;
            }
            return new Promise(function (ok, falha) {
                $.ajax({
                    url: caixa.attr('data-rota'), type: 'POST', dataType: 'json', contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': caixa.attr('data-token'), Accept: 'application/json' },
                    data: JSON.stringify({ numero: os.numero, veiculo: os.veiculo, odometro: os.odometro, data: os.data || null, tela: tela, id: id || null,
                                           produtos: os.produtos.map(function (p) { return { codigo: p.codigo, descricao: p.descricao }; }) }),
                    success: ok,
                    error: function () { falha(new Error('Não foi possível consultar o cadastro. Atualize a página e tente de novo.')); }
                });
            }).then(function (cadastro) {
                preencher(os, cadastro, tela, !id);
                saida.html(resumo(os, cadastro, tela));
            });
        }).catch(function (e) { erro(e && e.message ? e.message : 'Não foi possível ler o PDF.'); });
    }

    window.OsPdf = { importar: importar, interpretar: interpretar, linhasDaPagina: linhasDaPagina };

    $(function () {
        $('#osImportar').on('click', function () { $('#osArquivo').val('').trigger('click'); });
        $('#osArquivo').on('change', function () { if (this.files[0]) { importar(this.files[0]); } });

        // Quantidade ou valor unitário alterado: recalcula o total da peça (menos o desconto da OS)
        $(document).on('input', '#itens-pecas .item-quantidade, #itens-pecas [data-campo=valor_unitario], #itens-lubrificantes .item-quantidade, #itens-lubrificantes [data-campo=valor_unitario]', function () {
            var tr = $(this).closest('tr'), q = parseFloat(tr.find('.item-quantidade').val()), v = parseFloat(tr.find('[data-campo=valor_unitario]').val());
            var desconto = parseFloat(tr.find('[data-campo=desconto]').val()) || 0;
            if (!isNaN(q) && !isNaN(v)) { tr.find('[data-campo=valor_total]').val(Math.max(centavos(q * v - desconto), 0)); }
        });
        // Trocar a peça de uma linha: valores da outra peça não servem
        $(document).on('change', '#itens-pecas select.item-id', function () {
            $(this).closest('tr').removeData('os').find('[data-campo]').val('');
        });
        // Linha limpa pelo x (a última do quadro): não fica nada da OS nela
        $(document).on('click', '.item-remover', function () {
            $(this).closest('tr').removeData('os').find('.os-descricao, [data-campo=codigo_os]').remove();
        });
        // Mover uma linha entre Peças e Óleo lubrificante
        $(document).on('click', '.item-mover', function () {
            var tr = $(this).closest('tr'), op = tr.find('select.item-id option:selected');
            var p = $.extend({}, tr.data('os') || {});
            ['valor_unitario', 'desconto', 'valor_total'].forEach(function (c) { p[c] = tr.find('[data-campo=' + c + ']').val(); });
            p.quantidade = tr.find('.item-quantidade').val();
            if (tr.data('grupo') === 'pecas') {
                if (!op.val()) { return; }
                // Linha que não veio da OS agora: código e nome estão no texto da opção "Peça (código)"
                var m = op.text().match(/^(.*) \(([^()]+)\)$/);
                if (!p.codigo) { p.codigo = m ? m[2] : ''; p.descricao = m ? m[1] : op.text(); p.id = op.val(); }
                p.lubrificante = p.lubrificante || null;
                tirarLinha(tr);
                preencherLubrificante(p, []);
            } else {
                if (!p.codigo) { return; }
                tirarLinha(tr);
                var nova = preencherPeca(p, []);
                lembrarCodigo(nova, p.codigo);
            }
            atualizarTotais();
        });
        $(document).on('input change click', '#itens-pecas, #itens-lubrificantes, #itens-servicos', function () { setTimeout(atualizarTotais, 0); });
        atualizarTotais();
    });
})(window, jQuery);
