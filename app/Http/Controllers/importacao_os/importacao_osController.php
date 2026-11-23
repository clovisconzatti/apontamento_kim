<?php

namespace App\Http\Controllers\importacao_os;

use App\Http\Controllers\Controller;
use App\Support\ImportacaoOs;
use Illuminate\Http\Request;

// Liga os dados lidos do PDF da OS ao cadastro. Só consulta: nada é gravado até o usuário salvar o formulário.
class importacao_osController extends Controller
{
    public function conferir(Request $request)
    {
        $request->validate([
            'numero'    => 'nullable|string|max:30',
            'veiculo'   => 'nullable|string|max:60',
            'odometro'  => 'nullable|numeric',
            'data'      => 'nullable|date_format:Y-m-d',
            'produtos'             => 'nullable|array|max:500',
            'produtos.*.codigo'    => 'required|string|max:30',
            'produtos.*.descricao' => 'required|string|max:255',
            'tela'      => 'required|in:manutencao,revisao',
            'id'        => 'nullable|integer',
        ]);

        $equipamento = ImportacaoOs::equipamento($request->veiculo);

        return response()->json([
            'equipamento' => $equipamento,
            'horimetro'   => ImportacaoOs::horimetro($request->odometro, $equipamento, $request->data),
            'pecas'       => ImportacaoOs::classificar((array) $request->produtos),
            'ja_lancada'  => ImportacaoOs::jaLancada($request->numero, $request->tela, $request->id),
        ]);
    }
}
