{{--
    Serviços, peças e lubrificantes da próxima revisão (cadastro do Tipo de Revisão), recolhidos num "ver mais".
    Variável: $l (linha do painelRevisaoController::linhas).
--}}
@php
    $qtd = fn($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $resumo = [];
    if (count($l['servicos'])) { $resumo[] = count($l['servicos']).(count($l['servicos']) === 1 ? ' serviço' : ' serviços'); }
    if (count($l['pecas'])) { $resumo[] = count($l['pecas']).(count($l['pecas']) === 1 ? ' peça' : ' peças'); }
    if (count($l['lubrificantes'])) { $resumo[] = count($l['lubrificantes']).(count($l['lubrificantes']) === 1 ? ' lubrificante' : ' lubrificantes'); }
@endphp
@if ($resumo)
    <details>
        <summary style="cursor: pointer; font-weight: 600;">{{ implode(' · ', $resumo) }}</summary>
        @if (count($l['servicos']))
            <div style="margin-top: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; opacity: .8;">Serviços</div>
            <ul style="margin: 2px 0 0; padding-left: 18px;">
                @foreach ($l['servicos'] as $servico)
                    <li>{{ $servico }}</li>
                @endforeach
            </ul>
        @endif
        @if (count($l['pecas']))
            <div style="margin-top: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; opacity: .8;">Peças</div>
            <ul style="margin: 2px 0 0; padding-left: 18px;">
                @foreach ($l['pecas'] as $peca)
                    <li>{{ $qtd($peca['quantidade']) }} × {{ $peca['peca'] }}{{ $peca['codigo'] ? ' ('.$peca['codigo'].')' : '' }}</li>
                @endforeach
            </ul>
        @endif
        @if (count($l['lubrificantes']))
            <div style="margin-top: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; opacity: .8;">Óleo lubrificante</div>
            <ul style="margin: 2px 0 0; padding-left: 18px;">
                @foreach ($l['lubrificantes'] as $oleo)
                    <li>{{ $qtd($oleo['quantidade']) }} × {{ $oleo['lubrificante'] }}</li>
                @endforeach
            </ul>
        @endif
    </details>
@else
    <span style="opacity: .75;">Nada cadastrado no tipo de revisão</span>
@endif
