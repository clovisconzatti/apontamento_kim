<?php

namespace App\Http\Controllers\painel;

use App\Support\CustoHoras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Painel de Operações · Manutenções
 *
 * Junta a Manutenção e a Revisão num só painel de custos.
 * - Revisão: sempre preventiva.
 * - Manutenção: pelo "Tipo da Manutenção" lançado (PREVENTIVA ou CORRETIVA); sem tipo, conta como corretiva.
 * Só os custos informados no lançamento (as peças e os óleos lubrificantes ainda não entram):
 *   terceiros + mão de obra (horas trabalhadas × custo hora) + deslocamento + "manutenção diária" (campo antigo da Manutenção).
 */
class painelManutencaoController extends PainelBase
{
    const TIPOS = ['Preventiva' => 'Preventiva', 'Corretiva' => 'Corretiva'];
    const ORIGENS = ['Manutenção' => 'Manutenção', 'Revisão' => 'Revisão'];

    // Partes do custo (nome => campo do fato)
    const COMPONENTES = [
        'Terceiros'         => 'ct',
        'Mão de obra'       => 'ch',
        'Deslocamento'      => 'cd',
        'Manutenção diária' => 'cm',
    ];

    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_manutencao', $camposFiltro);

        $lancamentos = $this->lancamentos($filtros);
        $periodos = $this->periodos($filtros, array_column($lancamentos, 'd'));
        $fatos = array_map(function ($l) use ($periodos) {
            return $l + ['p' => $periodos['chaveDe']($l['d'])];
        }, $lancamentos);
        $totalRegistros = count($fatos);

        return view('painel.manutencao', compact('fatos', 'periodos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Planilha com um lançamento por linha, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_manutencao', $this->camposFiltro());
        $num = fn($v) => $this->numeroPlanilha($v);

        return $this->planilha('manutencoes',
            ['Data', 'Origem', 'Tipo', 'Máquina', 'Tipo de manutenção / revisão', 'OS', 'Hora inicial', 'Hora final', 'Horas trabalhadas',
             'Custo hora (R$/h)', 'Terceiros (R$)', 'Mão de obra (R$)', 'Deslocamento (R$)', 'Manutenção diária (R$)', 'Total (R$)'],
            collect($this->lancamentos($filtros))->map(fn($l) => [
                date('d/m/Y', strtotime($l['d'])), $l['origem'], $l['tipo'], $l['maquina'], $l['motivo'], $l['os'],
                $l['hi'], $l['hf'], $num($l['h']), $num($l['vh']),
                $num($l['ct']), $num($l['ch']), $num($l['cd']), $num($l['cm']), $num($l['total']),
            ]));
    }

    private function camposFiltro()
    {
        return $this->comPeriodo([
            'dtInicial'        => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'          => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'equipamento'      => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_equipamento' => $this->campoTipoEquipamento(3),
            'tipo'             => ['label' => 'Preventiva / Corretiva', 'tipo' => 'select', 'col' => 2, 'opcoes' => self::TIPOS],
            'origem'           => ['label' => 'Origem', 'tipo' => 'select', 'col' => 2, 'opcoes' => self::ORIGENS],
        ]);
    }

    /**
     * Um lançamento (manutenção ou revisão) por linha, do mais antigo para o mais novo:
     * d (data), origem, tipo (Preventiva/Corretiva), maquina, motivo (tipo de manutenção / revisão), os, hi/hf (horas),
     * h (horas trabalhadas), vh (custo hora) e os custos ct (terceiros), ch (mão de obra), cd (deslocamento),
     * cm (manutenção diária antiga) e total.
     */
    private function lancamentos(array $filtros)
    {
        $linhas = [];
        if (($filtros['origem'] ?? null) !== 'Revisão') {
            foreach ($this->manutencoes($filtros) as $m) {
                $tipo = mb_strtoupper((string) $m->tipo_nome) === 'PREVENTIVA' ? 'Preventiva' : 'Corretiva';
                $linhas[] = $this->linha($m, 'Manutenção', $tipo, $m->tipo_nome ?: 'Sem tipo informado', $m->custo, $m->manutencao_diaria);
            }
        }
        if (($filtros['origem'] ?? null) !== 'Manutenção') {
            foreach ($this->revisoes($filtros) as $r) {
                $linhas[] = $this->linha($r, 'Revisão', 'Preventiva', $r->tipo_nome ?: 'Revisão', $r->custo_terceiro, null);
            }
        }
        if (!empty($filtros['tipo'])) {
            $linhas = array_values(array_filter($linhas, fn($l) => $l['tipo'] === $filtros['tipo']));
        }
        usort($linhas, fn($a, $b) => [$a['d'], $a['origem']] <=> [$b['d'], $b['origem']]);
        return $linhas;
    }

    private function linha($l, string $origem, string $tipo, string $motivo, $terceiros, $diaria)
    {
        $horas = CustoHoras::horas($l->hora_inicial, $l->hora_final);
        $valores = [
            'ct' => round((float) $terceiros, 2),
            'ch' => (float) CustoHoras::custo($l->hora_inicial, $l->hora_final, $l->custo_hora),
            'cd' => round((float) $l->custo_deslocamento, 2),
            'cm' => round((float) $diaria, 2),
        ];
        return [
            'k'       => ($origem === 'Revisão' ? 'r' : 'm').$l->id, // identifica o lançamento
            'd'       => $l->data,
            'origem'  => $origem,
            'tipo'    => $tipo,
            'maquina' => $l->maquina ?: 'Sem máquina',
            'motivo'  => $motivo,
            'os'      => (string) $l->os,
            'hi'      => $l->hora_inicial ? substr($l->hora_inicial, 0, 5) : '',
            'hf'      => $l->hora_final ? substr($l->hora_final, 0, 5) : '',
            'h'       => $horas === null ? 0 : round($horas, 3),
            'vh'      => $l->custo_hora === null ? null : (float) $l->custo_hora,
        ] + $valores + ['total' => round(array_sum($valores), 2)];
    }

    private function manutencoes(array $filtros)
    {
        $q = DB::table('manutencao')
                ->leftJoin('equipamento', 'equipamento.id', 'manutencao.equipamento')
                ->leftJoin('tipo_manutencao', 'tipo_manutencao.id', 'manutencao.tipo_manutencao')
                ->whereNull('manutencao.deleted_at');
        $this->filtrarPeriodo($q, $filtros, 'manutencao.data');
        $this->filtrarIgual($q, $filtros, ['equipamento' => 'manutencao.equipamento']);
        $this->filtrarTipoEquipamento($q, $filtros, 'manutencao.equipamento');

        return $q->get([
            'manutencao.id', 'manutencao.data', 'manutencao.hora_inicial', 'manutencao.hora_final', 'manutencao.custo', 'manutencao.manutencao_diaria',
            'manutencao.ord_servico as os', 'tipo_manutencao.tipo as tipo_nome',
            $this->colunaOpcional('manutencao', 'custo_hora'), $this->colunaOpcional('manutencao', 'custo_deslocamento'),
            DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
        ]);
    }

    private function revisoes(array $filtros)
    {
        $q = DB::table('revisao')
                ->leftJoin('equipamento', 'equipamento.id', 'revisao.equipamento')
                ->leftJoin('tipo_revisao', 'tipo_revisao.id', 'revisao.tipo_revisao')
                ->whereNull('revisao.deleted_at');
        $this->filtrarPeriodo($q, $filtros, 'revisao.data');
        $this->filtrarIgual($q, $filtros, ['equipamento' => 'revisao.equipamento']);
        $this->filtrarTipoEquipamento($q, $filtros, 'revisao.equipamento');

        return $q->get([
            'revisao.id', 'revisao.data', 'revisao.nr_os as os', 'tipo_revisao.descricao as tipo_nome',
            $this->colunaOpcional('revisao', 'hora_inicial'), $this->colunaOpcional('revisao', 'hora_final'),
            $this->colunaOpcional('revisao', 'custo_terceiro'), $this->colunaOpcional('revisao', 'custo_hora'),
            $this->colunaOpcional('revisao', 'custo_deslocamento'),
            DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
        ]);
    }

    // Coluna que só existe depois da migration de custos: antes disso, vem vazia (o painel continua abrindo)
    private function colunaOpcional(string $tabela, string $coluna)
    {
        return Schema::hasColumn($tabela, $coluna) ? $tabela.'.'.$coluna : DB::raw("NULL as $coluna");
    }
}
