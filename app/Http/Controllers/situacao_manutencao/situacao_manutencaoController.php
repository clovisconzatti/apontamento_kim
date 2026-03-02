<?php

namespace App\Http\Controllers\situacao_manutencao;

use App\Http\Controllers\Controller;
use App\Models\situacao_manutencao;
use Illuminate\Http\Request;

class situacao_manutencaoController extends Controller
{

    public function listAll(Request $request ){

        $situacao_manutencoes = situacao_manutencao::orderBy('situacao', 'ASC')->get();
        return view('situacao_manutencao.listAll' , compact('situacao_manutencoes'));
    }

    public function formAdd()
    {
        return view('situacao_manutencao.add');
    }
    public function strore(Request $request)
    {
        try{
            $situacao_manutencao = new situacao_manutencao([
                "id"            => $request->id
                ,"situacao"     => $request->situacao
            ]);
            $situacao_manutencao->save();
        }catch(\Exception $e){
            return response()->json($situacao_manutencao);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $situacao_manutencoes = situacao_manutencao::where('id','=',$id)->first();

        return view('situacao_manutencao.edit' , compact('situacao_manutencoes'));
    }

    public function edit($id, Request $request)
    {
        try{
            $situacao_manutencao = situacao_manutencao::find($id);
            $situacao_manutencao->situacao	  = $request->situacao;
            $situacao_manutencao->save();
        }catch(\Exception $e){
            return response()->json($situacao_manutencao);
        }
        return response()->json('success');
    }
}
