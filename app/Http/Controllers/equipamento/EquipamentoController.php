<?php

namespace App\Http\Controllers\equipamento;

use App\Http\Controllers\Controller;
use App\Models\equipamento;
use Illuminate\Http\Request;

class EquipamentoController extends Controller
{

    public function listAll(Request $request ){

        $equipamentos = equipamento::orderBy('placa', 'ASC')->get();
        return view('equipamento.listAll' , compact('equipamentos'));
    }

    public function formAdd()
    {
        return view('equipamento.add');
    }
    public function strore(Request $request)
    {
        try{
            $equipamento = new equipamento([
                "id"            => $request->id
                ,"placa"        => $request->placa
                ,"equipamento"         => $request->equipamento
            ]);
            $equipamento->save();
        }catch(\Exception $e){
            return response()->json($equipamento);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $equipamento = equipamento::where('id','=',$id)->first();

        return view('equipamento.edit' , compact('equipamento'));
    }

    public function edit($id, Request $request)
    {
        try{
            $equipamento = equipamento::find($id);
            $equipamento->placa                 = $request->placa;
            $equipamento->equipamento		    = $request->equipamento;
            $equipamento->save();
        }catch(\Exception $e){
            return response()->json($equipamento);
        }
        return response()->json('success');
    }
}
