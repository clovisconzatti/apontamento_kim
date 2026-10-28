<?php

namespace App\Http\Controllers\painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Revisões
 *
 * Por equipamento: a última revisão lançada, o horímetro atual (dia mais recente da
 * Informação Diária) e quanto falta para a próxima revisão.
 * Situação: vencida (falta <= 0), próxima (falta dentro dos últimos 10% do intervalo) ou em dia.
 */
class painelRevisaoController extends Controller
{
    // Faixa de atenção: últimos 10% do intervalo (KM/Horas) da revisão
    const FAIXA_ATENCAO = 0.10;

    const SITUACOES = [
        'vencida' => 'Vencida',
        'proxima' => 'Próxima',
        'em_dia'  => 'Em dia',
    ];

    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltros($request, 'painel_revisoes', $camposFiltro);

        $todas = $this->linhas($filtros);
        $linhas = $this->filtrarSituacao($todas, $filtros['situacao']);

        $resumo = [
            'acompanhados' => count($todas),
            'vencida'      => count(array_filter($todas, fn($l) => $l['situacao'] === 'vencida')),
            'proxima'      => count(array_filter($todas, fn($l) => $l['situacao'] === 'proxima')),
            'em_dia'       => count(array_filter($todas, fn($l) => $l['situacao'] === 'em_dia')),
            'sem_revisao'  => $this->equipamentosSemRevisao($filtros),
        ];
        $porMes = $this->revisoesPorMes($filtros);
        $totalRegistros = count($linhas);
        $situacoes = self::SITUACOES;

        return view('painel.revisoes', compact('linhas', 'resumo', 'porMes', 'camposFiltro', 'filtros', 'totalRegistros', 'situacoes'));
    }

    // Endereço amigável de cada situação (painel/revisoes/vencidas ...) -> chave interna
    const LISTAS = [
        'vencidas' => 'vencida',
        'proximas' => 'proxima',
        'em-dia'   => 'em_dia',
    ];

    // Lista de trabalho: só os equipamentos de uma situação, sem os outros filtros do painel
    public function lista($lista)
    {
        $situacao = self::LISTAS[$lista];
        $semFiltro = ['equipamento' => null, 'tipo_revisao' => null, 'situacao' => null];
        $todas = $this->linhas($semFiltro);
        $linhas = $this->filtrarSituacao($todas, $situacao);

        // Abas: endereço, nome e quantidade de cada situação
        $abas = [];
        foreach (self::LISTAS as $endereco => $chave) {
            $abas[$endereco] = [
                'chave'      => $chave,
                'nome'       => self::SITUACOES[$chave],
                'quantidade' => count($this->filtrarSituacao($todas, $chave)),
            ];
        }
        $situacoes = self::SITUACOES;

        return view('painel.revisoes_lista', compact('linhas', 'situacao', 'situacoes', 'abas', 'lista'));
    }

    // Planilha (CSV para Excel) com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltros($request, 'painel_revisoes', $this->camposFiltro());
        $linhas = $this->filtrarSituacao($this->linhas($filtros), $filtros['situacao']);

        $num = fn($v) => is_null($v) ? '' : number_format($v, 2, ',', '');
        $saida = fopen('php://temp', 'r+');
        fwrite($saida, "\xEF\xBB\xBF"); // BOM: o Excel abre os acentos corretamente
        fputcsv($saida, ['Equipamento', 'Tipo da última revisão', 'Data da última revisão', 'Nº OS', 'Hora/KM atual',
                         'Data do apontamento', 'Hora/KM próx. revisão', 'Falta', 'Intervalo', 'Situação'], ';');
        foreach ($linhas as $l) {
            fputcsv($saida, [
                $l['equipamento'], $l['tipo'], date('d/m/Y', strtotime($l['data_revisao'])), $l['nr_os'],
                $num($l['atual']), $l['data_atual'] ? date('d/m/Y', strtotime($l['data_atual'])) : '',
                $num($l['proxima']), $num($l['falta']), $num($l['intervalo']), self::SITUACOES[$l['situacao']],
            ], ';');
        }
        rewind($saida);
        $csv = stream_get_contents($saida);
        fclose($saida);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="revisoes_'.date('Y-m-d').'.csv"',
        ]);
    }

    private function camposFiltro()
    {
        return [
            'equipamento'  => ['label' => 'Equipamento', 'tipo' => 'select', 'col' => 4, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_revisao' => ['label' => 'Tipo da última revisão', 'tipo' => 'select', 'col' => 4, 'opcoes' => $this->opcoesTabela('tipo_revisao', 'descricao')],
            'situacao'     => ['label' => 'Situação', 'tipo' => 'select', 'col' => 2, 'opcoes' => self::SITUACOES],
        ];
    }

    private function filtrarSituacao(array $linhas, $situacao)
    {
        if (!$situacao) {
            return $linhas;
        }
        return array_values(array_filter($linhas, fn($l) => $l['situacao'] === $situacao));
    }

    // Uma linha por equipamento com revisão, ordenada da mais urgente para a mais folgada
    private function linhas(array $filtros)
    {
        // Última revisão de cada equipamento (data mais recente; no mesmo dia, o último lançamento)
        $revisoes = DB::table('revisao')
                        ->leftJoin('equipamento', 'equipamento.id', 'revisao.equipamento')
                        ->leftJoin('tipo_revisao', 'tipo_revisao.id', 'revisao.tipo_revisao')
                        ->whereNull('revisao.deleted_at')
                        ->when($filtros['equipamento'], fn($q, $v) => $q->where('revisao.equipamento', $v))
                        ->orderBy('revisao.data', 'DESC')
                        ->orderBy('revisao.id', 'DESC')
                        ->get([
                            'revisao.id', 'revisao.equipamento as equipamento_id', 'revisao.data', 'revisao.nr_os',
                            'revisao.tipo_revisao', 'revisao.horimetro_atual', 'revisao.horimetro_proxima',
                            'equipamento.placa', 'equipamento.equipamento', 'tipo_revisao.descricao as tipo',
                        ])
                        ->unique('equipamento_id');

        if ($filtros['tipo_revisao']) {
            $revisoes = $revisoes->where('tipo_revisao', $filtros['tipo_revisao']);
        }

        $atuais = $this->horimetrosAtuais($revisoes->pluck('equipamento_id')->all());

        $linhas = [];
        foreach ($revisoes as $r) {
            $apontamento = $atuais[$r->equipamento_id] ?? null;

            // Vale o horímetro do último dia com apontamento na Informação Diária. O da revisão só entra quando
            // não há apontamento ou quando o apontamento é anterior à revisão e tem horímetro menor que o dela
            $apontamentoValido = $apontamento
                && ($apontamento->data >= $r->data || (float) $apontamento->horimetro >= (float) $r->horimetro_atual);
            if ($apontamentoValido) {
                $atual = (float) $apontamento->horimetro;
                $dataAtual = $apontamento->data;
                $origem = 'informacao';
            } else {
                $atual = (float) $r->horimetro_atual;
                $dataAtual = $r->data;
                $origem = 'revisao';
            }

            $intervalo = (float) $r->horimetro_proxima - (float) $r->horimetro_atual;
            $falta = (float) $r->horimetro_proxima - $atual;
            $faixa = $intervalo * self::FAIXA_ATENCAO;

            if ($falta <= 0) {
                $situacao = 'vencida';
                $intensidade = 1;
            } elseif ($faixa > 0 && $falta <= $faixa) {
                $situacao = 'proxima';
                $intensidade = 1 - $falta / $faixa; // 0 ao entrar na faixa, 1 no limite
            } else {
                $situacao = 'em_dia';
                $intensidade = 0;
            }

            $linhas[] = [
                'equipamento_id'  => $r->equipamento_id,
                'tipo_revisao_id' => $r->tipo_revisao,
                'equipamento'  => trim($r->placa.($r->equipamento ? ' - '.$r->equipamento : '')),
                'tipo'         => $r->tipo,
                'data_revisao' => $r->data,
                'nr_os'        => $r->nr_os,
                'atual'        => $atual,
                'data_atual'   => $dataAtual,
                'origem_atual' => $origem,
                'proxima'      => (float) $r->horimetro_proxima,
                'falta'        => $falta,
                'intervalo'    => $intervalo,
                'usado'        => $intervalo > 0 ? ($atual - (float) $r->horimetro_atual) / $intervalo : 0, // fração do intervalo já rodada
                'situacao'     => $situacao,
                'intensidade'  => $intensidade,
            ];
        }

        // Mais urgente primeiro: vencidas, depois próximas, depois em dia; dentro de cada uma, o que falta menos
        $ordem = ['vencida' => 0, 'proxima' => 1, 'em_dia' => 2];
        usort($linhas, fn($a, $b) => [$ordem[$a['situacao']], $a['falta']] <=> [$ordem[$b['situacao']], $b['falta']]);

        return $linhas;
    }

    // Horímetro do dia mais recente da Informação Diária de cada equipamento (o maior lançado naquele dia)
    private function horimetrosAtuais(array $equipamentos)
    {
        if (!$equipamentos) {
            return [];
        }
        $horimetro = 'COALESCE(informacao.horimetro_final, informacao.horimetro_inicial)';

        $ultimoDia = DB::table('informacao')
                        ->whereIn('equipamento', $equipamentos)
                        ->whereNull('deleted_at')
                        ->whereRaw("$horimetro IS NOT NULL")
                        ->groupBy('equipamento')
                        ->select('equipamento', DB::raw('MAX(data) as data'));

        return DB::table('informacao')
                    ->joinSub($ultimoDia, 'ultimo', function ($join) {
                        $join->on('ultimo.equipamento', '=', 'informacao.equipamento')
                             ->on('ultimo.data', '=', 'informacao.data');
                    })
                    ->whereNull('informacao.deleted_at')
                    ->groupBy('informacao.equipamento', 'informacao.data')
                    ->get(['informacao.equipamento', 'informacao.data', DB::raw("MAX($horimetro) as horimetro")])
                    ->keyBy('equipamento')
                    ->all();
    }

    // Equipamentos ativos que ainda não têm nenhuma revisão lançada
    private function equipamentosSemRevisao(array $filtros)
    {
        if ($filtros['equipamento'] || $filtros['tipo_revisao'] || $filtros['situacao']) {
            return null; // com filtro a contagem não se aplica
        }
        return DB::table('equipamento')
                    ->whereNull('deleted_at')
                    ->where(fn($q) => $q->whereNull('ativo')->orWhere('ativo', '<>', 'Nao'))
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))->from('revisao')
                          ->whereColumn('revisao.equipamento', 'equipamento.id')
                          ->whereNull('revisao.deleted_at');
                    })
                    ->count();
    }

    // Revisões lançadas por mês nos últimos 12 meses (meses sem revisão aparecem com zero)
    private function revisoesPorMes(array $filtros)
    {
        $inicio = date('Y-m-01', strtotime('-11 months'));
        $contagem = DB::table('revisao')
                        ->whereNull('deleted_at')
                        ->where('data', '>=', $inicio)
                        ->when($filtros['equipamento'], fn($q, $v) => $q->where('equipamento', $v))
                        ->when($filtros['tipo_revisao'], fn($q, $v) => $q->where('tipo_revisao', $v))
                        ->groupBy(DB::raw("DATE_FORMAT(data, '%Y-%m')"))
                        ->selectRaw("COUNT(*) as total, DATE_FORMAT(data, '%Y-%m') as mes")->pluck('total', 'mes')
                        ->all();

        $meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        $serie = [];
        for ($i = 0; $i < 12; $i++) {
            $mes = strtotime("$inicio +$i months");
            $chave = date('Y-m', $mes);
            $serie[] = [
                'rotulo'     => $meses[date('n', $mes) - 1].'/'.date('y', $mes),
                'quantidade' => (int) ($contagem[$chave] ?? 0),
            ];
        }
        return $serie;
    }
}
