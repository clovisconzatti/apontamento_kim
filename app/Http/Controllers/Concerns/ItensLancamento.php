<?php

namespace App\Http\Controllers\Concerns;

use App\Models\lubrificante;
use App\Models\peca;
use App\Support\ImportacaoOs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Linhas de itens com quantidade lançadas junto com um apontamento (peças, lubrificantes) e serviços executados.
 *
 * A view usa 'partials.itens-quadro' / 'partials.servicos-quadro' e o custom.js envia as linhas como
 * [grupo][n][id], [grupo][n][quantidade] e os outros campos da linha (ver coletaItens()).
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

    // Ignora as linhas vazias e exige item do cadastro + quantidade maior que zero ($comQuantidade = false: só o item).
    // Os erros vão para $erros (para ValidationException); devolve [['id' => , 'quantidade' => ], ...]
    protected function lerItens($linhas, string $tabela, string $nome, string $campo, array &$erros, bool $comQuantidade = true): array
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
            } elseif (!$comQuantidade) {
                $itens[] = ['id' => (int) $id, 'quantidade' => null];
            } elseif (!is_numeric($quantidade) || (float) $quantidade <= 0) {
                $erros[$campo.'.'.$linha.'.quantidade'] = $nome.' da linha '.$linha.': informe uma quantidade maior que zero.';
            } else {
                $itens[] = ['id' => (int) $id, 'quantidade' => (float) $quantidade];
            }
        }
        return $itens;
    }

    // Peças com valores (Manutenção e Revisão). Peça importada da OS que ainda não está no cadastro vem com id "novo",
    // codigo e descricao: só é cadastrada ao gravar (gravarPecas).
    protected function lerPecasComValor($linhas, array &$erros): array
    {
        $itens = [];
        $linha = 0;
        foreach ((array) $linhas as $dados) {
            $linha++;
            if (trim((string) ($dados['id'] ?? '')) === 'novo') {
                $codigo = trim((string) ($dados['codigo'] ?? ''));
                $descricao = trim((string) ($dados['descricao'] ?? ''));
                if ($codigo === '' || $descricao === '' || mb_strlen($codigo) > 20) {
                    $erros['pecas.'.$linha.'.id'] = 'Peça da linha '.$linha.': código ou descrição da OS inválidos.';
                    continue;
                }
                $quantidade = $dados['quantidade'] ?? '';
                if (!is_numeric($quantidade) || (float) $quantidade <= 0) {
                    $erros['pecas.'.$linha.'.quantidade'] = 'Peça da linha '.$linha.': informe uma quantidade maior que zero.';
                    continue;
                }
                $item = ['id' => null, 'codigo' => $codigo, 'descricao' => $descricao, 'quantidade' => (float) $quantidade];
            } else {
                $errosLinha = [];
                $lidos = $this->lerItens([$dados], 'peca', 'Peça', 'pecas', $errosLinha);
                foreach ($errosLinha as $chave => $mensagem) {
                    $erros[str_replace('pecas.1.', 'pecas.'.$linha.'.', $chave)] = str_replace('da linha 1:', 'da linha '.$linha.':', $mensagem);
                }
                if (!$lidos) {
                    continue;
                }
                $item = $lidos[0];
            }
            $itens[] = $item + $this->lerValores($dados, ['valor_unitario', 'desconto', 'valor_total'], 'Peça da linha '.$linha, 'pecas.'.$linha, $erros);
        }
        return $itens;
    }

    // Serviços executados: linhas sem nada são ignoradas; o serviço é obrigatório nas outras
    protected function lerServicos($linhas, array &$erros): array
    {
        $itens = [];
        $linha = 0;
        foreach ((array) $linhas as $dados) {
            $linha++;
            $dados = array_map(fn($v) => trim((string) $v), (array) $dados);
            unset($dados['id'], $dados['quantidade']);
            if (implode('', $dados) === '') {
                continue;
            }
            $nome = 'Serviço da linha '.$linha;
            if (($dados['servico'] ?? '') === '') {
                $erros['servicos.'.$linha.'.servico'] = $nome.': informe o serviço.';
                continue;
            }
            $data = $dados['data'] ?? '';
            if ($data !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                $erros['servicos.'.$linha.'.data'] = $nome.': data inválida.';
            }
            foreach (['hora_inicial', 'hora_final'] as $hora) {
                if (($dados[$hora] ?? '') !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $dados[$hora])) {
                    $erros['servicos.'.$linha.'.'.$hora] = $nome.': hora inválida.';
                }
            }
            $itens[] = [
                'servico'      => mb_substr($dados['servico'], 0, 150),
                'mecanico'     => mb_substr($dados['mecanico'] ?? '', 0, 100) ?: null,
                'data'         => $data ?: null,
                'hora_inicial' => ($dados['hora_inicial'] ?? '') ?: null,
                'hora_final'   => ($dados['hora_final'] ?? '') ?: null,
            ] + $this->lerValores($dados, ['valor_hora', 'valor_total'], $nome, 'servicos.'.$linha, $erros);
        }
        return $itens;
    }

    private function lerValores(array $dados, array $campos, string $nome, string $chave, array &$erros): array
    {
        $valores = [];
        foreach ($campos as $campo) {
            $v = trim((string) ($dados[$campo] ?? ''));
            if ($v !== '' && (!is_numeric($v) || (float) $v < 0)) {
                $erros[$chave.'.'.$campo] = $nome.': valor inválido.';
            }
            $valores[$campo] = $v === '' ? null : (float) $v;
        }
        return $valores;
    }

    // As tabelas são MyISAM (sem rollback): grava as linhas novas e só depois apaga as antigas, para um erro não perder o lançamento
    protected function trocarLinhas($relacao, array $linhas)
    {
        $antigas = (clone $relacao)->pluck('id')->all();
        foreach ($linhas as $linha) {
            $relacao->create($linha);
        }
        if ($antigas) {
            $modelo = $relacao->getRelated();
            $modelo->newQuery()->whereIn('id', $antigas)->delete();
        }
    }

    // Serviços já gravados (antes da migration valores_itens_os a tabela não existe)
    protected function linhasServicos($lancamento)
    {
        return Schema::hasTable($lancamento->getTable().'_servico') ? $lancamento->servicos : [];
    }

    // Sem a migration valores_itens_os não dá para gravar: avisa antes de mexer no lançamento
    protected function exigirTabelasOs(string $tabela)
    {
        if (!Schema::hasTable($tabela.'_servico') || !Schema::hasColumn($tabela.'_peca', 'valor_total')) {
            throw ValidationException::withMessages(['pecas' => 'Falta atualizar o banco de dados (php artisan migrate): nada foi gravado.']);
        }
    }

    // Peças e serviços da Manutenção/Revisão (cadastra as peças novas da OS)
    protected function gravarPecasServicos($lancamento, array $pecas, array $servicos)
    {
        $this->trocarLinhas($lancamento->pecas(), array_map(fn($p) => [
            'peca'           => $p['id'] ?? ImportacaoOs::pecaPorCodigo($p['codigo'], $p['descricao']),
            'quantidade'     => $p['quantidade'],
            'valor_unitario' => $p['valor_unitario'],
            'desconto'       => $p['desconto'],
            'valor_total'    => $p['valor_total'],
        ], $pecas));
        $this->trocarLinhas($lancamento->servicos(), $servicos);
    }

    // Linhas já gravadas no formato do partial: $relacao = $modelo->pecas, $coluna = 'peca'
    protected function linhasItens($relacao, string $coluna): array
    {
        return collect($relacao)->map(fn($i) => [
            'id'             => $i->$coluna,
            'quantidade'     => $i->quantidade,
            'valor_unitario' => $i->valor_unitario,
            'desconto'       => $i->desconto,
            'valor_total'    => $i->valor_total,
        ])->all();
    }
}
