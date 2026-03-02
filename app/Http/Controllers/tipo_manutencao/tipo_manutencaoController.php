<?php

namespace App\Http\Controllers\tipo_manutencao;

use App\Http\Controllers\Controller;
use App\Models\tipo_manutencao;
use Illuminate\Http\Request;

class tipo_manutencaoController extends Controller
{

    public function listAll(Request $request ){

        $tipo_manutencoes = tipo_manutencao::orderBy('tipo', 'ASC')->get();
        return view('tipo_manutencao.listAll' , compact('tipo_manutencoes'));
    }

    public function formAdd()
    {
        return view('tipo_manutencao.add');
    }
    public function strore(Request $request)
    {
        try{
            $tipo_manutencao = new tipo_manutencao([
                "id"                       => $request->id
                ,"tipo"         => $request->tipo
            ]);
            $tipo_manutencao->save();
        }catch(\Exception $e){
            return response()->json($tipo_manutencao);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $tipo_manutencoes = tipo_manutencao::where('id','=',$id)->first();

        return view('tipo_manutencao.edit' , compact('tipo_manutencoes'));
    }

    public function edit($id, Request $request)
    {
        try{
            $tipo_manutencao = tipo_manutencao::find($id);
            $tipo_manutencao->tipo		    = $request->tipo;
            $tipo_manutencao->save();
        }catch(\Exception $e){
            return response()->json($tipo_manutencao);
        }
        return response()->json('success');
    }
}
