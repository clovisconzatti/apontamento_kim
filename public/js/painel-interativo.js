/*
 * Painel interativo (Painel de Operações) — gráficos que filtram uns aos outros, como no Power BI.
 *
 * A página manda os lançamentos ("fatos") e descreve os gráficos; clicar numa barra, coluna ou linha
 * da planilha seleciona aquele item e todos os outros gráficos, indicadores e planilhas passam a mostrar
 * só os lançamentos dele. O gráfico clicado continua mostrando tudo, com os outros itens apagados.
 * Clicar de novo no item (ou no "x" da seleção) desfaz.
 *
 * new PainelInterativo({
 *   fatos:       [{...}, ...],                       lançamentos, um objeto por linha
 *   rotulos:     {maquina: 'Máquina', ...},           nome de cada dimensão (para a barra de seleção)
 *   selecao:     '#painelSelecao',                    onde mostrar a seleção atual
 *   graficos:    [{el, tipo: 'barras'|'colunas', dim, valor: lista => número, formato: v => texto,
 *                  dica: (chave, lista, valor) => texto, limite, cor: (chave, lista) => cor,
 *                  partes: lista => [{valor, cor}], referencia: chave => [min, max],
 *                  chaves: [{chave, rotulo, dica}] (colunas: ordem do tempo, inclusive vazios),
 *                  acumular: true (colunas: cada uma mostra a soma até ela, ex.: saldo),
 *                  ignorar: ['dim', ...] (seleções dessas dimensões não filtram este gráfico)}],
 *   indicadores: [{el, valor: lista => texto}],
 *   tabelas:     [{el (tbody), dim (ou null = uma linha por lançamento), valor: lista => número (ordem),
 *                  celulas: (chave, lista) => [{texto|html, classe}], linhas: lista => lista (sem dim),
 *                  rodape: {el, celulas: lista => [...]}, colunas, vazio}]
 * })
 */
(function (window, $) {
    'use strict';

    var esc = function (texto) {
        return String(texto === null || texto === undefined ? '' : texto).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    var formatos = {};

    // Utilitários para as páginas montarem medidas e textos
    var util = {
        soma: function (lista, campo) {
            var t = 0;
            for (var i = 0; i < lista.length; i++) { t += +lista[i][campo] || 0; }
            return t;
        },
        // campo: nome do campo ou função que monta a chave (ex.: colaborador + dia)
        distintos: function (lista, campo, condicao) {
            var vistos = {}, n = 0;
            for (var i = 0; i < lista.length; i++) {
                if (condicao && !condicao(lista[i])) { continue; }
                var chave = typeof campo === 'function' ? campo(lista[i]) : lista[i][campo];
                if (!vistos[chave]) { vistos[chave] = true; n++; }
            }
            return n;
        },
        razao: function (a, b) { return b > 0 ? a / b : null; },
        numero: function (valor, casas) {
            if (valor === null || valor === undefined || isNaN(valor)) { return '–'; }
            casas = casas === undefined ? 1 : casas;
            formatos[casas] = formatos[casas] || new Intl.NumberFormat('pt-BR', { maximumFractionDigits: casas });
            return formatos[casas].format(valor);
        },
        data: function (iso) {
            if (!iso) { return ''; }
            var p = String(iso).substr(0, 10).split('-');
            return p[2] + '/' + p[1] + '/' + p[0];
        },
        esc: esc
    };

    function PainelInterativo(cfg) {
        this.cfg = cfg;
        this.sel = {};
        this.rotuloSel = {};
        var painel = this;

        // Um clique em qualquer item marcado com data-dim/data-chave alterna a seleção
        $(document).on('click', '[data-pi-dim]', function (e) {
            e.preventDefault();
            var el = $(this);
            painel.alternar(el.attr('data-pi-dim'), el.attr('data-pi-chave'), el.attr('data-pi-rotulo'));
        });
        $(cfg.selecao).on('click', '[data-pi-limpar]', function (e) {
            e.preventDefault();
            var dim = $(this).attr('data-pi-limpar');
            if (dim === '*') { painel.sel = {}; } else { delete painel.sel[dim]; }
            painel.desenhar();
        });

        this.desenhar();
    }

    PainelInterativo.util = util;

    PainelInterativo.prototype.alternar = function (dim, chave, rotulo) {
        if (this.sel[dim] === chave) {
            delete this.sel[dim];
        } else {
            this.sel[dim] = chave;
            this.rotuloSel[dim] = rotulo || chave;
        }
        this.desenhar();
    };

    // Lançamentos que passam por todas as seleções, menos a da dimensão informada.
    // Um lançamento com livre: {dim: 1} vale para qualquer valor daquela dimensão (ex.: horas de máquina não têm tipo de óleo).
    PainelInterativo.prototype.filtrar = function (excetoDim) {
        return this.filtrarSem(excetoDim ? [excetoDim] : []);
    };

    // Lançamentos que passam pelas seleções, ignorando as das dimensões informadas
    // (ex.: o saldo de um tanque não depende do tipo de movimento selecionado)
    PainelInterativo.prototype.filtrarSem = function (ignorar) {
        var sel = this.sel, dims = Object.keys(sel).filter(function (d) { return ignorar.indexOf(d) < 0; });
        if (!dims.length) { return this.cfg.fatos; }
        return this.cfg.fatos.filter(function (f) {
            for (var i = 0; i < dims.length; i++) {
                if (f.livre && f.livre[dims[i]]) { continue; }
                if (String(f[dims[i]]) !== sel[dims[i]]) { return false; }
            }
            return true;
        });
    };

    var agrupar = function (lista, dim) {
        var grupos = {}, ordem = [];
        for (var i = 0; i < lista.length; i++) {
            var chave = String(lista[i][dim]);
            if (!grupos[chave]) { grupos[chave] = []; ordem.push(chave); }
            grupos[chave].push(lista[i]);
        }
        return { grupos: grupos, ordem: ordem };
    };

    PainelInterativo.prototype.desenhar = function () {
        var painel = this;
        (this.cfg.graficos || []).forEach(function (g) {
            g.tipo === 'colunas' ? painel.colunas(g) : painel.barras(g);
        });
        var todos = this.filtrar(null);
        (this.cfg.indicadores || []).forEach(function (k) { $(k.el).text(k.valor(todos)); });
        (this.cfg.tabelas || []).forEach(function (t) { painel.tabela(t, todos); });
        this.mostrarSelecao();
    };

    PainelInterativo.prototype.mostrarSelecao = function () {
        var cfg = this.cfg, sel = this.sel, rotulos = this.rotuloSel, dims = Object.keys(sel);
        var alvo = $(cfg.selecao);
        if (!dims.length) {
            alvo.html('<span class="viz-sel-dica"><i class="fas fa-mouse-pointer"></i> Clique numa barra, coluna ou linha da planilha para filtrar os outros gráficos.</span>');
            return;
        }
        var html = '<span class="viz-sel-titulo"><i class="fas fa-filter"></i> Selecionado:</span>';
        dims.forEach(function (d) {
            html += '<span class="viz-sel-chip">' + esc((cfg.rotulos || {})[d] || d) + ': <b>' + esc(rotulos[d]) + '</b>'
                  + ' <a href="#" data-pi-limpar="' + esc(d) + '" aria-label="Tirar esta seleção">&times;</a></span>';
        });
        html += '<a href="#" class="viz-sel-limpar" data-pi-limpar="*">Limpar seleção</a>';
        alvo.html(html);
    };

    var atributos = function (dim, chave, rotulo) {
        return ' data-pi-dim="' + esc(dim) + '" data-pi-chave="' + esc(chave) + '" data-pi-rotulo="' + esc(rotulo) + '"';
    };

    PainelInterativo.prototype.barras = function (g) {
        var dim = g.dim, selecionado = this.sel[dim];
        var r = agrupar(this.filtrarSem([dim].concat(g.ignorar || [])), dim);
        var itens = r.ordem.map(function (chave) {
            var lista = r.grupos[chave];
            return { chave: chave, lista: lista, valor: g.valor(lista) || 0 };
        }).filter(function (i) { return i.valor > 0; })
          .sort(function (a, b) { return b.valor - a.valor; });

        // Item selecionado nunca some no "Outros"
        var outros = null;
        if (g.limite && itens.length > g.limite) {
            var visiveis = itens.slice(0, g.limite), resto = itens.slice(g.limite);
            var idx = resto.findIndex(function (i) { return i.chave === selecionado; });
            if (idx >= 0) { visiveis.push(resto.splice(idx, 1)[0]); }
            outros = { quantidade: resto.length, valor: resto.reduce(function (t, i) { return t + i.valor; }, 0) };
            itens = visiveis;
        }

        var maximo = 0;
        itens.forEach(function (i) {
            maximo = Math.max(maximo, i.valor, g.referencia && g.referencia(i.chave) ? g.referencia(i.chave)[1] : 0);
        });
        if (outros) { maximo = Math.max(maximo, outros.valor); }
        maximo = maximo * 1.04 || 1;
        var pos = function (v) { return Math.round(Math.max(v, 0) / maximo * 10000) / 100; };

        var html = '';
        itens.forEach(function (i) {
            var apagado = selecionado !== undefined && selecionado !== i.chave ? ' viz-apagado' : '';
            var dica = g.dica ? g.dica(i.chave, i.lista, i.valor) : i.chave + ': ' + g.formato(i.valor);
            var attrs = atributos(dim, i.chave, i.chave);
            var barra = '';
            var ref = g.referencia ? g.referencia(i.chave) : null;
            if (ref) {
                barra += '<div class="viz-ref" style="left:' + pos(ref[0]) + '%;width:' + Math.max(0.6, pos(ref[1]) - pos(ref[0])) + '%"></div>';
            }
            if (g.partes) {
                barra += '<div class="viz-pilha" style="width:' + pos(i.valor) + '%">';
                g.partes(i.lista).forEach(function (p) {
                    if (p.valor > 0) { barra += '<div style="flex:' + p.valor + ' 1 0;background:' + p.cor + '"></div>'; }
                });
                barra += '</div>';
            } else {
                var cor = g.cor ? g.cor(i.chave, i.lista, i.valor) : 'var(--series-1)';
                barra += '<div class="viz-barra" style="width:' + pos(i.valor) + '%;background:' + cor + '"></div>';
            }
            html += '<a href="#" class="rotulo viz-clicavel' + apagado + '" title="' + esc(i.chave) + '"' + attrs + '>' + esc(i.chave) + '</a>'
                  + '<div class="viz-trilho viz-clicavel' + apagado + '" data-tip="' + esc(dica) + '"' + attrs + '>' + barra + '</div>'
                  + '<div class="viz-valor' + apagado + '">' + esc(g.formato(i.valor)) + '</div>';
        });
        if (outros) {
            html += '<div class="rotulo text-muted">Outros (' + outros.quantidade + ')</div>'
                  + '<div class="viz-trilho" data-tip="' + outros.quantidade + ' itens somados"><div class="viz-barra" style="width:' + pos(outros.valor) + '%;background:#9aa69e"></div></div>'
                  + '<div class="viz-valor">' + esc(g.formato(outros.valor)) + '</div>';
        }
        if (!itens.length) {
            html = '<div class="text-muted" style="grid-column:1/-1">Sem dados para a seleção.</div>';
        }
        $(g.el).html(html);
    };

    PainelInterativo.prototype.colunas = function (g) {
        var dim = g.dim, selecionado = this.sel[dim];
        var r = agrupar(this.filtrarSem([dim].concat(g.ignorar || [])), dim);
        var chaves = g.chaves || r.ordem.map(function (c) { return { chave: c, rotulo: c, dica: c }; });
        var itens = chaves.map(function (c) {
            var lista = r.grupos[String(c.chave)] || [];
            return { c: c, valor: lista.length ? g.valor(lista) || 0 : 0 };
        });
        // acumular: cada coluna mostra a soma até ela (ex.: saldo no fim de cada período)
        if (g.acumular) {
            var corrente = 0;
            itens.forEach(function (i) { corrente += i.valor; i.valor = corrente; });
        }
        var maximo = Math.max.apply(null, itens.map(function (i) { return i.valor; }).concat([0])) || 1;
        var maior = itens.findIndex(function (i) { return i.valor === maximo; });
        var passo = Math.ceil(itens.length / 12) || 1;
        var grade = 'grid-template-columns:repeat(' + Math.max(itens.length, 1) + ',minmax(0,1fr))';

        var colunas = '', rotulos = '';
        itens.forEach(function (i, n) {
            var apagado = selecionado !== undefined && selecionado !== String(i.c.chave) ? ' viz-apagado' : '';
            // Valor negativo (ex.: saldo abaixo de zero): coluna vazia e número em vermelho
            var negativo = i.valor < 0;
            var cap = (i.valor > 0 && (n === maior || n === itens.length - 1)) || (negativo && n === itens.length - 1)
                ? '<span class="cap"' + (negativo ? ' style="color:#b3261e"' : '') + '>' + esc(g.formato(i.valor)) + '</span>' : '';
            colunas += '<div class="viz-coluna viz-clicavel' + apagado + '" data-tip="' + esc(i.c.dica + ': ' + g.formato(i.valor)) + '"'
                     + atributos(dim, i.c.chave, i.c.dica) + '>' + cap
                     + '<div class="fill" style="height:' + Math.round(Math.max(i.valor, 0) / maximo * 8500) / 100 + '%;background:' + (g.corFixa || 'var(--series-1)') + '"></div></div>';
            rotulos += '<span>' + (n % passo === 0 ? esc(i.c.rotulo) : '') + '</span>';
        });
        $(g.el).html('<div class="viz-colunas" style="' + grade + '">' + colunas + '</div><div class="viz-meses" style="' + grade + '">' + rotulos + '</div>');
    };

    var celula = function (c) {
        var conteudo = c.html !== undefined ? c.html : esc(c.texto);
        return '<td' + (c.classe ? ' class="' + c.classe + '"' : '') + (c.estilo ? ' style="' + c.estilo + '"' : '') + '>' + conteudo + '</td>';
    };

    PainelInterativo.prototype.tabela = function (t, todos) {
        var html = '';
        if (t.dim) {
            var dim = t.dim, selecionado = this.sel[dim];
            // A planilha agrupada segue as outras seleções, mas mantém todas as suas linhas (a selecionada fica marcada)
            var r = agrupar(this.filtrarSem([dim].concat(t.ignorar || [])), dim);
            var linhas = r.ordem.map(function (chave) { return { chave: chave, lista: r.grupos[chave] }; });
            if (t.valor) {
                linhas.forEach(function (l) { l.valor = t.valor(l.lista) || 0; });
                linhas = linhas.filter(function (l) { return l.valor > 0; }).sort(function (a, b) { return b.valor - a.valor; });
            }
            linhas.forEach(function (l) {
                var marcada = selecionado === l.chave ? ' class="viz-linha-sel"' : '';
                html += '<tr' + marcada + atributos(dim, l.chave, l.chave) + ' style="cursor:pointer">'
                      + t.celulas(l.chave, l.lista).map(celula).join('') + '</tr>';
            });
            if (t.rodape) {
                $(t.rodape.el).html(linhas.length ? t.rodape.celulas(this.filtrar(null)).map(celula).join('') : '');
            }
        } else {
            (t.linhas ? t.linhas(todos) : todos).forEach(function (f) {
                html += '<tr>' + t.celulas(f).map(celula).join('') + '</tr>';
            });
        }
        if (!html) {
            html = '<tr><td colspan="' + (t.colunas || 1) + '" class="text-center text-muted">' + esc(t.vazio || 'Nada para a seleção atual.') + '</td></tr>';
        }
        $(t.el).html(html);
    };

    window.PainelInterativo = PainelInterativo;
})(window, jQuery);
