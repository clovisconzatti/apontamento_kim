<?php

namespace App\Http\Controllers\transferencia;

use App\Http\Controllers\Controller;
use App\Models\transferencia;
use Illuminate\Http\Request;

class transferenciaController extends Controller
{

    public function listAll(Request $request ){

        $transferencias = transferencia::orderBy('id', 'ASC')->get();
        return view('transferencia.listAll' , compact('transferencias'));
    }

    public function formAdd()
    {
        return view('transferencia.add');
    }
    public function strore(Request $request)
    {
        try{
            $transferencia = new transferencia([
                "id"            => $request->id
                ,"data"         => $request->data
                ,"combustivel"  => $request->combustivel
                ,"origem"       => $request->origem
                ,"destino"      => $request->destino
                ,"litros"       => $request->litros
                ,"fornecedor"   => $request->fornecedor
                ,"nr_doc"       => $request->nr_doc
            ]);
            $transferencia->save();
        }catch(\Exception $e){
            return response()->json($transferencia);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $transferencia = transferencia::where('id','=',$id)->first();

        return view('transferencia.edit' , compact('transferencia'));
    }

    public function edit($id, Request $request)
    {
        try{
            $transferencia = transferencia::find($id);
            $transferencia->data            = $request->data;
            $transferencia->combustivel     = $request->combustivel;
            $transferencia->origem          = $request->origem;
            $transferencia->destino		    = $request->destino;
            $transferencia->litros		    = $request->litros;
            $transferencia->fornecedor	    = $request->fornecedor;
            $transferencia->nr_doc		    = $request->nr_doc;
            $transferencia->save();
        }catch(\Exception $e){
            return response()->json($transferencia);
        }
        return response()->json('success');
    }
}
