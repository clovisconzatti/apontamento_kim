<?php

namespace App\Http\Controllers\comboio;

use App\Http\Controllers\Controller;
use App\Models\comboio;
use App\Models\fazenda;
use Illuminate\Http\Request;

class comboioController extends Controller
{

    public function listAll(Request $request ){

        $comboios = comboio::leftJoin('fazenda','fazenda.id','comboio.fazenda')
                                    ->orderBy('comboio.tanque', 'ASC')
                                    ->get([
                                        'comboio.id'
                                        ,'comboio.tanque'
                                        ,'comboio.capacidade'
                                        ,'comboio.uf'
                                        ,'comboio.obs'
                                        ,'fazenda.fazenda'
                                    ]);
        return view('comboio.listAll' , compact('comboios'));}

    public function formAdd()
    {
        $fazendas = fazenda::orderby('fazenda')->get();
        return view('comboio.add',compact('fazendas'));
    }
    public function strore(Request $request)
    {
        try{
            $comboio = new comboio([
                "id"                => $request->id
                ,"tanque"           => $request->tanque
                ,"capacidade"       => $request->capacidade
                ,"fazenda"          => $request->fazenda
                ,"uf"               => $request->uf
                ,"obs"              => $request->obs
            ]);
            $comboio->save();
        }catch(\Exception $e){
            return response()->json($comboio);
        }

        return response()->json('success');
    }

    public function formEdit($id)
    {
        $fazendas = fazenda::orderby('fazenda')->get();
        $comboios = comboio::where('id','=',$id)->first();

        return view('comboio.edit' , compact('comboios','fazendas'));
    }

    public function edit($id, Request $request)
    {
        try{
            $comboio = comboio::find($id);
            $comboio->tanque		    = $request->tanque;
            $comboio->capacidade		= $request->capacidade;
            $comboio->fazenda		    = $request->fazenda;
            $comboio->uf		        = $request->uf;
            $comboio->obs		        = $request->obs;
            $comboio->save();
        }catch(\Exception $e){
            return response()->json($comboio);
        }
        return response()->json('success');
    }
}
