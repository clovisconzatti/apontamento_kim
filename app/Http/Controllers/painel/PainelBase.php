<?php

namespace App\Http\Controllers\painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Base dos painéis do Painel de Operações que leem a Informação Diária:
 * cálculo de horas, série no tempo, período padrão e exportação para o Excel.
 */
abstract class PainelBase extends Controller
{
    // Acima disso, um lançamento é tratado como erro de digitação
    const JORNADA_MAXIMA = 16;   // horas homem (hora inicial -> final)
    const HORIMETRO_MAXIMO = 24; // horas de máquina (horímetro final - inicial)

    // Filtros da tela; sem filtro salvo, começa no mês atual
    protected function lerFiltrosPainel(Request $request, string $tela, array $campos)
    {
        return $this->lerFiltros($request, $tela, $campos, [
            'dtInicial' => date('Y-m-01'),
            'dtFinal'   => date('Y-m-d'),
        ]);
    }

    // Duração do lançamento em horas pela hora inicial/final (turno que vira o dia soma 24h)
    protected function duracaoSql()
    {
        return "(CASE WHEN informacao.hora_final >= informacao.hora_inicial
                      THEN TIME_TO_SEC(TIMEDIFF(informacao.hora_final, informacao.hora_inicial))
                      ELSE TIME_TO_SEC(TIMEDIFF(informacao.hora_final, informacao.hora_inicial)) + 86400 END) / 3600";
    }

    // Horas homem válidas do lançamento (0 quando vazio ou fora do limite)
    protected function horasHomemSql()
    {
        $d = $this->duracaoSql();
        return "(CASE WHEN informacao.hora_inicial IS NOT NULL AND informacao.hora_final IS NOT NULL
                       AND $d > 0 AND $d <= ".self::JORNADA_MAXIMA." THEN $d ELSE 0 END)";
    }

    // Horas de máquina válidas do lançamento pelo horímetro (0 quando vazio ou fora do limite)
    protected function horasMaquinaSql()
    {
        $d = 'informacao.horimetro_final - informacao.horimetro_inicial';
        return "(CASE WHEN $d >= 0 AND $d <= ".self::HORIMETRO_MAXIMO." THEN $d ELSE 0 END)";
    }

    // Períodos do gráfico de evolução: por dia até 45 dias, por semana até ~1 ano, por mês acima disso.
    // Devolve ['escala', 'chaves' => [['chave', 'rotulo', 'dica'], ...] em ordem, 'chaveDe' => fn(data) => chave]
    protected function periodos(array $filtros, array $datas)
    {
        sort($datas);
        $inicio = $filtros['dtInicial'] ?: (reset($datas) ?: date('Y-m-d'));
        $fim = $filtros['dtFinal'] ?: (end($datas) ?: date('Y-m-d'));
        $dias = (strtotime($fim) - strtotime($inicio)) / 86400 + 1;
        $escala = $dias <= 45 ? 'dia' : ($dias <= 400 ? 'semana' : 'mes');

        $chaveDe = function ($data) use ($escala) {
            $t = strtotime($data);
            if ($escala === 'dia') {
                return date('Y-m-d', $t);
            }
            return $escala === 'semana' ? date('Y-m-d', strtotime('monday this week', $t)) : date('Y-m', $t);
        };

        $meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        $chaves = [];
        for ($t = strtotime($inicio); $t <= strtotime($fim); $t = strtotime('+1 day', $t)) {
            $chave = $chaveDe(date('Y-m-d', $t));
            if (isset($chaves[$chave])) {
                continue;
            }
            $c = strtotime($escala === 'mes' ? $chave.'-01' : $chave);
            if ($escala === 'dia') {
                $chaves[$chave] = ['chave' => $chave, 'rotulo' => date('d/m', $c), 'dica' => date('d/m/Y', $c)];
            } elseif ($escala === 'semana') {
                $chaves[$chave] = ['chave' => $chave, 'rotulo' => date('d/m', $c), 'dica' => 'Semana de '.date('d/m/Y', $c)];
            } else {
                $rotulo = $meses[date('n', $c) - 1].'/'.date('y', $c);
                $chaves[$chave] = ['chave' => $chave, 'rotulo' => $rotulo, 'dica' => $rotulo];
            }
        }

        return ['escala' => $escala, 'chaves' => array_values($chaves), 'chaveDe' => $chaveDe];
    }

    // Lançamentos da Informação Diária já com nomes e horas calculadas, um por linha (base dos gráficos interativos).
    // $filtrosIgual: ['campo do filtro' => 'informacao.coluna']; $restringir: fn($query) para condições do painel.
    protected function lancamentosInformacao(array $filtros, array $filtrosIgual, callable $restringir = null)
    {
        $query = DB::table('informacao')
                    ->leftJoin('colaborador', 'colaborador.id', 'informacao.colaborador')
                    ->leftJoin('fazenda', 'fazenda.id', 'informacao.fazenda')
                    ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                    ->leftJoin('atividade', 'atividade.id', 'informacao.atividade')
                    ->leftJoin('comboio', 'comboio.id', 'informacao.origem_abastecimento')
                    ->whereNull('informacao.deleted_at');
        $this->filtrarPeriodo($query, $filtros, 'informacao.data');
        $this->filtrarIgual($query, $filtros, $filtrosIgual);
        if ($restringir) {
            $restringir($query);
        }

        return $query->orderBy('informacao.data')->get([
            'informacao.id', 'informacao.data', 'informacao.equipamento as equipamento_id',
            DB::raw("COALESCE(colaborador.colaborador, 'Sem colaborador') as colaborador"),
            DB::raw("COALESCE(fazenda.fazenda, 'Sem fazenda') as fazenda"),
            DB::raw("COALESCE(equipamento.equipamento, equipamento.placa, 'Sem máquina') as maquina"),
            DB::raw("COALESCE(atividade.atividade, 'Sem atividade') as atividade"),
            DB::raw("COALESCE(comboio.tanque, 'Sem tanque informado') as tanque"),
            DB::raw($this->horasHomemSql().' as horas_homem'),
            DB::raw($this->horasMaquinaSql().' as horas_maquina'),
            DB::raw('COALESCE(informacao.qnt_diesel, 0) as diesel'),
            'informacao.horimetro_inicial', 'informacao.horimetro_final', 'informacao.hora_inicial', 'informacao.hora_final',
        ]);
    }

    // Faixa de consumo (L/h) do cadastro de cada máquina: [nome da máquina => [min, max]]
    protected function faixasConsumo()
    {
        return DB::table('equipamento')
                    ->where(fn($q) => $q->where('consumo_minimo', '>', 0)->orWhere('consumo_maximo', '>', 0))
                    ->get(['equipamento', 'placa', 'consumo_minimo', 'consumo_maximo'])
                    ->mapWithKeys(fn($e) => [($e->equipamento ?: $e->placa) => [(float) $e->consumo_minimo, (float) ($e->consumo_maximo ?: $e->consumo_minimo)]])
                    ->all();
    }

    // Um intervalo entre abastecimentos acima disso não serve de referência (provável horímetro digitado errado)
    const INTERVALO_MAXIMO = 300;

    /**
     * Consumo pelos abastecimentos (tanque cheio): litros abastecidos ÷ horas desde o abastecimento anterior.
     * Para cada máquina, em ordem de data: compara o horímetro do abastecimento com o do abastecimento anterior que
     * tenha horímetro (sem horímetro, procura na data anterior; os litros dele somam no intervalo seguinte).
     * Mesmo horímetro da referência: os litros seguem para o próximo intervalo (não é erro).
     * Horímetro menor que a referência ou mais de INTERVALO_MAXIMO acima dela vai para a conferência e fica como
     * "candidata": se o próximo seguir a referência, era erro de digitação; se seguir a candidata, o horímetro
     * mudou de patamar (troca de painel, lançamento de outra máquina) e a candidata passa a ser a referência.
     * Considera todo o histórico até $ate (o abastecimento anterior pode ser de antes do período filtrado).
     *
     * Devolve ['intervalos' => [id do abastecimento => [litros, horas, data_ref, horimetro_ref, horimetro]],
     *          'conferir'   => [[id, data, maquina, litros, horimetro, horimetro_ref, data_ref], ...]]
     */
    protected function intervalosConsumo($ate)
    {
        $abastecimentos = DB::table('informacao')
                            ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                            ->whereNull('informacao.deleted_at')
                            ->where('informacao.qnt_diesel', '>', 0)
                            ->where('informacao.equipamento', '>', 0)
                            ->when($ate, fn($q) => $q->where('informacao.data', '<=', $ate))
                            ->orderBy('informacao.equipamento')
                            ->orderBy('informacao.data')
                            ->orderByRaw('informacao.horimetro_abastecimento IS NULL, informacao.horimetro_abastecimento, informacao.id')
                            ->get([
                                'informacao.id', 'informacao.data', 'informacao.equipamento', 'informacao.qnt_diesel',
                                'informacao.horimetro_abastecimento',
                                DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
                            ]);

        $intervalos = [];
        $conferir = [];
        $maquina = null;
        $valido = fn($horas) => $horas > 0 && $horas <= self::INTERVALO_MAXIMO;

        foreach ($abastecimentos as $a) {
            if ($a->equipamento !== $maquina) {
                $maquina = $a->equipamento;
                $referencia = null;  // último horímetro aceito
                $litros = 0;         // litros abastecidos depois da referência
                $candidata = null;   // leitura fora da sequência, à espera de confirmação
                $litrosCandidata = 0;
            }
            if ($referencia) {
                $litros += (float) $a->qnt_diesel;
            }
            if ($candidata) {
                $litrosCandidata += (float) $a->qnt_diesel;
            }
            $horimetro = $a->horimetro_abastecimento > 0 ? (float) $a->horimetro_abastecimento : null;
            if (is_null($horimetro)) {
                continue; // sem horímetro: os litros seguem para o próximo intervalo
            }
            if (!$referencia) {
                $referencia = ['data' => $a->data, 'horimetro' => $horimetro];
                continue;
            }
            if ($horimetro == $referencia['horimetro']) {
                continue; // mesmo horímetro do anterior (abasteceu sem trabalhar): os litros seguem para o próximo intervalo
            }

            $horas = $horimetro - $referencia['horimetro'];
            if ($valido($horas)) {
                // Segue a sequência: fecha o intervalo; se havia candidata, ela era só um erro de digitação
                $intervalos[$a->id] = [
                    'litros' => $litros, 'horas' => $horas, 'data_ref' => $referencia['data'],
                    'horimetro_ref' => $referencia['horimetro'], 'horimetro' => $horimetro,
                ];
            } elseif ($candidata && $valido($horimetro - $candidata['horimetro'])) {
                // Segue a candidata: o horímetro mudou de patamar de verdade; a candidata vira a referência
                $intervalos[$a->id] = [
                    'litros' => $litrosCandidata, 'horas' => $horimetro - $candidata['horimetro'], 'data_ref' => $candidata['data'],
                    'horimetro_ref' => $candidata['horimetro'], 'horimetro' => $horimetro,
                ];
            } else {
                // Fora da sequência: vai para a conferência e fica como candidata; a referência continua a mesma
                $conferir[] = [
                    'id' => $a->id, 'data' => $a->data, 'maquina' => $a->maquina, 'litros' => (float) $a->qnt_diesel,
                    'horimetro' => $horimetro, 'horimetro_ref' => $referencia['horimetro'], 'data_ref' => $referencia['data'],
                ];
                $candidata = ['data' => $a->data, 'horimetro' => $horimetro];
                $litrosCandidata = 0;
                continue;
            }
            $referencia = ['data' => $a->data, 'horimetro' => $horimetro];
            $litros = 0;
            $candidata = null;
            $litrosCandidata = 0;
        }

        return ['intervalos' => $intervalos, 'conferir' => $conferir];
    }

    // Planilha CSV que o Excel abre direto (separador ";" e acentos corretos)
    protected function planilha(string $arquivo, array $cabecalho, iterable $linhas)
    {
        $saida = fopen('php://temp', 'r+');
        fwrite($saida, "\xEF\xBB\xBF");
        fputcsv($saida, $cabecalho, ';');
        foreach ($linhas as $linha) {
            fputcsv($saida, $linha, ';');
        }
        rewind($saida);
        $csv = stream_get_contents($saida);
        fclose($saida);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$arquivo.'_'.date('Y-m-d').'.csv"',
        ]);
    }

    // Número no formato do Excel brasileiro (1234,50)
    protected function numeroPlanilha($valor)
    {
        return is_null($valor) ? '' : number_format($valor, 2, ',', '');
    }
}
