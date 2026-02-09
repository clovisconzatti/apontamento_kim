<?php

namespace App\Http\Controllers\baldeio;

use App\Http\Controllers\Controller;
use App\Models\baldeio;
use Illuminate\Http\Request;

class baldeioController extends Controller
{

    public function listAll(Request $request ){

        $baldeios = baldeio::orderBy('baldeio', 'ASC')->get();
        return view('baldeio.listAll' , compact('baldeios'));
    }

    public function formAdd()
    {
        return view('baldeio.add');
    }
    public function strore(Request $request)
    {
        try{
            $baldeio = new baldeio([
                "id"            => $request->id
                ,"baldeio"      => $request->baldeio
            ]);
            $baldeio->save();
        }catch(\Exception $e){
            return response()->json($baldeio);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $baldeio = baldeio::where('id','=',$id)->first();

        return view('baldeio.edit' , compact('baldeio'));
    }

    public function edit($id, Request $request)
    {
        try{
            $baldeio = baldeio::find($id);
            $baldeio->baldeio		    = $request->baldeio;
            $baldeio->save();
        }catch(\Exception $e){
            return response()->json($baldeio);
        }
        return response()->json('success');
    }
}
