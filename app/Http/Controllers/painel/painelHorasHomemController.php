<?php

namespace App\Http\Controllers\painel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Horas Homem
 *
 * Horas trabalhadas por colaborador a partir da hora inicial e final da Informação Diária.
 * Hora final menor que a inicial = turno que vira o dia (+24h). Jornadas acima de 16h
 * num lançamento são tratadas como erro de digitação: ficam fora dos totais e vão para a lista "a conferir".
 */
class painelHorasHomemController extends PainelBase
{
    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_horas_homem', $camposFiltro);

        $lancamentos = $this->lancamentosInformacao($filtros, $this->filtrosIgual(), function ($q) {
            $q->whereNotNull('informacao.hora_inicial')->whereNotNull('informacao.hora_final')->where('informacao.colaborador', '>', 0);
        });
        $periodos = $this->periodos($filtros, $lancamentos->pluck('data')->all());

        // Um lançamento por linha para os gráficos interativos; bh/bm = horas homem/máquina dos lançamentos que têm as duas
        $fatos = $lancamentos->filter(fn($l) => $l->horas_homem > 0)->map(function ($l) use ($periodos) {
            $ambos = $l->horas_homem > 0 && $l->horas_maquina > 0;
            return [
                'd' => $l->data, 'p' => $periodos['chaveDe']($l->data),
                'colaborador' => $l->colaborador, 'fazenda' => $l->fazenda, 'maquina' => $l->maquina,
                'hh' => round((float) $l->horas_homem, 3), 'hm' => round((float) $l->horas_maquina, 3),
                'bh' => $ambos ? round((float) $l->horas_homem, 3) : 0, 'bm' => $ambos ? round((float) $l->horas_maquina, 3) : 0,
            ];
        })->values();

        $conferir = $this->aConferir($filtros);
        $totalRegistros = $fatos->count();

        return view('painel.horas_homem', compact('fatos', 'periodos', 'conferir', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    private function filtrosIgual()
    {
        return [
            'fazenda'     => 'informacao.fazenda',
            'equipamento' => 'informacao.equipamento',
            'colaborador' => 'informacao.colaborador',
        ];
    }

    // Planilha por colaborador, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_horas_homem', $this->camposFiltro());
        $num = fn($v) => $this->numeroPlanilha($v);

        return $this->planilha('horas_homem',
            ['Colaborador', 'Dias trabalhados', 'Horas homem', 'Média por dia', 'Horas de máquina',
             'Aproveitamento (%)', 'Máquinas', 'Fazendas', 'Primeiro dia', 'Último dia'],
            $this->porColaborador($filtros)->map(fn($c) => [
                $c->colaborador, $c->dias, $num($c->horas), $num($c->media_dia), $num($c->horas_maquina),
                is_null($c->aproveitamento) ? '' : $num($c->aproveitamento * 100), $c->maquinas, $c->fazendas,
                date('d/m/Y', strtotime($c->primeiro_dia)), date('d/m/Y', strtotime($c->ultimo_dia)),
            ]));
    }

    private function camposFiltro()
    {
        return [
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'fazenda'     => ['label' => 'Fazenda', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('fazenda', 'fazenda')],
            'equipamento' => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'colaborador' => ['label' => 'Colaborador', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('colaborador', 'colaborador')],
        ];
    }

    // Lançamentos com colaborador, hora inicial e final, dentro dos filtros
    private function base(array $filtros)
    {
        $query = DB::table('informacao')
                    ->whereNull('informacao.deleted_at')
                    ->whereNotNull('informacao.hora_inicial')
                    ->whereNotNull('informacao.hora_final')
                    ->where('informacao.colaborador', '>', 0);
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, $this->filtrosIgual());
        return $query;
    }

    private function porColaborador(array $filtros)
    {
        $h = $this->horasHomemSql();
        $m = $this->horasMaquinaSql();
        // Aproveitamento só sobre lançamentos com as duas horas (para comparar igual com igual)
        $ambos = "($h > 0 AND $m > 0)";

        return $this->base($filtros)
                    ->leftJoin('colaborador', 'colaborador.id', 'informacao.colaborador')
                    ->groupBy('informacao.colaborador', 'colaborador.colaborador')
                    ->havingRaw("SUM($h) > 0")
                    ->orderByDesc('horas')
                    ->get([
                        'informacao.colaborador as colaborador_id',
                        DB::raw("COALESCE(colaborador.colaborador, 'Sem colaborador') as colaborador"),
                        DB::raw("SUM($h) as horas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN $h > 0 THEN informacao.data END) as dias"),
                        DB::raw("SUM($m) as horas_maquina"),
                        DB::raw("SUM(CASE WHEN $ambos THEN $h ELSE 0 END) as base_homem"),
                        DB::raw("SUM(CASE WHEN $ambos THEN $m ELSE 0 END) as base_maquina"),
                        DB::raw('COUNT(DISTINCT informacao.equipamento) as maquinas'),
                        DB::raw('COUNT(DISTINCT informacao.fazenda) as fazendas'),
                        DB::raw('MIN(informacao.data) as primeiro_dia'),
                        DB::raw('MAX(informacao.data) as ultimo_dia'),
                    ])
                    ->map(function ($c) {
                        $c->horas = (float) $c->horas;
                        $c->horas_maquina = (float) $c->horas_maquina;
                        $c->media_dia = $c->dias ? $c->horas / $c->dias : 0;
                        $c->aproveitamento = $c->base_homem > 0 ? $c->base_maquina / $c->base_homem : null;
                        return $c;
                    });
    }


    // Jornadas impossíveis (zero ou acima do limite): provável erro de digitação
    private function aConferir(array $filtros)
    {
        $d = $this->duracaoSql();
        return $this->base($filtros)
                    ->leftJoin('colaborador', 'colaborador.id', 'informacao.colaborador')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->whereRaw("NOT ($d > 0 AND $d <= ".self::JORNADA_MAXIMA.')')
                    ->orderByDesc('informacao.data')
                    ->get([
                        'informacao.id', 'informacao.data', 'informacao.hora_inicial', 'informacao.hora_final',
                        'colaborador.colaborador', 'equipamento.equipamento', DB::raw("$d as duracao"),
                    ]);
    }
}
