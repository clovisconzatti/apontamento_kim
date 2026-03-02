<?php

namespace App\Http\Controllers\peca;

use App\Http\Controllers\Controller;
use App\Models\peca;
use Illuminate\Http\Request;

class pecaController extends Controller
{

    public function listAll(Request $request ){

        $pecas = peca::orderBy('peca', 'ASC')->get();
        return view('peca.listAll' , compact('pecas'));
    }

    public function formAdd()
    {
        return view('peca.add');
    }
    public function strore(Request $request)
    {
        try{
            $peca = new peca([
                "id"            => $request->id
                ,"peca"         => $request->peca
                ,"cod_cargo"    => $request->cod_cargo
            ]);
            $peca->save();
        }catch(\Exception $e){
            return response()->json($peca);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $peca = peca::where('id','=',$id)->first();

        return view('peca.edit' , compact('peca'));
    }

    public function edit($id, Request $request)
    {
        try{
            $peca = peca::find($id);
            $peca->peca		    = $request->peca;
            $peca->cod_cargo    = $request->cod_cargo;
            $peca->save();
        }catch(\Exception $e){
            return response()->json($peca);
        }
        return response()->json('success');
    }
}
