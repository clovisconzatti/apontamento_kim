<?php

namespace App\Http\Controllers\arrasto;

use App\Http\Controllers\Controller;
use App\Models\arrasto;
use Illuminate\Http\Request;

class arrastoController extends Controller
{

    public function listAll(Request $request ){

        $arrastos = arrasto::orderBy('arrasto', 'ASC')->get();
        return view('arrasto.listAll' , compact('arrastos'));
    }

    public function formAdd()
    {
        return view('arrasto.add');
    }
    public function strore(Request $request)
    {
        try{
            $arrasto = new arrasto([
                "id"            => $request->id
                ,"arrasto"      => $request->arrasto
            ]);
            $arrasto->save();
        }catch(\Exception $e){
            return response()->json($arrasto);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $arrasto = arrasto::where('id','=',$id)->first();

        return view('arrasto.edit' , compact('arrasto'));
    }

    public function edit($id, Request $request)
    {
        try{
            $arrasto = arrasto::find($id);
            $arrasto->arrasto		    = $request->arrasto;
            $arrasto->save();
        }catch(\Exception $e){
            return response()->json($arrasto);
        }
        return response()->json('success');
    }
}
