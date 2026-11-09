{{--
    Ordem de serviço da próxima revisão, para imprimir ou salvar em PDF e levar à oficina.
    Uma folha A4 por equipamento: dados da revisão, serviços e peças do Tipo de Revisão (cadastro), linhas em branco
    para o que a oficina acrescentar e os campos de assinatura dos responsáveis.
    Variáveis: $linhas (do painelRevisaoController::linhas), $situacoes.
--}}
@php
    $n = fn($valor, $casas = 2) => rtrim(rtrim(number_format((float) $valor, $casas, ',', '.'), '0'), ',');
    $linhasExtras = 3; // linhas em branco no fim de cada tabela, para a oficina acrescentar
@endphp
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ordem de serviço - Revisão{{ count($linhas) === 1 ? ' - '.$linhas[0]['equipamento'] : '' }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9ecea; color: #16241b; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .barra { position: sticky; top: 0; z-index: 1; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between;
                 padding: 10px 16px; background: #0d2b18; color: #fff; }
        .barra button, .barra a { font: inherit; font-size: 14px; font-weight: 600; border: 0; border-radius: 6px; padding: 8px 14px; cursor: pointer; text-decoration: none; }
        .barra .imprimir { background: #2fa84f; color: #fff; }
        .barra .voltar { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.5); }
        .folha { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 12mm; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.15); }
        .cabecalho { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 2px solid #0b5a2a; padding-bottom: 8px; margin-bottom: 10px; }
        .cabecalho img { height: 46px; }
        .cabecalho h1 { margin: 0; font-size: 18px; color: #0b5a2a; }
        .cabecalho .emissao { text-align: right; font-size: 11px; color: #3d4d42; }
        h2 { margin: 14px 0 6px; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; color: #0b5a2a; }
        .dados { display: grid; grid-template-columns: repeat(3, 1fr); border: 1px solid #9aa79e; }
        .dados div { padding: 5px 8px; border-right: 1px solid #c8d0ca; border-bottom: 1px solid #c8d0ca; min-height: 36px; }
        .dados div:nth-child(3n) { border-right: 0; }
        .dados span { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: #5b6b60; }
        .dados b { font-size: 12.5px; }
        .dados .largo { grid-column: span 2; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9aa79e; padding: 5px 6px; text-align: left; vertical-align: middle; }
        th { background: #e3f1e7; font-size: 10px; text-transform: uppercase; }
        td { height: 24px; }
        .num { text-align: right; white-space: nowrap; }
        .centro { text-align: center; }
        .caixa { display: inline-block; width: 13px; height: 13px; border: 1.5px solid #16241b; vertical-align: middle; }
        .aviso { padding: 6px 8px; border: 1px dashed #9aa79e; color: #5b6b60; margin-bottom: 4px; }
        .linhas div { border-bottom: 1px solid #9aa79e; height: 22px; }
        .execucao { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
        .execucao div { border-bottom: 1px solid #16241b; padding-top: 14px; font-size: 11px; }
        .assinaturas { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 34px; }
        .assinaturas div { border-top: 1px solid #16241b; padding-top: 4px; text-align: center; font-size: 11px; }
        .assinaturas small { display: block; color: #5b6b60; margin-top: 10px; text-align: left; }
        .vazio { max-width: 600px; margin: 40px auto; padding: 24px; background: #fff; border-radius: 8px; text-align: center; font-size: 15px; }
        @media print {
            body { background: #fff; }
            .barra { display: none; }
            .folha { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; page-break-after: always; break-after: page; }
            .folha:last-child { page-break-after: auto; break-after: auto; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tr, .assinaturas { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="barra">
        <span>{{ count($linhas) }} {{ count($linhas) === 1 ? 'ordem de serviço' : 'ordens de serviço' }} · para gerar o PDF, escolha <b>"Salvar como PDF"</b> no destino da impressão</span>
        <span>
            <a class="voltar" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('painel.revisoes') }}">Voltar</a>
            <button type="button" class="imprimir" onclick="window.print()">Imprimir / Salvar PDF</button>
        </span>
    </div>

    @forelse ($linhas as $l)
        <section class="folha">
            <div class="cabecalho">
                <img src="{{ asset('img/logo.png') }}" alt="KIM Logística">
                <h1>Ordem de Serviço · Revisão</h1>
                <div class="emissao">Emitida em {{ date('d/m/Y H:i') }}<br>Nº OS: ____________</div>
            </div>

            <h2>Equipamento e revisão</h2>
            <div class="dados">
                <div class="largo"><span>Veículo / Equipamento</span><b>{{ $l['equipamento'] }}</b></div>
                <div><span>Situação</span><b>{{ $situacoes[$l['situacao']] }}</b></div>
                <div class="largo"><span>Revisão a executar</span><b>{{ $l['proxima_tipo'] ?: $l['tipo'] }}</b></div>
                <div><span>Hora/KM da revisão</span><b>{{ $n($l['proxima']) }}</b></div>
                <div><span>Hora/KM atual</span><b>{{ $n($l['atual']) }}</b> <small>em {{ date('d/m/Y', strtotime($l['data_atual'])) }}</small></div>
                <div><span>{{ $l['falta'] > 0 ? 'Faltam' : 'Passou da revisão' }}</span><b>{{ $n(abs($l['falta'])) }}</b></div>
                <div><span>Última revisão</span><b>{{ $l['tipo'] }}</b> <small>em {{ date('d/m/Y', strtotime($l['data_revisao'])) }}{{ $l['nr_os'] ? ' · OS '.$l['nr_os'] : '' }}</small></div>
            </div>

            <h2>Serviços</h2>
            @if (!count($l['servicos']))
                <div class="aviso">Nenhum serviço cadastrado no Tipo de Revisão "{{ $l['proxima_tipo'] ?: $l['tipo'] }}".</div>
            @endif
            <table>
                <thead><tr><th style="width: 5%;">#</th><th>Serviço</th><th class="centro" style="width: 11%;">Executado</th><th style="width: 34%;">Observação</th></tr></thead>
                <tbody>
                    @foreach ($l['servicos'] as $i => $servico)
                        <tr><td class="centro">{{ $i + 1 }}</td><td>{{ $servico }}</td><td class="centro"><span class="caixa"></span></td><td></td></tr>
                    @endforeach
                    @for ($i = 0; $i < $linhasExtras; $i++)
                        <tr><td></td><td></td><td class="centro"><span class="caixa"></span></td><td></td></tr>
                    @endfor
                </tbody>
            </table>

            <h2>Peças</h2>
            @if (!count($l['pecas']))
                <div class="aviso">Nenhuma peça cadastrada no Tipo de Revisão "{{ $l['proxima_tipo'] ?: $l['tipo'] }}".</div>
            @endif
            <table>
                <thead><tr><th style="width: 5%;">#</th><th>Peça</th><th style="width: 16%;">Código</th><th class="num" style="width: 12%;">Qtd. prevista</th><th class="num" style="width: 13%;">Qtd. utilizada</th></tr></thead>
                <tbody>
                    @foreach ($l['pecas'] as $i => $peca)
                        <tr><td class="centro">{{ $i + 1 }}</td><td>{{ $peca['peca'] }}</td><td>{{ $peca['codigo'] }}</td><td class="num">{{ $n($peca['quantidade']) }}</td><td></td></tr>
                    @endforeach
                    @for ($i = 0; $i < $linhasExtras; $i++)
                        <tr><td></td><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>

            <h2>Óleo lubrificante</h2>
            @if (!count($l['lubrificantes']))
                <div class="aviso">Nenhum óleo lubrificante cadastrado no Tipo de Revisão "{{ $l['proxima_tipo'] ?: $l['tipo'] }}".</div>
            @endif
            <table>
                <thead><tr><th style="width: 5%;">#</th><th>Lubrificante</th><th class="num" style="width: 12%;">Qtd. prevista</th><th class="num" style="width: 13%;">Qtd. utilizada</th></tr></thead>
                <tbody>
                    @foreach ($l['lubrificantes'] as $i => $oleo)
                        <tr><td class="centro">{{ $i + 1 }}</td><td>{{ $oleo['lubrificante'] }}</td><td class="num">{{ $n($oleo['quantidade']) }}</td><td></td></tr>
                    @endforeach
                    @for ($i = 0; $i < 2; $i++)
                        <tr><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>

            <h2>Observações da oficina</h2>
            <div class="linhas"><div></div><div></div><div></div></div>

            <div class="execucao">
                <div>Data da execução: ____/____/________</div>
                <div>Hora/KM na execução:</div>
                <div>Oficina:</div>
            </div>

            <div class="assinaturas">
                <div>Solicitante · KIM Logística<small>Nome:</small><small>Data: ____/____/________</small></div>
                <div>Responsável pela oficina<small>Nome:</small><small>Data: ____/____/________</small></div>
                <div>Conferência / recebimento<small>Nome:</small><small>Data: ____/____/________</small></div>
            </div>
        </section>
    @empty
        <div class="vazio">Nenhum equipamento com revisão para imprimir com os filtros atuais.</div>
    @endforelse

    <script>
        // Abre a janela de impressão assim que a página carrega (logo incluído)
        window.addEventListener('load', function () { if (document.querySelector('.folha')) { window.print(); } });
    </script>
</body>
</html>
