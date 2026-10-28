<?php

namespace App\Http\Controllers\painel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Consumo de Combustível
 *
 * Diesel lançado na Informação Diária (Total Diesel). O consumo em L/h é calculado pelos abastecimentos
 * (ver App\Support\ConsumoAbastecimento) e comparado com a faixa consumo mínimo / máximo do cadastro do equipamento.
 */
class painelCombustivelController extends PainelBase
{
    // Um abastecimento acima disso merece conferência (não sai dos totais)
    const ABASTECIMENTO_GRANDE = 1000;

    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_combustivel', $camposFiltro);

        [$fatos, $periodos] = $this->fatos($filtros);
        $faixas = $this->faixasConsumo();
        $conferir = $this->aConferir($filtros);
        $totalRegistros = count($fatos);

        return view('painel.combustivel', compact('fatos', 'periodos', 'faixas', 'conferir', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Planilha por máquina, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_combustivel', $this->camposFiltro());
        [$fatos] = $this->fatos($filtros);
        $faixas = $this->faixasConsumo();
        $num = fn($v) => $this->numeroPlanilha($v);

        $linhas = collect($fatos)->groupBy('maquina')->map(function ($l, $maquina) use ($faixas, $num) {
            $litros = $l->sum('di');
            $consumo = $l->sum('ih') > 0 ? $l->sum('il') / $l->sum('ih') : null;
            $faixa = $faixas[$maquina] ?? null;
            return [
                'litros' => $litros,
                'linha'  => [$maquina, $l->count(), $num($litros), $num($litros / $l->count()), $num($l->sum('ih')), $num($consumo),
                             $faixa ? $num($faixa[0]) : '', $faixa ? $num($faixa[1]) : '', $this->situacaoFaixa($consumo, $faixa),
                             date('d/m/Y', strtotime($l->max('d')))],
            ];
        })->sortByDesc('litros')->pluck('linha');

        return $this->planilha('consumo_combustivel',
            ['Máquina', 'Abastecimentos', 'Litros', 'Média por abastecimento', 'Horas entre abastecimentos', 'Consumo (L/h)',
             'Consumo mínimo (L/h)', 'Consumo máximo (L/h)', 'Situação', 'Último abastecimento'], $linhas);
    }

    private function situacaoFaixa($consumo, $faixa)
    {
        if (is_null($consumo) || !$faixa) {
            return 'Sem faixa no cadastro';
        }
        return $consumo > $faixa[1] ? 'Acima da faixa' : ($consumo < $faixa[0] ? 'Abaixo da faixa' : 'Dentro da faixa');
    }

    private function camposFiltro()
    {
        return [
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'fazenda'     => ['label' => 'Fazenda', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('fazenda', 'fazenda')],
            'equipamento' => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tanque'      => ['label' => 'Tanque de origem', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('comboio', 'tanque')],
        ];
    }

    private function filtrosIgual()
    {
        return [
            'fazenda'     => 'informacao.fazenda',
            'equipamento' => 'informacao.equipamento',
            'tanque'      => 'informacao.origem_abastecimento',
        ];
    }

    // Um abastecimento por linha para os gráficos interativos, com o intervalo de consumo que ele fecha
    private function fatos(array $filtros)
    {
        $lancamentos = $this->lancamentosInformacao($filtros, $this->filtrosIgual(), function ($q) {
            $q->where('informacao.qnt_diesel', '>', 0)->where('informacao.equipamento', '>', 0);
        });
        $intervalos = $this->intervalosConsumo($filtros['dtFinal'])['intervalos'];
        $periodos = $this->periodos($filtros, $lancamentos->pluck('data')->all());

        $fatos = $lancamentos->map(function ($l) use ($intervalos, $periodos) {
            $consumo = $intervalos[$l->id] ?? null;
            return [
                'd' => $l->data, 'p' => $periodos['chaveDe']($l->data),
                'maquina' => $l->maquina, 'fazenda' => $l->fazenda, 'tanque' => $l->tanque,
                'di' => round((float) $l->diesel, 2),
                'il' => $consumo ? round($consumo['litros'], 2) : 0, 'ih' => $consumo ? round($consumo['horas'], 3) : 0,
            ];
        })->values()->all();

        return [$fatos, $periodos];
    }

    // Abastecimentos muito grandes para uma máquina: conferir se não foi erro de digitação
    private function aConferir(array $filtros)
    {
        $query = DB::table('informacao')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->leftJoin('comboio', 'comboio.id', 'informacao.origem_abastecimento')
                    ->whereNull('informacao.deleted_at')
                    ->where('informacao.qnt_diesel', '>', self::ABASTECIMENTO_GRANDE);
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, $this->filtrosIgual());

        return $query->orderByDesc('informacao.qnt_diesel')->get([
            'informacao.id', 'informacao.data', 'informacao.qnt_diesel', 'informacao.nr_nf', 'comboio.tanque',
            DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
        ]);
    }
}
