<?php

namespace App\Http\Controllers\comprimento_madeira;

use App\Http\Controllers\Controller;
use App\Models\comprimento_madeira;
use Illuminate\Http\Request;

class comprimento_madeiraController extends Controller
{

    public function listAll(Request $request ){

        $comprimento_madeiras = comprimento_madeira::orderBy('comprimento', 'ASC')->get();
        return view('comprimento_madeira.listAll' , compact('comprimento_madeiras'));
    }

    public function formAdd()
    {
        return view('comprimento_madeira.add');
    }
    public function strore(Request $request)
    {
        try{
            $comprimento_madeira = new comprimento_madeira([
                "id"            => $request->id
                ,"comprimento"        => $request->comprimento
            ]);
            $comprimento_madeira->save();
        }catch(\Exception $e){
            return response()->json($comprimento_madeira);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $comprimento_madeira = comprimento_madeira::where('id','=',$id)->first();

        return view('comprimento_madeira.edit' , compact('comprimento_madeira'));
    }

    public function edit($id, Request $request)
    {
        try{
            $comprimento_madeira = comprimento_madeira::find($id);
            $comprimento_madeira->comprimento		    = $request->comprimento;
            $comprimento_madeira->save();
        }catch(\Exception $e){
            return response()->json($comprimento_madeira);
        }
        return response()->json('success');
    }
}
