{{--
    Saldo dos tanques em destaque (tela inicial). Variável: $saldosTanques (App\Support\SaldoCombustivel::saldos()).
    Cada cartão: saldo, nível em relação à capacidade e situação (cor + ícone + nome, nunca só a cor).
--}}
@php
    $nl = fn($v) => number_format((float) $v, 0, ',', '.');
    $situacoes = App\Support\SaldoCombustivel::SITUACOES;
    $ultimo = $saldosTanques->max('ultimo_lancamento');
    $semInicial = $saldosTanques->where('sem_saldo_inicial', true)->count();
@endphp
<style>
    .kim-saldos { background: #fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(13, 43, 24, .08); padding: 16px 18px; margin-top: 18px; border-top: 4px solid #0b5a2a; }
    .kim-saldos-topo { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
    .kim-saldos-topo h2 { margin: 0; font-size: 19px; font-weight: 700; color: #0d2b18; }
    .kim-saldos-topo small { color: #4a5a4f; font-size: 13px; }
    .kim-saldos-grade { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
    .kim-saldo { display: block; border: 1px solid #e3e8e4; border-radius: 10px; padding: 10px 12px; color: #16241b; text-decoration: none; }
    .kim-saldo:hover { border-color: #9fb3a5; text-decoration: none; color: #16241b; }
    .kim-saldo-nome { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kim-saldo-valor { font-size: 22px; font-weight: 700; line-height: 1.2; }
    .kim-saldo-nivel { height: 8px; border-radius: 4px; background: #e6ebe7; overflow: hidden; margin: 6px 0 4px; }
    .kim-saldo-nivel div { height: 100%; border-radius: 4px; }
    .kim-saldo-rodape { display: flex; justify-content: space-between; gap: 6px; font-size: 11px; color: #4a5a4f; }
    .kim-saldo-aviso { display: block; margin-top: 4px; font-size: 11px; font-weight: 600; color: #8a5a00; }
</style>

<div class="kim-saldos">
    <div class="kim-saldos-topo">
        <h2><i class="fas fa-battery-three-quarters" style="color: #0b5a2a;"></i> Saldo dos tanques</h2>
        <small>
            Saldo em {{ date('d/m/Y') }}{{ $ultimo ? ' · último lançamento em '.date('d/m/Y', strtotime($ultimo)) : '' }}
            @if (Route::has('painel.saldo_combustivel'))
                · <a href="{{ route('painel.saldo_combustivel') }}"><b>Ver painel completo →</b></a>
            @endif
        </small>
    </div>
    @if ($semInicial)
        <p style="margin: 0 0 10px; font-size: 13px; color: #6b4a00; background: #fff4d6; border-radius: 6px; padding: 6px 10px;">
            <i class="fas fa-exclamation-triangle" style="color: #c98500;"></i>
            {{ $semInicial }} {{ $semInicial == 1 ? 'tanque ainda não tem' : 'tanques ainda não têm' }} saldo inicial no cadastro do Comboio: o saldo deles é só a soma dos lançamentos e pode não bater com a medição.
        </p>
    @endif
    <div class="kim-saldos-grade">
        @forelse ($saldosTanques as $t)
            @php $s = $situacoes[$t->situacao]; @endphp
            <a class="kim-saldo" href="{{ Route::has('painel.saldo_combustivel') ? route('painel.saldo_combustivel', ['filtrar' => 1, 'tanque' => $t->id, 'dtInicial' => date('Y-m-01'), 'dtFinal' => date('Y-m-d')]) : '#' }}"
               title="{{ $t->tanque }}: {{ $s['nome'] }}">
                <div class="kim-saldo-nome">{{ $t->tanque }}</div>
                <div class="kim-saldo-valor" style="{{ $t->saldo < 0 ? 'color: #b3261e;' : '' }}">{{ $nl($t->saldo) }} L</div>
                <div class="kim-saldo-nivel" role="img" aria-label="{{ is_null($t->ocupacao) ? 'Sem capacidade cadastrada' : round(max($t->ocupacao, 0) * 100).'% da capacidade' }}">
                    <div style="width: {{ is_null($t->ocupacao) ? 0 : round(min(max($t->ocupacao, 0), 1) * 100, 1) }}%; background: {{ $s['cor'] }};"></div>
                </div>
                <div class="kim-saldo-rodape">
                    <span><i class="{{ $s['icone'] }}" style="color: {{ $s['cor'] }};"></i> {{ $s['nome'] }}</span>
                    <span>{{ is_null($t->ocupacao) ? 'sem capacidade' : round(max($t->ocupacao, 0) * 100).'% de '.$nl($t->capacidade) }}</span>
                </div>
                    <span class="kim-saldo-aviso">Ajustar o Saldo</span>
            </a>
        @empty
            <span class="text-muted">Nenhum tanque próprio ativo cadastrado.</span>
        @endforelse
    </div>
</div>
