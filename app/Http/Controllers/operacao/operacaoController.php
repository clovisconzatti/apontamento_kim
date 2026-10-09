<?php

namespace App\Http\Controllers\operacao;

use App\Http\Controllers\Controller;
use App\Models\operacao;
use Illuminate\Http\Request;

class operacaoController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Operação'],
        ];
        $filtros = $this->lerFiltros($request, 'operacao', $camposFiltro);

        $query = operacao::orderBy('operacao', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['operacao']);
        $operacoes = $query->get();
        $totalRegistros = $operacoes->count();

        return view('operacao.listAll' , compact('operacoes', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('operacao.add');
    }
    public function strore(Request $request)
    {
        try{
            $operacao = new operacao([
                "id"            => $request->id
                ,"operacao"        => $request->operacao
            ]);
            $operacao->save();
        }catch(\Exception $e){
            return response()->json($operacao);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $operacoes = operacao::where('id','=',$id)->first();

        return view('operacao.edit' , compact('operacoes'));
    }

    public function edit($id, Request $request)
    {
        try{
            $operacao = operacao::find($id);
            $operacao->operacao		    = $request->operacao;
            $operacao->save();
        }catch(\Exception $e){
            return response()->json($operacao);
        }
        return response()->json('success');
    }
}
