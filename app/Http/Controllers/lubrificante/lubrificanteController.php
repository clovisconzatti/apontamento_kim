<?php

namespace App\Http\Controllers\lubrificante;

use App\Http\Controllers\Controller;
use App\Models\lubrificante;
use Illuminate\Http\Request;

class lubrificanteController extends Controller
{

    public function listAll(Request $request ){

        $lubrificantes = lubrificante::orderBy('lubrificante', 'ASC')->get();
        return view('lubrificante.listAll' , compact('lubrificantes'));
    }

    public function formAdd()
    {
        return view('lubrificante.add');
    }
    public function strore(Request $request)
    {
        try{
            $lubrificante = new lubrificante([
                "id"            => $request->id
                ,"lubrificante"        => $request->lubrificante
            ]);
            $lubrificante->save();
        }catch(\Exception $e){
            return response()->json($lubrificante);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $lubrificante = lubrificante::where('id','=',$id)->first();

        return view('lubrificante.edit' , compact('lubrificante'));
    }

    public function edit($id, Request $request)
    {
        try{
            $lubrificante = lubrificante::find($id);
            $lubrificante->lubrificante		    = $request->lubrificante;
            $lubrificante->save();
        }catch(\Exception $e){
            return response()->json($lubrificante);
        }
        return response()->json('success');
    }
}
