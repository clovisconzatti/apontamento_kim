<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Saldo de combustível dos tanques próprios (comboio.tipo = 'proprio').
 *
 * Saldo = saldo inicial (cadastro do comboio) + todos os movimentos do tanque:
 *   + compra de combustível (tanque de destino)
 *   + transferência recebida (campo Tanque/Comboio)       − transferência enviada (campo Origem)
 *   − abastecimento na Informação Diária (Tanque de Origem)
 *   + ajuste Sobra                                         − ajuste Falta
 * O saldo inicial entra antes de todos os movimentos; a data do saldo inicial é só o registro de quando foi informado.
 * Só tanques próprios ativos: postos externos (só origem de combustível) e comboios inativos não têm saldo.
 */
class SaldoCombustivel
{
    // Abaixo disso (fração da capacidade) o tanque está com saldo baixo
    const NIVEL_BAIXO = 0.20;

    const TIPOS = [
        'compra'         => ['nome' => 'Compra',                 'sinal' => 1],
        'transf_entrada' => ['nome' => 'Transferência recebida', 'sinal' => 1],
        'sobra'          => ['nome' => 'Ajuste: sobra',          'sinal' => 1],
        'abastecimento'  => ['nome' => 'Abastecimento',          'sinal' => -1],
        'transf_saida'   => ['nome' => 'Transferência enviada',  'sinal' => -1],
        'falta'          => ['nome' => 'Ajuste: falta',          'sinal' => -1],
    ];

    const SITUACOES = [
        'negativo'    => ['nome' => 'Saldo negativo',       'cor' => '#d03b3b', 'icone' => 'fas fa-exclamation-circle'],
        'acima'       => ['nome' => 'Acima da capacidade',  'cor' => '#ec835a', 'icone' => 'fas fa-arrow-up'],
        'baixo'       => ['nome' => 'Saldo baixo',          'cor' => '#fab219', 'icone' => 'fas fa-exclamation-triangle'],
        'ok'          => ['nome' => 'Normal',               'cor' => '#0ca30c', 'icone' => 'fas fa-check-circle'],
    ];

    // Tanques próprios ativos com o cadastro do saldo inicial: [id => objeto] (comboio inativo não entra no saldo)
    public static function tanques()
    {
        return DB::table('comboio')
                    ->whereNull('deleted_at')
                    ->where(fn($q) => $q->whereNull('tipo')->orWhere('tipo', '<>', 'posto'))
                    ->where(fn($q) => $q->whereNull('ativo')->orWhere('ativo', '<>', 'Nao'))
                    ->orderBy('tanque')
                    ->get(['id', 'tanque', 'capacidade', 'ativo', 'saldo_inicial', 'data_saldo_inicial'])
                    ->keyBy('id');
    }

    /**
     * Todos os movimentos dos tanques próprios até $ate, em ordem de data,
     * cada um com o saldo do tanque logo depois dele (partindo do saldo inicial).
     * Cada movimento: ['id', 'origem' (tabela), 'data', 'tanque_id', 'tanque', 'tipo', 'litros' (com sinal), 'descricao', 'saldo']
     */
    public static function movimentos($ate = null, $tanques = null)
    {
        $tanques = $tanques ?: self::tanques();
        $ids = $tanques->keys()->all();
        if (!$ids) {
            return collect();
        }
        $ate = $ate ?: date('Y-m-d');
        $movimentos = [];
        $add = function ($origem, $id, $data, $tanque, $tipo, $litros, $descricao) use (&$movimentos) {
            $movimentos[] = [
                'id' => $id, 'origem' => $origem, 'data' => $data, 'tanque_id' => (int) $tanque, 'tipo' => $tipo,
                'litros' => self::TIPOS[$tipo]['sinal'] * abs((float) $litros), 'descricao' => $descricao,
            ];
        };

        foreach (DB::table('compra_combustivel')
                    ->leftJoin('fornecedor', 'fornecedor.id', 'compra_combustivel.fornecedor')
                    ->whereNull('compra_combustivel.deleted_at')->whereIn('compra_combustivel.tanque', $ids)
                    ->where('compra_combustivel.data', '<=', $ate)
                    ->get(['compra_combustivel.id', 'compra_combustivel.data', 'compra_combustivel.tanque', 'compra_combustivel.litros',
                           'compra_combustivel.nr_nf', 'fornecedor.fornecedor']) as $c) {
            $add('compra_combustivel', $c->id, $c->data, $c->tanque, 'compra', $c->litros,
                 trim(($c->fornecedor ?: 'Fornecedor').($c->nr_nf ? ' · NF '.$c->nr_nf : '')));
        }

        $transferencias = DB::table('transferencia')
                            ->leftJoin('comboio as origem', 'origem.id', 'transferencia.origem')
                            ->leftJoin('comboio as destino', 'destino.id', 'transferencia.tanque')
                            ->leftJoin('equipamento', 'equipamento.id', 'transferencia.destino')
                            ->whereNull('transferencia.deleted_at')
                            ->where('transferencia.data', '<=', $ate)
                            ->where(fn($q) => $q->whereIn('transferencia.origem', $ids)->orWhereIn('transferencia.tanque', $ids))
                            ->get(['transferencia.id', 'transferencia.data', 'transferencia.origem', 'transferencia.tanque', 'transferencia.litros',
                                   'transferencia.nr_doc', 'origem.tanque as nome_origem', 'destino.tanque as nome_destino',
                                   DB::raw('COALESCE(equipamento.equipamento, equipamento.placa) as equipamento')]);
        foreach ($transferencias as $t) {
            $doc = $t->nr_doc ? ' · doc '.$t->nr_doc : '';
            if (in_array($t->origem, $ids)) {
                $para = $t->nome_destino ?: ($t->equipamento ?: 'destino não informado');
                $add('transferencia', $t->id, $t->data, $t->origem, 'transf_saida', $t->litros, 'Para '.$para.$doc);
            }
            if (in_array($t->tanque, $ids)) {
                $add('transferencia', $t->id, $t->data, $t->tanque, 'transf_entrada', $t->litros, 'De '.($t->nome_origem ?: 'origem não informada').$doc);
            }
        }

        foreach (DB::table('informacao')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->whereNull('informacao.deleted_at')->where('informacao.qnt_diesel', '>', 0)
                    ->whereIn('informacao.origem_abastecimento', $ids)->where('informacao.data', '<=', $ate)
                    ->get(['informacao.id', 'informacao.data', 'informacao.origem_abastecimento', 'informacao.qnt_diesel', 'informacao.nr_nf',
                           DB::raw('COALESCE(equipamento.equipamento, equipamento.placa) as equipamento')]) as $i) {
            $add('informacao', $i->id, $i->data, $i->origem_abastecimento, 'abastecimento', $i->qnt_diesel,
                 ($i->equipamento ?: 'Máquina não informada').($i->nr_nf ? ' · NF '.$i->nr_nf : ''));
        }

        foreach (DB::table('ajuste_combustivel')
                    ->whereNull('deleted_at')->whereIn('tanque', $ids)->where('data', '<=', $ate)
                    ->get(['id', 'data', 'tanque', 'quantidade', 'tipo']) as $a) {
            $add('ajuste_combustivel', $a->id, $a->data, $a->tanque, $a->tipo === 'falta' ? 'falta' : 'sobra', $a->quantidade, 'Ajuste de saldo');
        }

        // Em ordem, com o saldo corrente de cada tanque (partindo do saldo inicial)
        $ordemTipo = array_flip(array_keys(self::TIPOS)); // no mesmo dia: entradas antes das saídas
        usort($movimentos, fn($a, $b) => [$a['data'], $ordemTipo[$a['tipo']], $a['id']] <=> [$b['data'], $ordemTipo[$b['tipo']], $b['id']]);

        $saldo = $tanques->map(fn($t) => (float) $t->saldo_inicial)->all();
        foreach ($movimentos as &$m) {
            $saldo[$m['tanque_id']] += $m['litros'];
            $m['saldo'] = $saldo[$m['tanque_id']];
            $m['tanque'] = $tanques[$m['tanque_id']]->tanque;
        }
        unset($m);

        return collect($movimentos);
    }

    /**
     * Saldo atual (até $ate) de cada tanque próprio, com capacidade, ocupação, situação e último lançamento.
     */
    public static function saldos($ate = null, $movimentos = null)
    {
        $tanques = self::tanques();
        $movimentos = $movimentos ?: self::movimentos($ate, $tanques);
        $porTanque = $movimentos->groupBy('tanque_id');

        return $tanques->map(function ($t) use ($porTanque) {
            $lista = $porTanque[$t->id] ?? collect();
            $saldo = (float) $t->saldo_inicial + $lista->sum('litros');
            $capacidade = $t->capacidade > 0 ? (float) $t->capacidade : null;
            $ocupacao = $capacidade ? $saldo / $capacidade : null;

            if ($saldo < 0) {
                $situacao = 'negativo';
            } elseif ($capacidade && $saldo > $capacidade * 1.02) {
                $situacao = 'acima';
            } elseif ($capacidade && $ocupacao < self::NIVEL_BAIXO) {
                $situacao = 'baixo';
            } else {
                $situacao = 'ok';
            }

            return (object) [
                'id'                 => $t->id,
                'tanque'             => $t->tanque,
                'ativo'              => $t->ativo !== 'Nao',
                'saldo'              => $saldo,
                'capacidade'         => $capacidade,
                'ocupacao'           => $ocupacao,
                'situacao'           => $situacao,
                'saldo_inicial'      => $t->saldo_inicial,
                'data_saldo_inicial' => $t->data_saldo_inicial,
                'sem_saldo_inicial'  => is_null($t->saldo_inicial),
                'entradas'           => $lista->where('litros', '>', 0)->sum('litros'),
                'saidas'             => -$lista->where('litros', '<', 0)->sum('litros'),
                'ultimo_lancamento'  => $lista->max('data'),
            ];
        })->values();
    }
}
