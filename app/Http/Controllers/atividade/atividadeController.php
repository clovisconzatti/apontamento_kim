<?php

namespace App\Http\Controllers\atividade;

use App\Http\Controllers\Controller;
use App\Models\atividade;
use Illuminate\Http\Request;

class atividadeController extends Controller
{
    public function listAll(Request $request ){

        $atividades = atividade::orderBy('atividade', 'ASC')->get();
        return view('atividade.listAll' , compact('atividades'));
    }

    public function formAdd()
    {
        return view('atividade.add');
    }
    public function strore(Request $request)
    {
        try{
            $atividade = new atividade([
                "id"            => $request->id
                ,"atividade"      => $request->atividade
            ]);
            $atividade->save();
        }catch(\Exception $e){
            return response()->json($atividade);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $atividade = atividade::where('id','=',$id)->first();

        return view('atividade.edit' , compact('atividade'));
    }

    public function edit($id, Request $request)
    {
        try{
            $atividade = atividade::find($id);
            $atividade->atividade		    = $request->atividade;
            $atividade->save();
        }catch(\Exception $e){
            return response()->json($atividade);
        }
        return response()->json('success');
    }
}
