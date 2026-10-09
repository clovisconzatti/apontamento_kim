<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Filtros de consulta padronizados para as telas de listagem.
 *
 * O controller define os campos do filtro ($campos) e a view inclui
 * 'partials.filtros', que monta o painel automaticamente.
 *
 * Tipos de campo: texto, data, select (com 'opcoes' => [valor => texto]).
 */
trait FiltrosListagem
{
    // Lê os filtros do formulário (?filtrar=1) ou da sessão; ?limpar=1 apaga todos os campos.
    // O $padrao só vale enquanto o usuário não filtrou nem limpou nada na sessão.
    protected function lerFiltros(Request $request, string $tela, array $campos, array $padrao = []): array
    {
        $chave = 'filtros.'.$tela;

        if ($request->has('limpar')) {
            $filtros = array_fill_keys(array_keys($campos), null);
            session()->put($chave, $filtros);
        } elseif ($request->has('filtrar')) {
            $filtros = [];
            foreach (array_keys($campos) as $campo) {
                $valor = trim((string) $request->get($campo, ''));
                $filtros[$campo] = $valor !== '' ? $valor : null;
            }
            session()->put($chave, $filtros);
        } else {
            $filtros = session($chave);
        }

        if (!is_array($filtros)) {
            $filtros = array_merge(array_fill_keys(array_keys($campos), null), $padrao);
        }
        // Garante todas as chaves (caso um campo novo seja incluído depois)
        $filtros = array_merge(array_fill_keys(array_keys($campos), null), $filtros);

        // Período invertido: corrige a ordem
        if (!empty($filtros['dtInicial']) && !empty($filtros['dtFinal']) && $filtros['dtInicial'] > $filtros['dtFinal']) {
            [$filtros['dtInicial'], $filtros['dtFinal']] = [$filtros['dtFinal'], $filtros['dtInicial']];
        }

        return $filtros;
    }

    // Busca textual em várias colunas (qualquer uma que contenha o termo)
    protected function filtrarTexto($query, $termo, array $colunas)
    {
        if ($termo === null || $termo === '') {
            return $query;
        }
        return $query->where(function ($q) use ($termo, $colunas) {
            foreach ($colunas as $coluna) {
                $q->orWhere($coluna, 'like', '%'.$termo.'%');
            }
        });
    }

    // Período (data inicial / data final) sobre a coluna de data informada
    protected function filtrarPeriodo($query, array $filtros, string $coluna)
    {
        if (!empty($filtros['dtInicial'])) {
            $query->where($coluna, '>=', $filtros['dtInicial']);
        }
        if (!empty($filtros['dtFinal'])) {
            $query->where($coluna, '<=', $filtros['dtFinal']);
        }
        return $query;
    }

    // Filtros de igualdade: ['campo do filtro' => 'tabela.coluna']
    protected function filtrarIgual($query, array $filtros, array $mapa)
    {
        foreach ($mapa as $campo => $coluna) {
            if (isset($filtros[$campo]) && $filtros[$campo] !== null && $filtros[$campo] !== '') {
                $query->where($coluna, $filtros[$campo]);
            }
        }
        return $query;
    }

    // Opções de um select a partir de uma tabela de cadastro: [id => descrição]
    protected function opcoesTabela(string $tabela, string $texto, string $valor = 'id'): array
    {
        return DB::table($tabela)
                    ->whereNull('deleted_at')
                    ->whereNotNull($texto)
                    ->orderBy($texto)
                    ->pluck($texto, $valor)
                    ->all();
    }

    // Opções de um select com os valores já usados numa coluna: [valor => valor]
    protected function opcoesDistintas(string $tabela, string $coluna): array
    {
        $valores = DB::table($tabela)
                    ->whereNull('deleted_at')
                    ->whereNotNull($coluna)
                    ->where($coluna, '<>', '')
                    ->distinct()
                    ->orderBy($coluna)
                    ->pluck($coluna)
                    ->all();
        return array_combine($valores, $valores) ?: [];
    }
}
