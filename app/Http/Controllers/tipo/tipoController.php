<?php

namespace App\Http\Controllers\tipo;

use App\Http\Controllers\Controller;
use App\Models\tipo;
use Illuminate\Http\Request;

class tipoController extends Controller
{

    public function listAll(Request $request ){

        $tipos = tipo::orderBy('tipo', 'ASC')->get();
        return view('tipo.listAll' , compact('tipos'));
    }

    public function formAdd()
    {
        return view('tipo.add');
    }
    public function strore(Request $request)
    {
        try{
            $tipo = new tipo([
                "id"            => $request->id
                ,"tipo"        => $request->tipo
            ]);
            $tipo->save();
        }catch(\Exception $e){
            return response()->json($tipo);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $tipo = tipo::where('id','=',$id)->first();

        return view('tipo.edit' , compact('tipo'));
    }

    public function edit($id, Request $request)
    {
        try{
            $tipo = tipo::find($id);
            $tipo->tipo		    = $request->tipo;
            $tipo->save();
        }catch(\Exception $e){
            return response()->json($tipo);
        }
        return response()->json('success');
    }
}
