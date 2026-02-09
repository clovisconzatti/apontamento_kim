<?php

namespace App\Http\Controllers\fazenda;

use App\Http\Controllers\Controller;
use App\Models\colaborador;
use App\Models\fazenda;
use Illuminate\Http\Request;

class fazendaController extends Controller
{
    public function listAll(Request $request ){

        $fazendas = fazenda::leftJoin('colaborador','colaborador.id','fazenda.apontador')
                                    ->orderBy('fazenda', 'ASC')
                                    ->get([
                                        'fazenda.id'
                                        ,'fazenda.fazenda'
                                        ,'fazenda.uf'
                                        ,'fazenda.apontador'
                                        ,'fazenda.ativa'
                                        ,'colaborador.colaborador'
                                    ]);
        return view('fazenda.listAll' , compact('fazendas'));
    }

    public function formAdd()
    {
        $colaboradores = colaborador::orderby('colaborador')->get();
        return view('fazenda.add',compact('colaboradores'));
    }
    public function strore(Request $request)
    {
        try{
            $fazenda = new fazenda([
                "id"                => $request->id
                , "fazenda"         => $request->fazenda
                , "uf"              => $request->uf
                , "apontador"       => $request->apontador
                , "ativa"           => $request->ativa
            ]);
            $fazenda->save();
        }catch(\Exception $e){
            return response()->json($fazenda);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $fazenda = fazenda::where('id','=',$id)->first();
        $colaboradores = colaborador::orderby('colaborador')->get();
        return view('fazenda.edit',compact('fazenda','colaboradores'));

    }

    public function edit($id, Request $request)
    {
        try{
            $fazenda = fazenda::find($id);
            $fazenda->fazenda                  = $request->fazenda;
            $fazenda->uf		               = $request->uf;
            $fazenda->apontador                = $request->apontador;
            $fazenda->ativa                    = $request->ativa;
            $fazenda->save();
        }catch(\Exception $e){
            return response()->json($fazenda);
        }
        return response()->json('success');
    }


}
