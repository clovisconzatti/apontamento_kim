<?php

namespace App\Http\Controllers\painel;

use App\Support\SaldoCombustivel;
use Illuminate\Http\Request;

/**
 * Painel de Operações · Saldo de Combustível dos tanques próprios (cálculo em App\Support\SaldoCombustivel).
 *
 * Os gráficos recebem os movimentos do período e, por tanque, uma linha de "abertura" (saldo inicial do cadastro
 * + movimentos anteriores ao período): assim a evolução do saldo é a soma acumulada.
 */
class painelSaldoCombustivelController extends PainelBase
{
    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_saldo_combustivel', $camposFiltro);

        [$fatos, $periodos, $tanques, $dias] = $this->fatos($filtros);
        $situacoes = SaldoCombustivel::SITUACOES;
        $totalRegistros = count(array_filter($fatos, fn($f) => $f['k'] === 'mov'));

        return view('painel.saldo_combustivel', compact('fatos', 'periodos', 'tanques', 'dias', 'situacoes', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Extrato (CSV para Excel) com os movimentos do período e o saldo do tanque depois de cada um
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_saldo_combustivel', $this->camposFiltro());
        [$fatos] = $this->fatos($filtros);
        $num = fn($v) => $this->numeroPlanilha($v);

        $linhas = collect($fatos)->filter(fn($f) => $f['k'] !== 'abertura')
                    ->sortBy([['tanque', 'asc'], ['d', 'asc'], ['o', 'asc']])
                    ->map(fn($f) => [$f['tanque'], date('d/m/Y', strtotime($f['d'])), $f['tipo'], $f['desc'],
                                     $f['l'] > 0 ? $num($f['l']) : '', $f['l'] < 0 ? $num(-$f['l']) : '', $num($f['s'])]);

        return $this->planilha('saldo_combustivel', ['Tanque', 'Data', 'Movimento', 'Descrição', 'Entrada (L)', 'Saída (L)', 'Saldo do tanque (L)'], $linhas);
    }

    private function camposFiltro()
    {
        $tanques = SaldoCombustivel::tanques()->pluck('tanque', 'id')->all();
        return [
            'dtInicial' => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'   => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'tanque'    => ['label' => 'Tanque', 'tipo' => 'select', 'col' => 4, 'opcoes' => $tanques],
        ];
    }

    private function fatos(array $filtros)
    {
        $inicio = $filtros['dtInicial'] ?: '1900-01-01';
        $fim = $filtros['dtFinal'] ?: date('Y-m-d');
        $todos = SaldoCombustivel::movimentos($fim);
        $tanques = SaldoCombustivel::saldos($fim, $todos)
                        ->filter(fn($t) => !$filtros['tanque'] || $t->id == $filtros['tanque'])
                        ->values();
        $ids = $tanques->pluck('id')->all();
        $periodos = $this->periodos($filtros, $todos->where('data', '>=', $inicio)->pluck('data')->all());
        $primeiro = $periodos['chaves'][0]['chave'] ?? $periodos['chaveDe']($inicio);
        $tipos = SaldoCombustivel::TIPOS;

        $fatos = [];
        foreach ($tanques as $t) {
            // Saldo no início do período: saldo inicial do cadastro + movimentos anteriores ao período
            $antes = $todos->where('tanque_id', $t->id)->where('data', '<', $inicio)->sum('litros');
            $fatos[] = ['k' => 'abertura', 'd' => $inicio, 'p' => $primeiro, 'tanque' => $t->tanque, 'tipo' => '', 'dir' => '',
                        'l' => round((float) $t->saldo_inicial + $antes, 2), 'livre' => ['tipo' => 1, 'dir' => 1]];
        }

        $ordem = 0;
        foreach ($todos as $m) {
            if ($m['data'] < $inicio || !in_array($m['tanque_id'], $ids)) {
                continue;
            }
            $fatos[] = [
                'k' => 'mov', 'd' => $m['data'], 'p' => $periodos['chaveDe']($m['data']), 'tanque' => $m['tanque'],
                'tipo' => $tipos[$m['tipo']]['nome'], 'dir' => $m['litros'] >= 0 ? 'Entrada' : 'Saída',
                'l' => round($m['litros'], 2), 's' => round($m['saldo'], 2), 'desc' => $m['descricao'],
                'id' => $m['id'], 'org' => $m['origem'], 'o' => $ordem++,
            ];
        }

        $dias = (int) ((strtotime($fim) - strtotime($filtros['dtInicial'] ?: ($todos->min('data') ?: $fim))) / 86400) + 1;

        return [$fatos, $periodos, $tanques, max($dias, 1)];
    }
}
