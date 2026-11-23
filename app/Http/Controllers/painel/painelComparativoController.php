<?php

namespace App\Http\Controllers\painel;

use App\Support\ConsumoAbastecimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Comparativo por tipo e cilindros
 *
 * Agrupa as máquinas por Tipo + Cilindros do cadastro do equipamento (ex.: "FORWARDER 6 cilindros") e compara
 * consumo (L/h), horas, aproveitamento, produção e custo de manutenção, com as mesmas contas dos outros painéis.
 * Cadastros de terceiros (placa TERCEIRO) ficam de fora: cada um reúne várias máquinas.
 */
class painelComparativoController extends PainelBase
{
    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_comparativo', $camposFiltro);

        [$fatos, $periodos, $cilindros, $semCilindros] = $this->fatos($filtros);
        $totalRegistros = count($fatos);

        return view('painel.comparativo', compact('fatos', 'periodos', 'cilindros', 'semCilindros', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Planilha por máquina, com o total de cada grupo, e os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_comparativo', $this->camposFiltro());
        [$fatos] = $this->fatos($filtros);
        $num = fn($v) => $this->numeroPlanilha($v);
        $razao = fn($a, $b) => $b > 0 ? $num($a / $b) : '';

        $linha = function ($l, $grupo, $maquina) use ($num, $razao) {
            $horas = $l->sum('hm');
            $fm = $l->sum('fm');
            return [$grupo, $maquina, $l->where('hm', '>', 0)->pluck('eq')->unique()->count(), $num($horas),
                    $razao($l->sum('il'), $l->sum('ih')), $num($l->sum('di')), $razao($l->sum('bm') * 100, $l->sum('bh')),
                    $num($fm), $razao($fm, $l->sum('hp')), $fm > 0 ? number_format($l->sum('dp') / $fm, 3, ',', '') : '',
                    $num($l->sum('c')), $razao($l->sum('cr'), $horas)];
        };
        $linhas = [];
        foreach (collect($fatos)->groupBy('grupo')->sortKeys() as $grupo => $l) {
            $linhas[] = $linha($l, $grupo, 'TOTAL DO GRUPO');
            foreach ($l->groupBy('eq')->sortBy(fn($m) => $m->first()['maquina']) as $m) {
                $linhas[] = $linha($m, $grupo, $m->first()['maquina']);
            }
        }

        return $this->planilha('comparativo_tipo_cilindros',
            ['Grupo', 'Máquina', 'Máquinas com horas', 'Horas de máquina', 'Consumo (L/h)', 'Diesel (L)', 'Aproveitamento (%)',
             'Faturamento (m²)', 'Produtividade (m²/h)', 'Consumo por produção (L/m²)', 'Custo de manutenção (R$)', 'Custo por hora (R$/h)'], $linhas);
    }

    private function camposFiltro()
    {
        return $this->comPeriodo([
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'equipamento' => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_equipamento' => $this->campoTipoEquipamento(3),
            'ano_equipamento' => $this->campoAnoEquipamento(),
            'cilindros_equipamento' => $this->campoCilindrosEquipamento(),
        ]);
    }

    /**
     * [fatos, periodos, cilindros (quantidades que aparecem, em ordem), máquinas sem cilindros no cadastro]
     * Um lançamento (Informação Diária, manutenção ou revisão) por linha:
     * grupo, tipo, cil ('' = sem cilindros), maquina, eq (id), hm (horas máquina), bm/bh (horas máquina/homem dos lançamentos
     * com as duas), di (diesel), il/ih (litros/horas do intervalo de consumo), fm (m² faturados), hp (horas produzindo),
     * dp (diesel das máquinas com produção), c (custo de manutenção), cr (custo só das máquinas com horas no período).
     */
    private function fatos(array $filtros)
    {
        $cadastro = DB::table('equipamento')
                        ->leftJoin('tipo', 'tipo.id', 'equipamento.tipo')
                        ->where(fn($q) => $q->whereNull('equipamento.placa')->orWhere('equipamento.placa', '<>', ConsumoAbastecimento::PLACA_TERCEIROS))
                        ->get(['equipamento.id', 'equipamento.equipamento', 'equipamento.placa', 'equipamento.cilindros', 'equipamento.controla_media', 'tipo.tipo'])
                        ->keyBy('id');

        $lancamentos = $this->lancamentosInformacao($filtros, ['equipamento' => 'informacao.equipamento'],
                            fn($q) => $q->whereIn('informacao.equipamento', $cadastro->keys()));
        $faturamento = DB::table('informacao')->whereNull('deleted_at')->where('fat_m', '>', 0);
        $this->filtrarPeriodo($faturamento, $filtros, 'informacao.data');
        $faturamento = $faturamento->pluck('fat_m', 'id');
        $custos = array_filter(app(painelManutencaoController::class)->lancamentos($filtros), fn($l) => isset($cadastro[$l['eq']]));

        $intervalos = $this->intervalosConsumo($filtros['dtFinal'])['intervalos'];
        $periodos = $this->periodos($filtros, array_merge($lancamentos->pluck('data')->all(), array_column($custos, 'd')));

        // Máquinas com horas e com produção no período (produção, como no painel próprio, só das que controlam média)
        $comHoras = $comProducao = [];
        foreach ($lancamentos as $l) {
            if ($l->horas_maquina > 0) {
                $comHoras[$l->equipamento_id] = true;
            }
            if (isset($faturamento[$l->id]) && ConsumoAbastecimento::controlaMedia($cadastro[$l->equipamento_id]->controla_media)) {
                $comProducao[$l->equipamento_id] = true;
            }
        }

        $cilindros = $semCilindros = $fatos = [];
        $base = function ($id, $data) use ($cadastro, $periodos, &$cilindros, &$semCilindros) {
            $e = $cadastro[$id];
            $tipo = $e->tipo ?: 'SEM TIPO';
            $cil = (int) $e->cilindros;
            $maquina = $e->equipamento ?: $e->placa;
            if ($cil > 0) {
                $cilindros[$cil] = $cil;
            } else {
                $semCilindros[$id] = $maquina;
            }
            return ['eq' => $id, 'maquina' => $maquina, 'tipo' => $tipo, 'cil' => $cil > 0 ? (string) $cil : '',
                    'grupo' => $tipo.' '.($cil > 0 ? $cil.' cilindros' : 'sem cilindros'),
                    'd' => $data, 'p' => $periodos['chaveDe']($data)];
        };
        $zero = array_fill_keys(['hm', 'bm', 'bh', 'di', 'il', 'ih', 'fm', 'hp', 'dp', 'c', 'cr'], 0);

        foreach ($lancamentos as $l) {
            $consumo = $intervalos[$l->id] ?? null;
            $produz = isset($comProducao[$l->equipamento_id]);
            $fm = $produz ? (float) ($faturamento[$l->id] ?? 0) : 0;
            if ($l->horas_maquina <= 0 && $l->diesel <= 0 && $fm <= 0) {
                continue;
            }
            $ambos = $l->horas_homem > 0 && $l->horas_maquina > 0;
            $fatos[] = array_merge($base($l->equipamento_id, $l->data), $zero, [
                'hm' => round((float) $l->horas_maquina, 3),
                'bm' => $ambos ? round((float) $l->horas_maquina, 3) : 0, 'bh' => $ambos ? round((float) $l->horas_homem, 3) : 0,
                'di' => round((float) $l->diesel, 2),
                'il' => $consumo ? round($consumo['litros'], 2) : 0, 'ih' => $consumo ? round($consumo['horas'], 3) : 0,
                'fm' => round($fm, 2), 'hp' => $fm > 0 ? round((float) $l->horas_maquina, 3) : 0,
                'dp' => $produz ? round((float) $l->diesel, 2) : 0,
            ]);
        }
        foreach ($custos as $c) {
            $fatos[] = array_merge($base($c['eq'], $c['d']), $zero, ['c' => $c['total'], 'cr' => isset($comHoras[$c['eq']]) ? $c['total'] : 0]);
        }

        sort($cilindros);
        asort($semCilindros);
        return [$fatos, $periodos, $cilindros, $semCilindros];
    }
}
