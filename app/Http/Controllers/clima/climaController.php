<?php

namespace App\Http\Controllers\clima;

use App\Http\Controllers\Controller;
use App\Models\clima;
use Illuminate\Http\Request;

class climaController extends Controller
{
    public function listAll(Request $request ){

        $climas = clima::orderBy('clima', 'ASC')->get();
        return view('clima.listAll' , compact('climas'));
    }

    public function formAdd()
    {
        return view('clima.add');
    }
    public function strore(Request $request)
    {
        try{
            $clima = new clima([
                "id"            => $request->id
                ,"clima"      => $request->clima
            ]);
            $clima->save();
        }catch(\Exception $e){
            return response()->json($clima);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $clima = clima::where('id','=',$id)->first();

        return view('clima.edit' , compact('clima'));
    }

    public function edit($id, Request $request)
    {
        try{
            $clima = clima::find($id);
            $clima->clima		    = $request->clima;
            $clima->save();
        }catch(\Exception $e){
            return response()->json($clima);
        }
        return response()->json('success');
    }
}
