{{-- Estilo das telas do Painel de Operações (cores de status, cartões, gráficos, planilha). Uso: <div class="viz-root"> --}}
<style>
    .viz-root {
        --surface-1: #ffffff;
        --text-primary: #16241b;
        --text-secondary: #4a5a4f;
        --grid: #e3e8e4;
        --series-1: #2a78d6;
        --good: #0ca30c;
        --warning: #fab219;
        --critical: #d03b3b;
        color: var(--text-primary);
    }
    .viz-root .viz-topo { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .viz-root .viz-topo small { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .06em; }
    .viz-root .viz-topo h3 { margin: 0; }
    .viz-root .viz-card { background: var(--surface-1); border: 1px solid #dde4de; border-radius: 12px; padding: 18px 20px; margin-bottom: 16px; }
    .viz-root .viz-card h4 { margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #0b5a2a; }
    .viz-root .viz-card .viz-sub { margin: 0 0 14px; font-size: 13px; color: var(--text-secondary); }

    /* Indicadores */
    .viz-root .viz-tiles { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .viz-root .viz-tile { display: block; background: var(--surface-1); border: 1px solid #dde4de; border-radius: 12px; padding: 14px 16px; color: var(--text-primary); text-decoration: none; }
    .viz-root a.viz-tile:hover { border-color: #9fb3a5; text-decoration: none; color: var(--text-primary); }
    .viz-root .viz-tile-label { font-size: 13px; font-weight: 600; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; }
    .viz-root .viz-tile-valor { font-size: 30px; font-weight: 700; line-height: 1.2; margin-top: 4px; }
    .viz-root .viz-tile-nota { font-size: 12px; color: var(--text-secondary); }

    /* Barras horizontais: uso do intervalo */
    .viz-root .viz-barras { display: grid; grid-template-columns: minmax(120px, 220px) minmax(0, 1fr) 52px; column-gap: 12px; row-gap: 4px; align-items: center; }
    .viz-root .viz-barras .rotulo { font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .viz-root .viz-trilho { position: relative; height: 26px; }
    .viz-root .viz-barra { position: absolute; left: 0; top: 5px; height: 16px; border-radius: 0 4px 4px 0; }
    .viz-root .viz-marca { position: absolute; top: 0; bottom: 0; width: 1px; background: #9aa69e; }
    .viz-root .viz-faixa { position: absolute; top: 0; bottom: 0; background: rgba(250, 178, 25, .12); }
    .viz-root .viz-valor { font-size: 13px; font-weight: 600; text-align: right; font-variant-numeric: tabular-nums; }
    .viz-root .viz-eixo { position: relative; height: 18px; font-size: 11px; color: var(--text-secondary); }
    .viz-root .viz-eixo span { position: absolute; transform: translateX(-50%); white-space: nowrap; }
    .viz-root .viz-legenda { display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: var(--text-secondary); margin-top: 10px; }
    .viz-root .viz-legenda i.cor { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }

    /* Colunas: revisões por mês */
    .viz-root .viz-colunas { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 2px; align-items: end; height: 180px; border-bottom: 1px solid var(--grid); }
    .viz-root .viz-coluna { position: relative; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; height: 100%; }
    .viz-root .viz-coluna .fill { width: 60%; max-width: 24px; background: var(--series-1); border-radius: 4px 4px 0 0; min-height: 0; }
    .viz-root .viz-coluna .cap { font-size: 12px; font-weight: 600; margin-bottom: 4px; }
    .viz-root .viz-meses { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 2px; font-size: 11px; color: var(--text-secondary); text-align: center; margin-top: 4px; }

    /* Planilha */
    .viz-root .viz-tabela-box { overflow-x: auto; }
    .viz-root table.viz-tabela { width: 100%; border-collapse: collapse; font-size: 13px; }
    .viz-root .viz-tabela th { background: #0d2b18; color: #fff; font-weight: 600; padding: 10px; white-space: nowrap; }
    .viz-root .viz-tabela td { padding: 9px 10px; border-bottom: 1px solid #e3e8e4; vertical-align: middle; }
    .viz-root .viz-tabela td.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .viz-root .viz-selo { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; white-space: nowrap; }
    .viz-root .viz-origem { display: block; font-size: 11px; opacity: .8; }

    .viz-root a.viz-tile .viz-tile-ver { display: block; margin-top: 6px; font-size: 12px; font-weight: 700; color: #0b5a2a; }
    .viz-root a.viz-tile:hover .viz-tile-ver { text-decoration: underline; }

    /* Lista de trabalho (uma situação) */
    .viz-root .viz-lista { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
    .viz-root .viz-item { border: 1px solid #dde4de; border-radius: 12px; padding: 16px 18px; background: var(--surface-1); display: flex; flex-direction: column; gap: 10px; }
    .viz-root .viz-item-topo { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .viz-root .viz-item-nome { font-size: 17px; font-weight: 700; margin: 0; }
    .viz-root .viz-item-tipo { font-size: 13px; opacity: .85; }
    .viz-root .viz-item-destaque { font-size: 26px; font-weight: 700; line-height: 1.1; }
    .viz-root .viz-item-destaque small { display: block; font-size: 12px; font-weight: 600; opacity: .85; }
    .viz-root .viz-item-dados { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; font-size: 13px; }
    .viz-root .viz-item-dados span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; opacity: .8; }
    .viz-root .viz-item-dados b { font-variant-numeric: tabular-nums; }
    .viz-root .viz-medidor { position: relative; height: 10px; border-radius: 5px; background: rgba(22, 36, 27, .12); overflow: hidden; }
    .viz-root .viz-medidor div { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 5px; background: #16241b; }
    .viz-root .viz-item .btn { align-self: flex-start; }
    .viz-root .viz-vazio { text-align: center; padding: 40px 20px; color: var(--text-secondary); }
    .viz-root .viz-vazio i { font-size: 36px; color: var(--good); display: block; margin-bottom: 10px; }

    .viz-tip { position: fixed; z-index: 1000; pointer-events: none; background: #16241b; color: #fff; font-size: 13px; line-height: 1.45; padding: 8px 10px; border-radius: 6px; max-width: 280px; box-shadow: 0 4px 12px rgba(0,0,0,.2); white-space: pre-line; }
    @media (max-width: 991px) { .viz-root .viz-tiles { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .viz-root .viz-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .viz-root .viz-barras { grid-template-columns: minmax(90px, 120px) minmax(0, 1fr) 44px; }
    }
</style>
