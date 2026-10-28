<?php

namespace App\Http\Controllers\painel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Horas Máquina
 *
 * Horas trabalhadas por equipamento pelo horímetro da Informação Diária (final - inicial).
 * Diferença negativa ou acima de 24h num lançamento é tratada como erro de digitação:
 * fica fora dos totais e vai para a lista "a conferir".
 * Consumo (L/h) pelos abastecimentos, igual ao painel Consumo Médio (ver App\Support\ConsumoAbastecimento).
 */
class painelHorasMaquinaController extends PainelBase
{
    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_horas_maquina', $camposFiltro);

        [$fatos, $periodos] = $this->fatos($filtros);
        $conferir = $this->aConferir($filtros);
        $totalRegistros = count($fatos);

        return view('painel.horas_maquina', compact('fatos', 'periodos', 'conferir', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Planilha por máquina, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_horas_maquina', $this->camposFiltro());
        [$fatos] = $this->fatos($filtros);
        $num = fn($v) => $this->numeroPlanilha($v);
        $soma = fn($l, $c) => array_sum(array_column($l, $c));

        $linhas = [];
        foreach (collect($fatos)->groupBy('maquina') as $maquina => $l) {
            $l = $l->all();
            $horas = $soma($l, 'hm');
            $dias = count(array_unique(array_column(array_filter($l, fn($f) => $f['hm'] > 0), 'd')));
            $linhas[] = [$maquina, $dias, $num($horas), $num($dias ? $horas / $dias : 0), $num($soma($l, 'hh')),
                         $soma($l, 'bh') > 0 ? $num($soma($l, 'bm') / $soma($l, 'bh') * 100) : '',
                         $num($soma($l, 'di')), $soma($l, 'ih') > 0 ? $num($soma($l, 'il') / $soma($l, 'ih')) : '',
                         count(array_unique(array_column($l, 'colaborador'))), date('d/m/Y', strtotime(max(array_column($l, 'd'))))];
        }
        usort($linhas, fn($a, $b) => (float) str_replace(',', '.', $b[2]) <=> (float) str_replace(',', '.', $a[2]));

        return $this->planilha('horas_maquina',
            ['Máquina', 'Dias trabalhados', 'Horas de máquina', 'Média por dia', 'Horas homem', 'Aproveitamento (%)',
             'Diesel (L)', 'Consumo (L/h)', 'Colaboradores', 'Último dia'], $linhas);
    }

    private function camposFiltro()
    {
        return [
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'fazenda'     => ['label' => 'Fazenda', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('fazenda', 'fazenda')],
            'equipamento' => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_equipamento' => $this->campoTipoEquipamento(),
            'atividade'   => ['label' => 'Atividade', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('atividade', 'atividade')],
        ];
    }

    private function filtrosIgual()
    {
        return [
            'fazenda'     => 'informacao.fazenda',
            'equipamento' => 'informacao.equipamento',
            'atividade'   => 'informacao.atividade',
        ];
    }

    // Um lançamento por linha para os gráficos interativos (com horas de máquina ou com intervalo de consumo)
    private function fatos(array $filtros)
    {
        $lancamentos = $this->lancamentosInformacao($filtros, $this->filtrosIgual(), fn($q) => $q->where('informacao.equipamento', '>', 0));
        $intervalos = $this->intervalosConsumo($filtros['dtFinal'])['intervalos'];
        $periodos = $this->periodos($filtros, $lancamentos->pluck('data')->all());

        $fatos = [];
        foreach ($lancamentos as $l) {
            $consumo = $intervalos[$l->id] ?? null;
            if ($l->horas_maquina <= 0 && !$consumo) {
                continue;
            }
            $ambos = $l->horas_homem > 0 && $l->horas_maquina > 0;
            $fatos[] = [
                'd' => $l->data, 'p' => $periodos['chaveDe']($l->data),
                'maquina' => $l->maquina, 'fazenda' => $l->fazenda, 'atividade' => $l->atividade, 'colaborador' => $l->colaborador,
                'hm' => round((float) $l->horas_maquina, 3), 'hh' => round((float) $l->horas_homem, 3),
                'bh' => $ambos ? round((float) $l->horas_homem, 3) : 0, 'bm' => $ambos ? round((float) $l->horas_maquina, 3) : 0,
                'di' => round((float) $l->diesel, 2),
                'il' => $consumo ? round($consumo['litros'], 2) : 0, 'ih' => $consumo ? round($consumo['horas'], 3) : 0,
                'hf' => $l->horimetro_final ? (float) $l->horimetro_final : null,
            ];
        }
        return [$fatos, $periodos];
    }

    // Horímetro com diferença negativa ou acima de 24h: provável erro de digitação
    private function aConferir(array $filtros)
    {
        $d = 'informacao.horimetro_final - informacao.horimetro_inicial';
        $query = DB::table('informacao')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->whereNull('informacao.deleted_at')
                    ->whereNotNull('informacao.horimetro_inicial')
                    ->whereNotNull('informacao.horimetro_final')
                    ->whereRaw("NOT ($d >= 0 AND $d <= ".self::HORIMETRO_MAXIMO.')');
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, $this->filtrosIgual());
        $this->filtrarTipoEquipamento($query, $filtros, 'informacao.equipamento');

        return $query->orderByDesc('informacao.data')->get([
            'informacao.id', 'informacao.data', 'informacao.horimetro_inicial', 'informacao.horimetro_final',
            DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"), DB::raw("$d as diferenca"),
        ]);
    }
}
