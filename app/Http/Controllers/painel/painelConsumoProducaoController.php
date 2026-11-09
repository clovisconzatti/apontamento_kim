<?php

namespace App\Http\Controllers\painel;

use App\Support\ConsumoAbastecimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Consumo por Produção
 *
 * Litros de diesel por m² faturado (campo "Faturamento (m²)" da Informação Diária), só das máquinas que tiveram
 * faturamento no período: L/m² = diesel abastecido ÷ faturamento.
 * O abastecimento costuma ser lançado numa linha própria (sem fazenda e em outro dia que a produção), por isso a conta
 * é feita por máquina e por período (no mínimo por semana), nunca por fazenda ou por dia.
 * Equipamentos com "Controla Média" = Não ficam de fora, como nas outras médias de consumo.
 */
class painelConsumoProducaoController extends PainelBase
{
    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_producao', $camposFiltro);

        [$fatos, $periodos] = $this->fatos($filtros);
        $totalRegistros = count($fatos);

        return view('painel.consumo_producao', compact('fatos', 'periodos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Planilha por máquina, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_producao', $this->camposFiltro());
        [$fatos] = $this->fatos($filtros);
        $num = fn($v) => $this->numeroPlanilha($v);

        $linhas = collect($fatos)->groupBy('maquina')->map(function ($l, $maquina) use ($num) {
            $producao = $l->sum('fm');
            $diesel = $l->sum('di');
            $horas = $l->sum('hm');
            return [
                'producao' => $producao,
                'linha'    => [$maquina, $l->where('fm', '>', 0)->pluck('d')->unique()->count(), $num($producao), $num($diesel),
                               $producao > 0 ? $num($diesel / $producao) : '', $num($horas), $horas > 0 ? $num($producao / $horas) : '',
                               date('d/m/Y', strtotime($l->where('fm', '>', 0)->max('d')))],
            ];
        })->sortByDesc('producao')->pluck('linha');

        return $this->planilha('consumo_producao',
            ['Máquina', 'Dias com produção', 'Faturamento (m²)', 'Diesel (L)', 'Consumo (L/m²)', 'Horas de máquina produzindo',
             'Produtividade (m²/h)', 'Último dia com produção'], $linhas);
    }

    private function camposFiltro()
    {
        return $this->comPeriodo([
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'equipamento' => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_equipamento' => $this->campoTipoEquipamento(3),
        ]);
    }

    // Um lançamento por linha (das máquinas com faturamento no período): diesel, faturamento e horas de máquina produzindo
    private function fatos(array $filtros)
    {
        // Máquinas que faturaram no período (com os filtros da tela)
        $comProducao = DB::table('informacao')->whereNull('deleted_at')->where('fat_m', '>', 0)->where('equipamento', '>', 0);
        $this->filtrarPeriodo($comProducao, $filtros, 'informacao.data');
        $this->filtrarIgual($comProducao, $filtros, ['equipamento' => 'informacao.equipamento']);
        $this->filtrarTipoEquipamento($comProducao, $filtros, 'informacao.equipamento');
        $maquinas = $comProducao->distinct()->pluck('equipamento')->all();
        if (!$maquinas) {
            return [[], $this->periodosSemanais($filtros, [])];
        }

        $lancamentos = $this->lancamentosInformacao($filtros, ['equipamento' => 'informacao.equipamento'], function ($q) use ($maquinas) {
            $q->whereIn('informacao.equipamento', $maquinas)
              ->where(fn($q) => $q->where('informacao.qnt_diesel', '>', 0)->orWhere('informacao.fat_m', '>', 0));
            ConsumoAbastecimento::filtrarControlaMedia($q);
        });
        $faturamento = DB::table('informacao')->whereIn('id', $lancamentos->pluck('id'))->pluck('fat_m', 'id');
        $periodos = $this->periodosSemanais($filtros, $lancamentos->pluck('data')->all());

        $fatos = $lancamentos->map(function ($l) use ($faturamento, $periodos) {
            $fm = (float) ($faturamento[$l->id] ?? 0);
            return [
                'd' => $l->data, 'p' => $periodos['chaveDe']($l->data), 'maquina' => $l->maquina,
                'di' => round((float) $l->diesel, 2),
                'fm' => round(max($fm, 0), 2),
                'hm' => $fm > 0 ? round((float) $l->horas_maquina, 3) : 0, // horas de máquina só nos dias com produção
            ];
        })->values()->all();

        return [$fatos, $periodos];
    }

    // Igual aos outros painéis, mas nunca por dia: o diesel e a produção raramente caem no mesmo dia
    private function periodosSemanais(array $filtros, array $datas)
    {
        $periodos = $this->periodos($filtros, $datas);
        if ($periodos['escala'] !== 'dia') {
            return $periodos;
        }

        sort($datas);
        $inicio = $filtros['dtInicial'] ?: (reset($datas) ?: date('Y-m-d'));
        $fim = $filtros['dtFinal'] ?: (end($datas) ?: date('Y-m-d'));
        $chaveDe = fn($data) => date('Y-m-d', strtotime('monday this week', strtotime($data)));

        $chaves = [];
        for ($t = strtotime($chaveDe($inicio)); $t <= strtotime($fim); $t = strtotime('+7 days', $t)) {
            $chaves[] = ['chave' => date('Y-m-d', $t), 'rotulo' => date('d/m', $t), 'dica' => 'Semana de '.date('d/m/Y', $t)];
        }
        return ['escala' => 'semana', 'chaves' => $chaves, 'chaveDe' => $chaveDe];
    }
}
