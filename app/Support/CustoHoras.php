<?php

namespace App\Support;

/**
 * Custo das horas trabalhadas (Revisão e Manutenção): horas entre a hora inicial e a final × custo hora (R$/h).
 * Se a hora final for menor que a inicial, o trabalho virou o dia (soma 24h). A mesma conta é feita no navegador
 * (partials/custo-horas) só como prévia.
 */
class CustoHoras
{
    // Horas trabalhadas (ex.: 08:15 -> 11:45 = 3,5); null sem as duas horas
    public static function horas($inicial, $final)
    {
        if (!$inicial || !$final) {
            return null;
        }
        $minutos = self::minutos($final) - self::minutos($inicial);
        if ($minutos < 0) {
            $minutos += 24 * 60;
        }
        return $minutos / 60;
    }

    // Horas trabalhadas × custo hora; null sem horas ou sem custo hora
    public static function custo($inicial, $final, $custoHora)
    {
        $horas = self::horas($inicial, $final);
        if ($horas === null || $custoHora === null || $custoHora === '') {
            return null;
        }
        return round($horas * (float) $custoHora, 2);
    }

    // 3,5 -> "3h30"
    public static function formatar($horas)
    {
        $minutos = (int) round($horas * 60);
        return intdiv($minutos, 60).'h'.str_pad($minutos % 60, 2, '0', STR_PAD_LEFT);
    }

    private static function minutos($hora)
    {
        [$h, $m] = array_map('intval', explode(':', substr((string) $hora, 0, 5)) + [0, 0]);
        return $h * 60 + $m;
    }
}
