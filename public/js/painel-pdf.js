/*
 * Exportar em PDF as planilhas do Painel de Operações.
 * Coloca um botão "Exportar PDF" em cada quadro com table.viz-tabela. O relatório sai como está na tela (filtros e
 * seleção dos gráficos), com a empresa e os filtros no cabeçalho, pela impressão do navegador ("Salvar como PDF").
 * Dados da página: window.painelPdf = {empresa, logo, usuario} e window.painelFiltrosAtivos = [{label, valor}].
 * <table data-pdf-ignora="dim,dim">: seleções que não filtram aquela planilha (não aparecem no cabeçalho dela).
 * <th data-pdf-fora>: coluna que não vai para o PDF (as de título "Ação", "Ações", "Formulário" ou vazio já ficam de fora).
 */
(function (window, document) {
    'use strict';

    var esc = function (t) {
        return String(t === null || t === undefined ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };
    var texto = function (el) { return el ? el.textContent.replace(/\s+/g, ' ').trim() : ''; };
    var lista = function (nos) { return Array.prototype.slice.call(nos); };
    var remover = function (el) { el.parentNode.removeChild(el); };

    var estilo = [
        '* { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }',
        'body { margin: 0; color: #16241b; font-family: Arial, Helvetica, sans-serif; font-size: 10px; }',
        '.cab { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #0b5a2a; padding-bottom: 8px; }',
        '.cab img { height: 52px; } .cab .emp { flex: 1; line-height: 1.4; } .cab .emp b { font-size: 16px; color: #0b5a2a; }',
        '.cab .emissao { text-align: right; line-height: 1.5; color: #3d4d42; }',
        'h1 { margin: 10px 0 2px; font-size: 15px; } h2 { margin: 0 0 6px; font-size: 12px; font-weight: normal; color: #3d4d42; }',
        '.filtros { border: 1px solid #c8d0ca; background: #f3f7f4; padding: 6px 8px; margin-bottom: 10px; line-height: 1.6; }',
        '.filtros span { margin: 0 14px 0 4px; display: inline-block; }',
        'table { width: 100%; border-collapse: collapse; } thead { display: table-header-group; } tfoot { display: table-row-group; }',
        'th { background: #0d2b18; color: #fff; font-size: 9px; text-align: left; } th, td { padding: 4px 5px; border: 1px solid #c8d0ca; }',
        'tr { page-break-inside: avoid; break-inside: avoid; } tbody tr:nth-child(even) { background: #f7f9f7; }',
        'tfoot td { font-weight: bold; background: #e3f1e7; } .num { text-align: right; white-space: nowrap; }',
        '.text-right { text-align: right; } .text-center { text-align: center; } small, .viz-origem { display: block; font-size: 8px; }',
        'td a { color: inherit; text-decoration: none; }'
    ].join('\n');

    // Cópia da planilha só com o que faz sentido no papel
    function copiar(tabela) {
        var copia = tabela.cloneNode(true);

        // Campos editáveis viram texto (o valor digitado não vai no clone)
        var campos = tabela.querySelectorAll('input, select, textarea');
        lista(copia.querySelectorAll('input, select, textarea')).forEach(function (c, n) {
            c.parentNode.replaceChild(document.createTextNode(c.type === 'hidden' ? '' : campos[n].value), c);
        });
        // Linhas escondidas na tela ficam de fora; com uma linha selecionada, sai só ela (igual ao total e aos gráficos)
        var linhas = tabela.querySelectorAll('tbody tr'), temSelecao = !!tabela.querySelector('tbody tr.viz-linha-sel');
        lista(copia.querySelectorAll('tbody tr')).forEach(function (tr, n) {
            if (window.getComputedStyle(linhas[n]).display === 'none' || (temSelecao && !tr.classList.contains('viz-linha-sel'))) {
                remover(tr);
            }
        });
        // Colunas de ação (botões)
        var cabecalho = lista(copia.querySelectorAll('thead th')), total = cabecalho.length, tirar = [];
        cabecalho.forEach(function (th, n) {
            if (th.hasAttribute('data-pdf-fora') || /^(ações|ação|formulário|)$/i.test(texto(th))) { tirar.push(n); }
        });
        lista(copia.rows).forEach(function (tr) {
            var celulas = lista(tr.cells);
            if (celulas.length !== total) { return; }
            tirar.forEach(function (n) { tr.removeChild(celulas[n]); });
        });
        lista(copia.querySelectorAll('button, .btn, script')).forEach(remover);
        lista(copia.querySelectorAll('i')).forEach(function (i) {
            if (!i.style.background && !i.style.backgroundColor) { remover(i); } // ícones; a marca de cor fica
        });
        lista(copia.querySelectorAll('a')).forEach(function (a) { a.removeAttribute('href'); });
        lista(copia.querySelectorAll('details')).forEach(function (d) { d.open = true; });
        lista(copia.querySelectorAll('[id]')).forEach(function (e) { e.removeAttribute('id'); });
        return { html: copia.outerHTML, colunas: total - tirar.length };
    }

    function relatorio(tabela) {
        var dados = window.painelPdf || {}, empresa = dados.empresa || {};
        var painel = texto(document.querySelector('.viz-topo h3'));
        var titulo = texto(tabela.closest('.viz-card').querySelector('h4'));
        var nome = painel + ' - ' + titulo;
        var copia = copiar(tabela);

        var filtros = (window.painelFiltrosAtivos || []).map(function (f) {
            return '<span>' + esc(f.label) + ': <b>' + esc(f.valor) + '</b></span>';
        });
        var ignora = (tabela.getAttribute('data-pdf-ignora') || '').split(',');
        var selecao = lista(document.querySelectorAll('.viz-sel-chip')).filter(function (c) {
            var x = c.querySelector('[data-pi-limpar]');
            return !x || ignora.indexOf(x.getAttribute('data-pi-limpar')) < 0;
        }).map(function (c) {
            var chip = c.cloneNode(true);
            lista(chip.querySelectorAll('a')).forEach(remover);
            return '<span>' + esc(texto(chip)) + '</span>';
        });

        var agora = new Date().toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
        var linha = function (v) { return v ? '<br>' + esc(v) : ''; };
        return {
            nome: nome,
            html: '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><title>' + esc(nome) + '</title><style>'
                + '@page { size: A4 ' + (copia.colunas > 7 ? 'landscape' : 'portrait') + '; margin: 10mm 10mm 14mm;'
                + ' @bottom-right { content: "Página " counter(page) " de " counter(pages); font: 9px Arial; color: #5b6b60; } }\n'
                + estilo + '</style></head><body>'
                + '<div class="cab"><img src="' + esc(dados.logo) + '" alt=""><div class="emp"><b>' + esc(empresa.nome) + '</b>' + linha(empresa.ramo)
                + linha(empresa.cnpj ? 'CNPJ: ' + empresa.cnpj : '') + linha(empresa.endereco) + linha(empresa.contato) + '</div>'
                + '<div class="emissao">Emitido em ' + esc(agora) + (dados.usuario ? '<br>por ' + esc(dados.usuario) : '') + '</div></div>'
                + '<h1>' + esc(painel) + '</h1><h2>' + esc(titulo) + '</h2>'
                + '<div class="filtros"><b>Filtros aplicados:</b>' + (filtros.length ? filtros.join('') : ' nenhum (todos os registros)')
                + (selecao.length ? '<br><b>Seleção nos gráficos:</b>' + selecao.join('') : '') + '</div>'
                + copia.html + '</body></html>'
        };
    }

    // Imprime num iframe escondido (sem abrir outra aba)
    function imprimir(tabela) {
        var r = relatorio(tabela), tituloPagina = document.title;
        var antigo = document.getElementById('painelPdfFrame');
        if (antigo) { remover(antigo); }
        var quadro = document.createElement('iframe');
        quadro.id = 'painelPdfFrame';
        quadro.setAttribute('aria-hidden', 'true');
        quadro.style.cssText = 'position:fixed;left:-10000px;top:0;width:1000px;height:600px;border:0;';
        quadro.onload = function () {
            var voltar = function () { document.title = tituloPagina; };
            document.title = r.nome; // nome sugerido do arquivo
            quadro.contentWindow.onafterprint = voltar;
            quadro.contentWindow.focus();
            quadro.contentWindow.print();
            setTimeout(voltar, 1000);
        };
        quadro.srcdoc = r.html;
        document.body.appendChild(quadro);
    }

    window.PainelPdf = { relatorio: relatorio, imprimir: imprimir };

    document.addEventListener('DOMContentLoaded', function () {
        lista(document.querySelectorAll('.viz-root table.viz-tabela')).forEach(function (tabela) {
            var cartao = tabela.closest('.viz-card');
            if (!cartao) { return; }
            var botao = document.createElement('button');
            botao.type = 'button';
            botao.className = 'btn btn-danger btn-sm';
            botao.style.cssText = 'float:right;margin-left:12px;';
            botao.title = 'Na janela de impressão, escolha "Salvar como PDF" no destino';
            botao.innerHTML = '<span class="fas fa-file-pdf"></span> Exportar PDF';
            botao.addEventListener('click', function () { imprimir(tabela); });
            cartao.insertBefore(botao, cartao.firstChild);
        });
    });
})(window, document);
