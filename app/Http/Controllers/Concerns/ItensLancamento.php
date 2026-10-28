<?php

namespace App\Http\Controllers\Concerns;

use App\Models\lubrificante;
use App\Models\peca;
use Illuminate\Support\Facades\DB;

/**
 * Linhas de itens com quantidade lançadas junto com um apontamento (peças, lubrificantes).
 *
 * A view usa 'partials.itens-quadro' e o custom.js envia as linhas como
 * [grupo][n][id] e [grupo][n][quantidade] (ver coletaItens()).
 */
trait ItensLancamento
{
    // Opções do select de peças: "Peça (código cargo)"
    protected function opcoesPecas(): array
    {
        return peca::orderBy('peca')->get()
                    ->mapWithKeys(fn($p) => [$p->id => $p->cod_cargo ? $p->peca.' ('.$p->cod_cargo.')' : $p->peca])
                    ->all();
    }

    // Opções do select de lubrificantes
    protected function opcoesLubrificantes(): array
    {
        return lubrificante::orderBy('lubrificante')->pluck('lubrificante', 'id')->all();
    }

    // Ignora as linhas vazias e exige item do cadastro + quantidade maior que zero.
    // Os erros vão para $erros (para ValidationException); devolve [['id' => , 'quantidade' => ], ...]
    protected function lerItens($linhas, string $tabela, string $nome, string $campo, array &$erros): array
    {
        $itens = [];
        $linha = 0;
        foreach ((array) $linhas as $dados) {
            $linha++;
            $id = trim((string) ($dados['id'] ?? ''));
            $quantidade = trim((string) ($dados['quantidade'] ?? ''));
            if ($id === '' && $quantidade === '') {
                continue;
            }
            if ($id === '' || !DB::table($tabela)->where('id', $id)->whereNull('deleted_at')->exists()) {
                $erros[$campo.'.'.$linha.'.id'] = $nome.' da linha '.$linha.': selecione um item do cadastro.';
            } elseif (!is_numeric($quantidade) || (float) $quantidade <= 0) {
                $erros[$campo.'.'.$linha.'.quantidade'] = $nome.' da linha '.$linha.': informe uma quantidade maior que zero.';
            } else {
                $itens[] = ['id' => (int) $id, 'quantidade' => (float) $quantidade];
            }
        }
        return $itens;
    }

    // Linhas já gravadas no formato do partial: $relacao = $modelo->pecas, $coluna = 'peca'
    protected function linhasItens($relacao, string $coluna): array
    {
        return collect($relacao)->map(fn($i) => ['id' => $i->$coluna, 'quantidade' => $i->quantidade])->all();
    }
}
