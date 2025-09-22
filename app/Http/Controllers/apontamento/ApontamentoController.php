<?php

namespace App\Http\Controllers\apontamento;

use App\Http\Controllers\Controller;
use App\Models\apontamento;
use App\Models\equipamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApontamentoController extends Controller
{
    public function listAll(Request $request ){
        $filtros=[];

        $filtroDtInicial  = ($request->get('dtInicial'))? $request->get('dtInicial') : session('filtroDtInicial');
        session()->put('filtroDtInicial', $filtroDtInicial);
        $filtroDtFinal  = ($request->get('dtFinal'))? $request->get('dtFinal') : session('filtroDtFinal');
        session()->put('filtroDtFinal', $filtroDtFinal);
        $filtroPlaca  = ($request->get('nome'))? $request->get('nome') : session('filtroPlaca');
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
                                    ->get([
                                        'apontamento.id'
                                        ,'equipamento.id as id_equipamento'
                                        ,'apontamento.data'
                                        ,'equipamento.equipamento'
                                        ,'apontamento.litros'
                                        ,'apontamento.km'
                                        ,'apontamento.horas'
                                        ,'apontamento.combustivel'
                                        ,'apontamento.obs'
                                        ,'equipamento.placa'
                                        ,'apontamento.origem'
                                        ,DB::raw("(SELECT km FROM apontamento AS apto WHERE apto.equipamento = apontamento.equipamento AND apto.data <= apontamento.data AND apto.id<apontamento.id ORDER BY apto.id DESC LIMIT 1) AS ultimo_km")
                                    ]);
        return view('apontamento.listAll' , compact('apontamentos','filtroDtInicial','filtroDtFinal','filtroPlaca'));
    }

    public function formAdd()
    {
        $placas = equipamento::orderby('placa')->get();
        return view('apontamento.add',compact('placas'));
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
                , "origem"          => $request->origemAbastecimento
                , "ultimo_km"       => $request->ultimo_km
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
        $placas = equipamento::orderby('placa')->get();
        return view('apontamento.edit',compact('apontamento','placas'));

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
            $apontamento->origem                = $request->origemAbastecimento;
            $apontamento->ultimo_km             = $request->ultimo_km;
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


    public function apontamentoAnexo($apontamento){
        $abastecida=apontamento::select([
                                    'equipamento.id as id_equipamento'
                                    ,'apontamento.id'
                                    ,'equipamento.equipamento'
                                    ,'equipamento.placa'
                                    ,'apontamento.anexo'
                                ])
                                ->leftjoin('equipamento','equipamento.id','apontamento.equipamento')
                                ->find($apontamento);
        return view('apontamento.anexo',compact('abastecida'));
    }

    public function upload(Request $request){
        // dd($request);
        $nome=$request->nomeArquivo;
        $placa=$request->placa;
        $id=$request->apontamento;
        if (!$request->file('arquivo')){
            return redirect()->route('apontamento.anexo',["apontamento"=>$id]);
        }
        $extensao=$request->file('arquivo')->guessExtension();
        $nomearquivo=$nome.'_'.$id.'.'.$extensao;
        $apontamento=apontamento::find($id);
        // dd($apontamento);
        $apontamento->anexo=$nomearquivo;
        $apontamento->save();

        $request->file('arquivo')->storeAs('public/'.$placa,$nomearquivo);
        return redirect()->route('apontamento.apontamentoAnexo',["apontamento"=>$id]);

    }
}
