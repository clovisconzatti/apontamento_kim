<?php

namespace App\Http\Controllers\combustivel;

use App\Http\Controllers\Controller;
use App\Models\combustivel;
use App\Models\equipamento;
use Illuminate\Http\Request;

class combustivelController extends Controller
{
    public function listAll(Request $request ){
        $filtros=[];

        $filtroDtInicial  = ($request->get('dtInicial'))? $request->get('dtInicial') : session('filtroDtInicial');
        session()->put('filtroDtInicial', $filtroDtInicial);
        $filtroDtFinal  = ($request->get('dtFinal'))? $request->get('dtFinal') : session('filtroDtFinal');
        session()->put('filtroDtFinal', $filtroDtFinal);
        $filtroPlaca  = ($request->get('placa'))? $request->get('placa') : session('filtroPlaca');
        session()->put('filtroPlaca', $filtroPlaca);


        if($filtroPlaca){
            $filtros[]=['equipamento.placa','like','%'.$filtroPlaca.'%'];
        }

        if($filtroDtFinal){
            $filtros[]=['combustivel.data','>=',$filtroDtInicial];
            $filtros[]=['combustivel.data','<=',$filtroDtFinal];
        }


        $combustivels = combustivel::leftJoin('equipamento','equipamento.id','combustivel.equipamento')
                                    ->where($filtros)
                                    ->orderBy('data', 'DESC')
                                    ->get([
                                        'combustivel.id'
                                        ,'equipamento.id as id_equipamento'
                                        ,'combustivel.data'
                                        ,'equipamento.equipamento'
                                        ,'combustivel.litros'
                                        ,'combustivel.km'
                                        ,'combustivel.horas'
                                        ,'combustivel.combustivel'
                                        ,'combustivel.obs'
                                        ,'equipamento.placa'
                                    ]);
        return view('combustivel.listAll' , compact('combustivels','filtroDtInicial','filtroDtFinal','filtroPlaca'));
    }

    public function formAdd()
    {
        $placas = equipamento::orderby('placa')->get();
        return view('combustivel.add',compact('placas'));
    }
    public function strore(Request $request)
    {
        try{
            $combustivel = new combustivel([
                "id"                => $request->id
                ,"data"             => $request->data
                , "equipamento"    => $request->equipamento
                , "litros"          => $request->litros
                , "km"              => $request->km
                , "horas"           => $request->horas
                , "combustivel"     => $request->combustivel
                , "obs"             => $request->obs
            ]);
            $combustivel->save();
        }catch(\Exception $e){
            return response()->json($combustivel);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $combustivel = combustivel::where('id','=',$id)->first();
        $placas = equipamento::orderby('placa')->get();
        return view('combustivel.edit',compact('combustivel','placas'));

    }

    public function edit($id, Request $request)
    {
        try{
            $combustivel = combustivel::find($id);
            $combustivel->data                  = $request->data;
            $combustivel->equipamento		    = $request->equipamento;
            $combustivel->litros                = $request->litros;
            $combustivel->km                    = $request->km;
            $combustivel->horas                 = $request->horas;
            $combustivel->combustivel           = $request->combustivel;
            $combustivel->obs                   = $request->obs;
            $combustivel->save();
        }catch(\Exception $e){
            return response()->json($combustivel);
        }
        return response()->json('success');
    }

    public function checaKm(Request $request){
        $km = combustivel::where('equipamento',$request->equipamento)
                          ->orderBy('data','desc')
                          ->orderBy('id','desc')
                          ->first();
        return response()->json($km);
    }

    public function checaHora(Request $request){
        $horas = combustivel::where('equipamento',$request->equipamento)
                          ->orderBy('data','desc')
                          ->orderBy('id','desc')
                          ->first();
        return response()->json($horas);
    }
}
