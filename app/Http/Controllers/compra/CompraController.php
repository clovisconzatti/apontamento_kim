<?php

namespace App\Http\Controllers\compra;

use App\Http\Controllers\Controller;
use App\Models\apontamento;
use App\Models\equipamento;
use Illuminate\Http\Request;

class CompraController extends Controller
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
            $filtros[]=['apontamento.data','>=',$filtroDtInicial];
            $filtros[]=['apontamento.data','<=',$filtroDtFinal];
        }


        $apontamentos = apontamento::leftJoin('equipamento','equipamento.id','apontamento.equipamento')
                                    ->where($filtros)
                                    ->orderBy('data', 'DESC')
                                    ->get();
        return view('compra.listAll' , compact('apontamentos','filtroDtInicial','filtroDtFinal','filtroPlaca'));
    }

    public function formAdd()
    {
        $placas = equipamento::orderby('placa')->get();
        return view('compra.add',compact('placas'));
    }
    public function strore(Request $request)
    {
        try{
            $apontamento = new apontamento([
                "id"                => $request->id
                ,"data"             => $request->data
                , "equipamento"    => $request->equipamento
                , "litros"          => $request->litros
                , "km"              => $request->km
                , "horas"           => $request->horas
                , "combustivel"     => $request->combustivel
                , "obs"             => $request->obs
            ]);
            $apontamento->save();
        }catch(\Exception $e){
            return response()->json($apontamento);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $apontamento = apontamento::where('id','=',$id)->first();

        return view('compra.edit' , compact('apontamento'));
    }

    public function edit($id, Request $request)
    {
        try{
            $apontamento = apontamento::find($id);
            $apontamento->data                  = $request->data;
            $apontamento->equipamento		    = $request->equipamento;
            $apontamento->litros                = $request->litros;
            $apontamento->km                    = $request->km;
            $apontamento->horas                 = $request->horas;
            $apontamento->combustivel           = $request->combustivel;
            $apontamento->obs                   = $request->obs;
            $apontamento->save();
        }catch(\Exception $e){
            return response()->json($apontamento);
        }
        return response()->json('success');
    }

    public function checaKm(Request $request){
        $km = apontamento::where('equipamento',$request->equipamento)
                          ->orderBy('data','desc')
                          ->orderBy('id','desc')
                          ->first();
        return response()->json($km);
    }

    public function checaHora(Request $request){
        $horas = apontamento::where('equipamento',$request->equipamento)
                          ->orderBy('data','desc')
                          ->orderBy('id','desc')
                          ->first();
        return response()->json($horas);
    }
}
