<?php

namespace App\Http\Controllers\painel;

use App\Support\ConsumoAbastecimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Painel de Operações · Consumo Fora da Faixa
 *
 * Abastecimentos cuja média (L/h, mesma conta da Informação Diária e do Consumo Médio — ver App\Support\ConsumoAbastecimento)
 * ficou abaixo do consumo mínimo ou acima do consumo máximo do cadastro do equipamento.
 * Só equipamentos com "Controla Média" = Sim e com consumo mínimo e/ou máximo preenchido (limite vazio ou zero não é avaliado).
 */
class painelConsumoForaFaixaController extends PainelBase
{
    const SITUACOES = ['abaixo' => 'Abaixo do mínimo', 'acima' => 'Acima do máximo'];

    public function index(Request $request)
    {
        $camposFiltro = $this->camposFiltro();
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_fora_faixa', $camposFiltro);

        [$fora, $medidos] = $this->dados($filtros);
        $maquinas = $this->porMaquina($fora, $medidos);
        $totalRegistros = count($fora);

        return view('painel.consumo_fora_faixa', compact('fora', 'medidos', 'maquinas', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Planilha com cada abastecimento fora da faixa, com os mesmos filtros da tela
    public function exportar(Request $request)
    {
        $filtros = $this->lerFiltrosPainel($request, 'painel_consumo_fora_faixa', $this->camposFiltro());
        [$fora] = $this->dados($filtros);
        $num = fn($v) => $this->numeroPlanilha($v);

        return $this->planilha('consumo_fora_faixa',
            ['Data do abastecimento', 'Máquina', 'Fazenda', 'Colaborador', 'Abastecimento anterior', 'Horímetro anterior', 'Horímetro',
             'Horas', 'Litros', 'Consumo (L/h)', 'Consumo mínimo (L/h)', 'Consumo máximo (L/h)', 'Situação', 'Diferença (L/h)', 'Diferença (%)'],
            collect($fora)->map(fn($f) => [
                date('d/m/Y', strtotime($f['data'])), $f['maquina'], $f['fazenda'], $f['colaborador'],
                date('d/m/Y', strtotime($f['data_ref'])), $num($f['horimetro_ref']), $num($f['horimetro']),
                $num($f['horas']), $num($f['litros']), $num($f['media']),
                $f['minimo'] > 0 ? $num($f['minimo']) : '', $f['maximo'] > 0 ? $num($f['maximo']) : '',
                self::SITUACOES[$f['situacao']], $num($f['diferenca']), $num($f['percentual']),
            ]));
    }

    private function camposFiltro()
    {
        return $this->comPeriodo([
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'fazenda'     => ['label' => 'Fazenda', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('fazenda', 'fazenda')],
            'equipamento' => ['label' => 'Máquina', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_equipamento' => $this->campoTipoEquipamento(),
            'situacao'    => ['label' => 'Situação', 'tipo' => 'select', 'col' => 2, 'opcoes' => self::SITUACOES],
        ]);
    }

    /**
     * [fora, medidos]
     * fora:    um abastecimento fora da faixa por linha (mais recentes primeiro), com a conta completa
     * medidos: [máquina => [n, litros, horas]] de todos os abastecimentos medidos no período (das máquinas com faixa),
     *          para o % fora da faixa e a média da máquina no período
     */
    private function dados(array $filtros)
    {
        $query = DB::table('informacao')
                    ->join('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->leftJoin('fazenda', 'fazenda.id', 'informacao.fazenda')
                    ->leftJoin('colaborador', 'colaborador.id', 'informacao.colaborador')
                    ->whereNull('informacao.deleted_at')
                    ->where('informacao.qnt_diesel', '>', 0)
                    ->where(fn($q) => $q->where('equipamento.consumo_minimo', '>', 0)->orWhere('equipamento.consumo_maximo', '>', 0));
        ConsumoAbastecimento::filtrarControlaMedia($query);
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, ['fazenda' => 'informacao.fazenda', 'equipamento' => 'informacao.equipamento']);
        $this->filtrarTipoEquipamento($query, $filtros, 'informacao.equipamento');

        $lancamentos = $query->orderByDesc('informacao.data')->orderByDesc('informacao.id')->get([
            'informacao.id', 'informacao.data', 'informacao.equipamento as equipamento_id',
            'equipamento.consumo_minimo', 'equipamento.consumo_maximo',
            DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
            DB::raw("COALESCE(fazenda.fazenda, 'Sem fazenda') as fazenda"),
            DB::raw("COALESCE(colaborador.colaborador, 'Sem colaborador') as colaborador"),
        ]);
        if ($lancamentos->isEmpty()) {
            return [[], []];
        }

        $intervalos = ConsumoAbastecimento::intervalos($filtros['dtFinal'] ?: null, $lancamentos->pluck('equipamento_id')->unique()->values()->all())['intervalos'];

        $fora = [];
        $medidos = [];
        foreach ($lancamentos as $l) {
            $i = $intervalos[$l->id] ?? null;
            if (!$i) {
                continue;
            }
            $medidos[$l->maquina]['n'] = ($medidos[$l->maquina]['n'] ?? 0) + 1;
            $medidos[$l->maquina]['litros'] = ($medidos[$l->maquina]['litros'] ?? 0) + $i['litros'];
            $medidos[$l->maquina]['horas'] = ($medidos[$l->maquina]['horas'] ?? 0) + $i['horas'];

            $media = round($i['litros'] / $i['horas'], 2);
            $situacao = ConsumoAbastecimento::foraDaFaixa($media, $l->consumo_minimo, $l->consumo_maximo);
            if (!$situacao || (!empty($filtros['situacao']) && $filtros['situacao'] !== $situacao)) {
                continue;
            }
            $limite = (float) ($situacao === 'abaixo' ? $l->consumo_minimo : $l->consumo_maximo);
            $fora[] = [
                'id' => $l->id, 'data' => $l->data, 'maquina' => $l->maquina, 'fazenda' => $l->fazenda, 'colaborador' => $l->colaborador,
                'litros' => $i['litros'], 'horas' => $i['horas'], 'media' => $media,
                'horimetro' => $i['horimetro'], 'horimetro_ref' => $i['horimetro_ref'], 'data_ref' => $i['data_ref'],
                'minimo' => (float) $l->consumo_minimo, 'maximo' => (float) $l->consumo_maximo, 'situacao' => $situacao,
                'diferenca' => $media - $limite, 'percentual' => ($media - $limite) / $limite * 100,
            ];
        }

        return [$fora, $medidos];
    }

    // Resumo por máquina (só as que tiveram abastecimento fora da faixa), da que mais saiu da faixa para a que menos
    private function porMaquina(array $fora, array $medidos)
    {
        return collect($fora)->groupBy('maquina')->map(function ($l, $maquina) use ($medidos) {
            $primeiro = $l->first();
            $m = $medidos[$maquina];
            $media = round($m['litros'] / $m['horas'], 2);
            return [
                'maquina'  => $maquina,
                'minimo'   => $primeiro['minimo'],
                'maximo'   => $primeiro['maximo'],
                'medidos'  => $m['n'],
                'fora'     => $l->count(),
                'abaixo'   => $l->where('situacao', 'abaixo')->count(),
                'acima'    => $l->where('situacao', 'acima')->count(),
                'media'    => $media,  // de todos os abastecimentos medidos da máquina no período
                'situacao' => ConsumoAbastecimento::foraDaFaixa($media, $primeiro['minimo'], $primeiro['maximo']),
                'menor'    => $l->min('media'),
                'maior'    => $l->max('media'),
                'ultimo'   => $l->max('data'),
            ];
        })->sortByDesc(fn($m) => [$m['fora'] / max($m['medidos'], 1), $m['fora']])->values()->all();
    }
}
