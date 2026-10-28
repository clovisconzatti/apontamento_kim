<?php

namespace App\Http\Controllers\painel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Consumo de Óleo Lubrificante
 *
 * Junta os lubrificantes lançados na Informação Diária, na Manutenção e na Revisão.
 * A revisão não tem fazenda: com o filtro de fazenda ela fica de fora.
 */
class painelLubrificanteController extends PainelBase
{
    const ORIGENS = [
        'informacao' => 'Informação Diária',
        'manutencao' => 'Manutenção',
        'revisao'    => 'Revisão',
    ];

    // Paleta categórica (ordem fixa, validada para daltonismo nas 3 primeiras): cor segue o tipo, nunca a posição
    const CORES = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#6250d6', '#e34948'];

    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_lubrificante', $camposFiltro);

        $tipos = $this->tipos();
        $lancamentos = $this->lancamentos($filtros)->get();
        $periodos = $this->periodos($filtros, $lancamentos->pluck('data')->all());

        // Um lançamento de lubrificante por linha para os gráficos interativos
        $fatos = $lancamentos->map(fn($l) => [
            'd' => $l->data, 'p' => $periodos['chaveDe']($l->data),
            'maquina' => $l->maquina, 'fazenda' => $l->fazenda_nome ?: 'Sem fazenda (revisão)',
            'tipo' => $tipos[$l->lubrificante]['nome'] ?? 'Sem tipo', 'origem' => self::ORIGENS[$l->origem],
            'q' => round((float) $l->quantidade, 2), 'hm' => 0,
        ])->values()->all();

        // Horas de máquina (para a média a cada 100 h) das máquinas que têm lubrificante: valem para qualquer tipo e origem
        $maquinas = $lancamentos->pluck('equipamento')->unique()->filter()->values()->all();
        foreach ($this->horasPorDia($filtros, $maquinas) as $h) {
            $fatos[] = [
                'd' => $h->data, 'p' => $periodos['chaveDe']($h->data), 'maquina' => $h->maquina, 'fazenda' => $h->fazenda,
                'tipo' => '', 'origem' => '', 'q' => 0, 'hm' => round((float) $h->horas, 3), 'livre' => ['tipo' => 1, 'origem' => 1],
            ];
        }

        // Cor fixa de cada tipo (pelo nome, que é o que os gráficos usam)
        $cores = collect($tipos)->mapWithKeys(fn($t) => [$t['nome'] => $t['cor']])->all();
        $totalRegistros = $lancamentos->count();

        return view('painel.lubrificante', compact('fatos', 'periodos', 'cores', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Horas de máquina por dia, máquina e fazenda (só das máquinas informadas)
    private function horasPorDia(array $filtros, array $maquinas)
    {
        if (!$maquinas) {
            return [];
        }
        $m = $this->horasMaquinaSql();
        $query = DB::table('informacao')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->leftJoin('fazenda', 'fazenda.id', 'informacao.fazenda')
                    ->whereNull('informacao.deleted_at')
                    ->whereIn('informacao.equipamento', $maquinas);
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, ['fazenda' => 'informacao.fazenda']);

        return $query->groupBy('informacao.data', 'informacao.equipamento', 'informacao.fazenda', 'equipamento.equipamento', 'equipamento.placa', 'fazenda.fazenda')
                     ->havingRaw("SUM($m) > 0")
                     ->get([
                         'informacao.data',
                         DB::raw("COALESCE(equipamento.equipamento, equipamento.placa, 'Sem máquina') as maquina"),
                         DB::raw("COALESCE(fazenda.fazenda, 'Sem fazenda') as fazenda"),
                         DB::raw("SUM($m) as horas"),
                     ]);
    }

    // Planilha por máquina e tipo de lubrificante, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_lubrificante', $this->camposFiltro());
        $tipos = $this->tipos();
        $lancamentos = $this->lancamentos($filtros)->get();
        $num = fn($v) => $this->numeroPlanilha($v);

        $linhas = [];
        foreach ($this->porMaquina($lancamentos, $filtros) as $m) {
            foreach ($m->tipos as $id => $quantidade) {
                $linhas[] = [$m->maquina, $tipos[$id]['nome'] ?? 'Sem tipo', $num($quantidade), $num($m->horas),
                             $m->horas > 0 ? $num($quantidade / $m->horas * 100) : '', date('d/m/Y', strtotime($m->ultimo_dia))];
            }
        }
        return $this->planilha('consumo_lubrificante',
            ['Máquina', 'Lubrificante', 'Quantidade', 'Horas de máquina', 'Quantidade a cada 100 h', 'Último lançamento'], $linhas);
    }

    private function camposFiltro()
    {
        return [
            'dtInicial'    => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'      => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'fazenda'      => ['label' => 'Fazenda', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('fazenda', 'fazenda')],
            'equipamento'  => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'lubrificante' => ['label' => 'Lubrificante', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('lubrificante', 'lubrificante')],
            'origem'       => ['label' => 'Lançado em', 'tipo' => 'select', 'col' => 2, 'opcoes' => self::ORIGENS],
        ];
    }

    // Tipos de lubrificante com a cor fixa de cada um (pela ordem do cadastro)
    private function tipos()
    {
        $tipos = [];
        foreach (DB::table('lubrificante')->orderBy('id')->get(['id', 'lubrificante']) as $i => $t) {
            $tipos[$t->id] = ['nome' => $t->lubrificante, 'cor' => self::CORES[$i % count(self::CORES)]];
        }
        return $tipos;
    }

    // Todos os lançamentos de lubrificante das três origens, já filtrados
    private function lancamentos(array $filtros)
    {
        $informacao = DB::table('informacao')
                        ->whereNull('informacao.deleted_at')
                        ->where('informacao.qnt_lubrificante', '>', 0)
                        ->select(DB::raw("'informacao' as origem"), 'informacao.data', 'informacao.equipamento', 'informacao.fazenda',
                                 'informacao.tipo_lubrificante as lubrificante', 'informacao.qnt_lubrificante as quantidade');

        $manutencao = DB::table('manutencao_lubrificante')
                        ->join('manutencao', 'manutencao.id', 'manutencao_lubrificante.manutencao_id')
                        ->whereNull('manutencao.deleted_at')
                        ->select(DB::raw("'manutencao' as origem"), 'manutencao.data', 'manutencao.equipamento', 'manutencao.fazenda',
                                 'manutencao_lubrificante.lubrificante', 'manutencao_lubrificante.quantidade');

        $revisao = DB::table('revisao_lubrificante')
                        ->join('revisao', 'revisao.id', 'revisao_lubrificante.revisao_id')
                        ->whereNull('revisao.deleted_at')
                        ->select(DB::raw("'revisao' as origem"), 'revisao.data', 'revisao.equipamento', DB::raw('NULL as fazenda'),
                                 'revisao_lubrificante.lubrificante', 'revisao_lubrificante.quantidade');

        $query = DB::query()->fromSub($informacao->unionAll($manutencao)->unionAll($revisao), 'l')
                    ->leftJoin('equipamento', 'equipamento.id', 'l.equipamento')
                    ->leftJoin('fazenda', 'fazenda.id', 'l.fazenda')
                    ->select('l.origem', 'l.data', 'l.equipamento', 'l.lubrificante', 'l.quantidade',
                             DB::raw("COALESCE(equipamento.equipamento, equipamento.placa, 'Sem máquina') as maquina"),
                             'fazenda.fazenda as fazenda_nome');

        $this->filtrarPeriodo($query, $filtros, 'l.data');
        $this->filtrarIgual($query, $filtros, [
            'fazenda'      => 'l.fazenda',
            'equipamento'  => 'l.equipamento',
            'lubrificante' => 'l.lubrificante',
            'origem'       => 'l.origem',
        ]);
        return $query;
    }

    // Quantidade por máquina (com a divisão por tipo) e as horas de máquina do período para a média a cada 100 h
    private function porMaquina($lancamentos, array $filtros)
    {
        $m = $this->horasMaquinaSql();
        $horas = DB::table('informacao')->whereNull('deleted_at')->where('equipamento', '>', 0);
        $this->filtrarPeriodo($horas, $filtros, 'informacao.data');
        $this->filtrarIgual($horas, $filtros, ['fazenda' => 'informacao.fazenda', 'equipamento' => 'informacao.equipamento']);
        $horas = $horas->groupBy('equipamento')->selectRaw("SUM($m) as total, equipamento")->pluck('total', 'equipamento');

        return $lancamentos->groupBy('equipamento')->map(function ($g, $id) use ($horas) {
            return (object) [
                'equipamento_id' => $id,
                'maquina'        => $g->first()->maquina,
                'quantidade'     => $g->sum('quantidade'),
                'lancamentos'    => $g->count(),
                'tipos'          => $g->groupBy('lubrificante')->map(fn($t) => $t->sum('quantidade'))->sortKeys()->all(),
                'horas'          => (float) ($horas[$id] ?? 0),
                'ultimo_dia'     => $g->max('data'),
            ];
        })->map(function ($maq) {
            $maq->por_100h = $maq->horas > 0 ? $maq->quantidade / $maq->horas * 100 : null;
            return $maq;
        })->sortByDesc('quantidade')->values();
    }
}
