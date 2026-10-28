<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Consumo pelos abastecimentos (tanque cheio): litros abastecidos ÷ horas desde o abastecimento anterior.
 * Usado na listagem da Informação Diária e nos painéis (Consumo Médio, Combustível, Horas Máquina).
 *
 * Para cada máquina, em ordem de data: compara o horímetro do abastecimento com o do abastecimento anterior que
 * tenha horímetro (sem horímetro, procura na data anterior; os litros dele somam no intervalo seguinte).
 * Mesmo horímetro da referência: os litros seguem para o próximo intervalo (não é erro).
 * Horímetro menor que a referência ou mais de INTERVALO_MAXIMO acima dela vai para a conferência e fica como
 * "candidata": se o próximo seguir a referência, era erro de digitação; se seguir a candidata, o horímetro
 * mudou de patamar (troca de painel, lançamento de outra máquina) e a candidata passa a ser a referência.
 * Antes disso, o horímetro do abastecimento é comparado com o horímetro do próprio dia (inicial/final do lançamento):
 * se estiver mais de TOLERANCIA_DIA fora da faixa, vai direto para a conferência (erro de digitação, ex.: 5547 por 554,7).
 * Equipamentos de terceiros (placa TERCEIRO) ficam de fora: o cadastro reúne várias máquinas.
 */
class ConsumoAbastecimento
{
    // Um intervalo entre abastecimentos acima disso não serve de referência (provável horímetro digitado errado)
    const INTERVALO_MAXIMO = 300;

    // O horímetro do abastecimento pode ficar até isso fora da faixa inicial–final do dia (abasteceu antes de começar ou depois de parar)
    const TOLERANCIA_DIA = 24;

    // Cadastros com esta placa reúnem várias máquinas de terceiros, cada uma com o seu horímetro: o consumo não é calculado
    const PLACA_TERCEIROS = 'TERCEIRO';

    const MOTIVOS = [
        'terceiro' => 'Equipamento de terceiros: o cadastro reúne várias máquinas (cada uma com o seu horímetro), o consumo não é calculado',
        'primeiro' => 'Primeiro abastecimento com horímetro desta máquina: ainda não há anterior para comparar',
        'mesmo'    => 'Mesmo horímetro do abastecimento anterior: os litros entram no próximo intervalo',
        'reinicio' => 'A sequência recomeça aqui: o abastecimento anterior tinha o horímetro fora da sequência (está em "conferir")',
        'conferir' => 'Horímetro fora da sequência (menor que o anterior ou mais de 300 h acima): conferir',
    ];

    /**
     * Considera todo o histórico até $ate (o abastecimento anterior pode ser de antes do período filtrado).
     * $equipamentos: calcula só essas máquinas (null = todas).
     *
     * Devolve ['intervalos'    => [id do abastecimento => [litros, horas, data_ref, horimetro_ref, horimetro]],
     *          'conferir'      => [[id, data, maquina, litros, horimetro, horimetro_ref, data_ref, tipo], ...]
     *                             tipo 'dia': longe do horímetro do dia (dia_inicial/dia_final);
     *                             'salto': não segue o anterior (horimetro_ref); 'seguintes': os abastecimentos seguintes
     *                             não seguem este (horimetro_seg/data_seg é o seguinte; horimetro_ref pode ser nulo),
     *          'sem_intervalo' => [id => 'primeiro'|'mesmo'|'reinicio'|'conferir']  (tem horímetro, mas não fecha intervalo)]
     */
    public static function intervalos($ate = null, array $equipamentos = null)
    {
        $abastecimentos = DB::table('informacao')
                            ->leftJoin('equipamento', 'equipamento.id', 'informacao.equipamento')
                            ->whereNull('informacao.deleted_at')
                            ->where('informacao.qnt_diesel', '>', 0)
                            ->where('informacao.equipamento', '>', 0)
                            ->where(fn($q) => $q->whereNull('equipamento.placa')->orWhere('equipamento.placa', '<>', self::PLACA_TERCEIROS))
                            ->when($ate, fn($q) => $q->where('informacao.data', '<=', $ate))
                            ->when(!is_null($equipamentos), fn($q) => $q->whereIn('informacao.equipamento', $equipamentos))
                            ->orderBy('informacao.equipamento')
                            ->orderBy('informacao.data')
                            ->orderByRaw('informacao.horimetro_abastecimento IS NULL, informacao.horimetro_abastecimento, informacao.id')
                            ->get([
                                'informacao.id', 'informacao.data', 'informacao.equipamento', 'informacao.qnt_diesel',
                                'informacao.horimetro_abastecimento', 'informacao.horimetro_inicial', 'informacao.horimetro_final',
                                DB::raw("COALESCE(equipamento.equipamento, equipamento.placa) as maquina"),
                            ]);

        $intervalos = [];
        $conferir = [];      // [id => problema]
        $semIntervalo = [];
        $maquina = null;
        $valido = fn($horas) => $horas > 0 && $horas <= self::INTERVALO_MAXIMO;
        // Leitura de horímetro guardada como referência (com a anterior a ela, para explicar um problema depois)
        $leitura = fn($a, $horimetro, $anterior = null) => [
            'id' => $a->id, 'data' => $a->data, 'horimetro' => $horimetro, 'litros' => (float) $a->qnt_diesel, 'maquina' => $a->maquina,
            'data_ant' => $anterior['data'] ?? null, 'horimetro_ant' => $anterior['horimetro'] ?? null,
        ];

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
            // Confere com o horímetro do próprio dia (inicial/final do lançamento): longe dele é erro de digitação.
            // Não vira referência nem candidata, para não confundir a sequência; os litros seguem para o próximo intervalo.
            $dia = array_filter([(float) $a->horimetro_inicial, (float) $a->horimetro_final], fn($h) => $h > 0);
            if ($dia && ($horimetro < min($dia) - self::TOLERANCIA_DIA || $horimetro > max($dia) + self::TOLERANCIA_DIA)) {
                $conferir[$a->id] = [
                    'id' => $a->id, 'data' => $a->data, 'maquina' => $a->maquina, 'litros' => (float) $a->qnt_diesel,
                    'horimetro' => $horimetro, 'horimetro_ref' => $referencia['horimetro'] ?? null, 'data_ref' => $referencia['data'] ?? null,
                    'dia_inicial' => $a->horimetro_inicial, 'dia_final' => $a->horimetro_final, 'tipo' => 'dia',
                ];
                $semIntervalo[$a->id] = 'conferir';
                continue;
            }
            if (!$referencia) {
                $referencia = $leitura($a, $horimetro);
                $semIntervalo[$a->id] = 'primeiro';
                continue;
            }
            if ($horimetro == $referencia['horimetro']) {
                $semIntervalo[$a->id] = 'mesmo';
                continue; // mesmo horímetro do anterior (abasteceu sem trabalhar): os litros seguem para o próximo intervalo
            }

            $horas = $horimetro - $referencia['horimetro'];
            if ($valido($horas)) {
                // Segue a sequência: fecha o intervalo; se havia candidata, ela era só um erro de digitação
                $intervalos[$a->id] = [
                    'litros' => $litros, 'horas' => $horas, 'data_ref' => $referencia['data'],
                    'horimetro_ref' => $referencia['horimetro'], 'horimetro' => $horimetro,
                ];
                $nova = $leitura($a, $horimetro, $referencia);
            } elseif ($candidata && $valido($horimetro - $candidata['horimetro'])) {
                // Segue a candidata: ela estava certa e quem estava fora da sequência era a referência antiga.
                // A candidata sai da conferência (recomeça a sequência) e a referência antiga entra no lugar dela.
                $intervalos[$a->id] = [
                    'litros' => $litrosCandidata, 'horas' => $horimetro - $candidata['horimetro'], 'data_ref' => $candidata['data'],
                    'horimetro_ref' => $candidata['horimetro'], 'horimetro' => $horimetro,
                ];
                unset($conferir[$candidata['id']]);
                $semIntervalo[$candidata['id']] = 'reinicio';
                unset($intervalos[$referencia['id']]);
                $semIntervalo[$referencia['id']] = 'conferir';
                $conferir[$referencia['id']] = [
                    'id' => $referencia['id'], 'data' => $referencia['data'], 'maquina' => $referencia['maquina'], 'litros' => $referencia['litros'],
                    'horimetro' => $referencia['horimetro'], 'horimetro_ref' => $referencia['horimetro_ant'], 'data_ref' => $referencia['data_ant'],
                    'horimetro_seg' => $candidata['horimetro'], 'data_seg' => $candidata['data'], 'tipo' => 'seguintes',
                ];
                $nova = $leitura($a, $horimetro, $candidata);
            } else {
                // Fora da sequência: vai para a conferência e fica como candidata; a referência continua a mesma
                $conferir[$a->id] = [
                    'id' => $a->id, 'data' => $a->data, 'maquina' => $a->maquina, 'litros' => (float) $a->qnt_diesel,
                    'horimetro' => $horimetro, 'horimetro_ref' => $referencia['horimetro'], 'data_ref' => $referencia['data'], 'tipo' => 'salto',
                ];
                $semIntervalo[$a->id] = 'conferir';
                $candidata = $leitura($a, $horimetro, $referencia);
                $litrosCandidata = 0;
                continue;
            }
            $referencia = $nova;
            $litros = 0;
            $candidata = null;
            $litrosCandidata = 0;
        }

        return ['intervalos' => $intervalos, 'conferir' => array_values($conferir), 'sem_intervalo' => $semIntervalo];
    }
}
