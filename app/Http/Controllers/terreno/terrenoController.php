<?php

namespace App\Http\Controllers\terreno;

use App\Http\Controllers\Controller;
use App\Models\terreno;
use Illuminate\Http\Request;

class terrenoController extends Controller
{
    public function listAll(Request $request ){

        $terrenos = terreno::orderBy('terreno', 'ASC')->get();
        return view('terreno.listAll' , compact('terrenos'));
    }

    public function formAdd()
    {
        return view('terreno.add');
    }
    public function strore(Request $request)
    {
        try{
            $terreno = new terreno([
                "id"            => $request->id
                ,"terreno"      => $request->terreno
            ]);
            $terreno->save();
        }catch(\Exception $e){
            return response()->json($terreno);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $terreno = terreno::where('id','=',$id)->first();

        return view('terreno.edit' , compact('terreno'));
    }

    public function edit($id, Request $request)
    {
        try{
            $terreno = terreno::find($id);
            $terreno->terreno		    = $request->terreno;
            $terreno->save();
        }catch(\Exception $e){
            return response()->json($terreno);
        }
        return response()->json('success');
    }
}
