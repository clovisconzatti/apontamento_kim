<?php

namespace App\Http\Controllers\colaborador;

use App\Http\Controllers\Controller;
use App\Models\colaborador;
use Illuminate\Http\Request;

class colaboradorController extends Controller
{

    public function listAll(Request $request ){

        $colaboradores = colaborador::orderBy('colaborador', 'ASC')->get();
        return view('colaborador.listAll' , compact('colaboradores'));
    }

    public function formAdd()
    {
        return view('colaborador.add');
    }
    public function strore(Request $request)
    {
        try{
            $colaborador = new colaborador([
                "id"            => $request->id
                ,"colaborador"      => $request->colaborador
                ,"uf"               => $request->uf
                ,"ativo"            => $request->ativo
                ,"cod"              => $request->cod
                ,"empresa"          => $request->empresa
            ]);
            $colaborador->save();
        }catch(\Exception $e){
            return response()->json($colaborador);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $colaborador = colaborador::where('id','=',$id)->first();

        return view('colaborador.edit' , compact('colaborador'));
    }

    public function edit($id, Request $request)
    {
        try{
            $colaborador = colaborador::find($id);
            $colaborador->colaborador		    = $request->colaborador;
            $colaborador->uf		            = $request->uf;
            $colaborador->ativo		            = $request->ativo;
            $colaborador->cod		            = $request->cod;
            $colaborador->empresa		        = $request->empresa;
            $colaborador->save();
        }catch(\Exception $e){
            return response()->json($colaborador);
        }
        return response()->json('success');
    }
}
