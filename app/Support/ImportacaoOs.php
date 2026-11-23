<?php

namespace App\Support;

use App\Models\lubrificante;
use App\Models\peca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Importação da Ordem de Serviço em PDF (Manutenção e Revisão). O PDF é lido no navegador (public/js/os-pdf.js);
 * aqui os dados da OS são ligados ao cadastro: equipamento pela placa e peças pelo código.
 */
class ImportacaoOs
{
    // "001007" e "1007" são o mesmo código
    public static function chaveCodigo($codigo)
    {
        $codigo = mb_strtoupper(trim((string) $codigo));
        return ltrim($codigo, '0') ?: $codigo;
    }

    // [código normalizado => id] das peças do cadastro (código repetido: vale o cadastro mais antigo)
    public static function pecasPorCodigo()
    {
        $mapa = [];
        foreach (DB::table('peca')->whereNull('deleted_at')->where('cod_cargo', '<>', '')->orderBy('id')->get(['id', 'cod_cargo']) as $p) {
            $mapa += [self::chaveCodigo($p->cod_cargo) => $p->id];
        }
        return $mapa;
    }

    // Id da peça com o código; se não existir, cadastra
    public static function pecaPorCodigo($codigo, $descricao)
    {
        $id = self::pecasPorCodigo()[self::chaveCodigo($codigo)] ?? null;
        if (!$id) {
            $id = peca::create(['peca' => mb_substr($descricao, 0, 50), 'cod_cargo' => $codigo])->id;
        }
        return $id;
    }

    /**
     * Separa o óleo das peças. Para cada produto da OS ([['codigo', 'descricao'], ...]) devolve
     * ['codigo', 'id' (peça do cadastro ou null), 'lubrificante' => null (é peça) | 0 (óleo a escolher) | id do lubrificante].
     * Vale primeiro o que já foi escolhido antes para o código; sem escolha, a descrição.
     */
    public static function classificar(array $produtos)
    {
        $pecas = self::pecasPorCodigo();
        $memoria = Schema::hasTable('lubrificante_os') ? DB::table('lubrificante_os')->pluck('lubrificante', 'codigo')->all() : [];
        $lubrificantes = DB::table('lubrificante')->whereNull('deleted_at')->pluck('lubrificante', 'id')->all();

        return array_map(function ($p) use ($pecas, $memoria, $lubrificantes) {
            $chave = self::chaveCodigo($p['codigo']);
            if (array_key_exists($chave, $memoria)) {
                // Lubrificante lembrado que saiu do cadastro: escolher de novo
                $lubrificante = $memoria[$chave] === null ? null : (isset($lubrificantes[$memoria[$chave]]) ? (int) $memoria[$chave] : 0);
            } else {
                $lubrificante = self::pareceLubrificante($p['descricao']) ? self::mesmaViscosidade($p['descricao'], $lubrificantes) : null;
            }
            return ['codigo' => $p['codigo'], 'id' => $pecas[$chave] ?? null, 'lubrificante' => $lubrificante];
        }, $produtos);
    }

    // Óleo, graxa, fluido ou aditivo pela descrição (filtro de óleo, bomba de óleo etc. são peças)
    public static function pareceLubrificante($descricao)
    {
        $d = Str::upper(Str::ascii((string) $descricao));
        if (preg_match('/FILTRO|BOMBA|TAMPA|RETENTOR|SENSOR|MANG|VARETA|CARTER|BUJAO|BOIA|ANEL|VEDA|JUNTA|RESERVATORIO|ENGRAX|BICO/', $d)) {
            return false;
        }
        return (bool) preg_match('/^(OLEO|GRAXA|LUBRIF|FLUIDO|ADITIVO|LIQ\w*\.? *(DE +)?ARREF)|\b(0|5|10|15|20|25)W[- ]?(20|30|40|50|60)\b/', $d);
    }

    // Lubrificante do cadastro com a mesma viscosidade (ex.: 15W40), se for um só; senão 0 (a escolher)
    private static function mesmaViscosidade($descricao, array $lubrificantes)
    {
        $grau = fn($t) => preg_match('/\b(0|5|10|15|20|25)W[- ]?(20|30|40|50|60)\b/', Str::upper((string) $t), $m) ? $m[1].'W'.$m[2] : null;
        $procurado = $grau($descricao);
        $iguais = $procurado ? array_keys(array_filter($lubrificantes, fn($l) => $grau($l) === $procurado)) : [];
        return count($iguais) === 1 ? (int) $iguais[0] : 0;
    }

    // Id do lubrificante com este nome; se não existir, cadastra
    public static function lubrificantePorNome($nome)
    {
        $nome = mb_substr(trim((string) $nome), 0, 50);
        return DB::table('lubrificante')->whereNull('deleted_at')->where('lubrificante', $nome)->value('id')
            ?: lubrificante::create(['lubrificante' => $nome])->id;
    }

    // Guarda a escolha para o código ($lubrificante null = é peça)
    public static function lembrar($codigo, $lubrificante)
    {
        if (trim((string) $codigo) === '') {
            return;
        }
        DB::table('lubrificante_os')->updateOrInsert(['codigo' => self::chaveCodigo($codigo)], ['lubrificante' => $lubrificante, 'updated_at' => now()]);
    }

    // Equipamento pela placa ou nome; com cadastro repetido, o ativo com mais lançamentos na Informação Diária
    public static function equipamento($veiculo)
    {
        $veiculo = trim((string) $veiculo);
        if ($veiculo === '') {
            return null;
        }
        $achados = DB::table('equipamento')->whereNull('deleted_at')
                        ->where(fn($q) => $q->where('placa', $veiculo)->orWhere('equipamento', $veiculo))
                        ->get(['id', 'ativo']);
        return optional($achados->sortByDesc(fn($e) => [
            $e->ativo !== 'Nao' ? 1 : 0,
            DB::table('informacao')->whereNull('deleted_at')->where('equipamento', $e->id)->count(),
        ])->first())->id;
    }

    /**
     * O odômetro da OS pode vir sem a vírgula (49729 = 4.972,9). Compara com o horímetro mais recente do equipamento
     * até a data e devolve o valor (ou o valor ÷ 10) mais próximo dele; sem referência, o valor como está.
     */
    public static function horimetro($odometro, $equipamento, $data)
    {
        $odometro = (float) $odometro;
        if ($odometro <= 0) {
            return null;
        }
        $referencia = $equipamento ? DB::table('informacao')->whereNull('deleted_at')->where('equipamento', $equipamento)
                        ->when($data, fn($q) => $q->where('data', '<=', $data))
                        ->whereRaw('COALESCE(horimetro_final, horimetro_inicial) > 0')
                        ->orderByDesc('data')->orderByDesc('id')
                        ->value(DB::raw('COALESCE(horimetro_final, horimetro_inicial)')) : null;
        if (!$referencia) {
            return $odometro;
        }
        return abs($odometro / 10 - $referencia) < abs($odometro - $referencia) ? $odometro / 10 : $odometro;
    }

    // Onde a OS já foi lançada: ['Manutenção de 16/11/2026', ...] ($tela/$id = lançamento aberto, que não conta)
    public static function jaLancada($numero, $tela, $id)
    {
        $numero = ltrim(trim((string) $numero), '0');
        if ($numero === '') {
            return [];
        }
        $onde = [];
        foreach (['manutencao' => ['ord_servico', 'Manutenção'], 'revisao' => ['nr_os', 'Revisão']] as $tabela => [$coluna, $nome]) {
            $datas = DB::table($tabela)->whereNull('deleted_at')
                        ->whereRaw("TRIM(LEADING '0' FROM $coluna) = ?", [$numero])
                        ->when($tabela === $tela && $id, fn($q) => $q->where('id', '<>', $id))
                        ->pluck('data');
            foreach ($datas as $data) {
                $onde[] = $nome.' de '.date('d/m/Y', strtotime($data));
            }
        }
        return $onde;
    }
}
