<?php

namespace App\Http\Controllers\painel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Consumo Médio por máquina
 *
 * Pelos abastecimentos: litros abastecidos ÷ (horímetro deste abastecimento − horímetro do abastecimento anterior).
 * Sem horímetro no abastecimento anterior, procura na data anterior (ver App\Support\ConsumoAbastecimento).
 */
class painelConsumoMedioController extends PainelBase
{
    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_medio', $camposFiltro);

        [$fatos, $periodos, $conferir] = $this->fatos($filtros);
        $faixas = $this->faixasConsumo();
        $semHorimetro = $this->semHorimetroQuery($filtros)->count();
        $totalRegistros = count($fatos);

        return view('painel.consumo_medio', compact('fatos', 'periodos', 'conferir', 'faixas', 'semHorimetro', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Tela de correção: abastecimentos do período filtrado sem horímetro de abastecimento
    public function semHorimetro(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_medio', $camposFiltro);

        $lancamentos = $this->semHorimetroQuery($filtros)
                            ->leftJoin('fazenda', 'fazenda.id', 'informacao.fazenda')
                            ->leftJoin('colaborador', 'colaborador.id', 'informacao.colaborador')
                            ->leftJoin('comboio', 'comboio.id', 'informacao.origem_abastecimento')
                            ->orderByDesc('informacao.data')
                            ->orderBy('maquina')
                            ->get([
                                'informacao.id', 'informacao.data', 'informacao.qnt_diesel', 'informacao.nr_nf',
                                'informacao.horimetro_inicial', 'informacao.horimetro_final',
                                DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
                                'fazenda.fazenda', 'colaborador.colaborador', 'comboio.tanque',
                            ]);
        $totalRegistros = $lancamentos->count();

        return view('painel.consumo_medio_sem_horimetro', compact('lancamentos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Grava só o horímetro de abastecimento de um lançamento (correção rápida, linha a linha)
    public function salvarHorimetro($id, Request $request)
    {
        $request->validate(
            ['horimetro_abastecimento' => 'required|numeric|gt:0'],
            ['horimetro_abastecimento.required' => 'Informe o horímetro do abastecimento.',
             'horimetro_abastecimento.numeric'  => 'O horímetro deve ser um número.',
             'horimetro_abastecimento.gt'       => 'O horímetro deve ser maior que zero.']
        );
        $atualizado = DB::table('informacao')
                        ->where('id', $id)
                        ->whereNull('deleted_at')
                        ->where('qnt_diesel', '>', 0)
                        ->update(['horimetro_abastecimento' => (float) $request->horimetro_abastecimento, 'updated_at' => now()]);
        if (!$atualizado) {
            return response()->json(['message' => 'Lançamento não encontrado.'], 404);
        }
        return response()->json('success');
    }

    // Abastecimentos (com diesel) sem horímetro de abastecimento, dentro dos filtros da tela
    private function semHorimetroQuery(array $filtros)
    {
        $query = DB::table('informacao')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->whereNull('informacao.deleted_at')
                    ->where('informacao.qnt_diesel', '>', 0)
                    ->where('informacao.equipamento', '>', 0)
                    ->where(fn($q) => $q->whereNull('informacao.horimetro_abastecimento')->orWhere('informacao.horimetro_abastecimento', '<=', 0));
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, [
            'fazenda'     => 'informacao.fazenda',
            'equipamento' => 'informacao.equipamento',
            'tanque'      => 'informacao.origem_abastecimento',
        ]);
        return $query;
    }

    // Planilha com cada intervalo entre abastecimentos (a conta completa), com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_medio', $this->camposFiltro());
        [$fatos] = $this->fatos($filtros);
        $num = fn($v) => $this->numeroPlanilha($v);

        return $this->planilha('consumo_medio',
            ['Máquina', 'Fazenda', 'Data do abastecimento', 'Horímetro', 'Abastecimento anterior', 'Horímetro anterior',
             'Horas', 'Litros', 'Consumo (L/h)'],
            collect($fatos)->sortBy([['maquina', 'asc'], ['d', 'asc']])->map(fn($f) => [
                $f['maquina'], $f['fazenda'], date('d/m/Y', strtotime($f['d'])), $num($f['h']),
                date('d/m/Y', strtotime($f['dr'])), $num($f['hr']), $num($f['ih']), $num($f['il']), $num($f['il'] / $f['ih']),
            ]));
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

    // Um intervalo de consumo por linha (o abastecimento que fecha o intervalo), dentro dos filtros
    private function fatos(array $filtros)
    {
        $lancamentos = $this->lancamentosInformacao($filtros, [
            'fazenda'     => 'informacao.fazenda',
            'equipamento' => 'informacao.equipamento',
            'tanque'      => 'informacao.origem_abastecimento',
        ], fn($q) => $q->where('informacao.qnt_diesel', '>', 0)->where('informacao.equipamento', '>', 0));
        $calculo = $this->intervalosConsumo($filtros['dtFinal']);
        $periodos = $this->periodos($filtros, $lancamentos->pluck('data')->all());

        $fatos = [];
        $ids = [];
        foreach ($lancamentos as $l) {
            $ids[$l->id] = true;
            $i = $calculo['intervalos'][$l->id] ?? null;
            if (!$i) {
                continue;
            }
            $fatos[] = [
                'id' => $l->id, 'd' => $l->data, 'p' => $periodos['chaveDe']($l->data),
                'maquina' => $l->maquina, 'fazenda' => $l->fazenda, 'tanque' => $l->tanque,
                'il' => round($i['litros'], 2), 'ih' => round($i['horas'], 3),
                'h' => $i['horimetro'], 'hr' => $i['horimetro_ref'], 'dr' => $i['data_ref'],
            ];
        }

        // Conferência: só os abastecimentos que entraram nos filtros da tela
        $conferir = array_values(array_filter($calculo['conferir'], fn($c) => isset($ids[$c['id']])));

        return [$fatos, $periodos, $conferir];
    }
}
