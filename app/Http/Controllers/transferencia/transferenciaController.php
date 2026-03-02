<?php

namespace App\Http\Controllers\transferencia;

use App\Http\Controllers\Controller;
use App\Models\transferencia;
use App\Models\comboio;
use App\Models\tipo_combustivel;
use App\Models\colaborador;
use App\Models\equipamento;
use App\Models\fazenda;
use Illuminate\Http\Request;

class transferenciaController extends Controller
{

    public function listAll(Request $request ){

        $transferencias = transferencia::leftJoin('colaborador','colaborador.id','transferencia.operador')
                                    ->leftJoin('comboio as origem_comboio','origem_comboio.id','transferencia.origem')
                                    ->leftJoin('equipamento as destino_comboio','destino_comboio.id','transferencia.destino')
                                    ->leftJoin('comboio as tanque','tanque.id','transferencia.tanque')
                                    ->leftJoin('fazenda','fazenda.id','transferencia.fazenda')
                                    ->leftJoin('tipo_combustivel','tipo_combustivel.id','transferencia.combustivel')
                                    ->orderBy('data', 'DESC')
                                    ->get([
                                        'transferencia.id'
                                        ,'transferencia.data'
                                        ,'origem_comboio.tanque as origem'
                                        ,'destino_comboio.equipamento as destino'
                                        ,'transferencia.litros'
                                        ,'transferencia.nr_doc'
                                        ,'tipo_combustivel.combustivel'
                                        ,'transferencia.horimetro'
                                        ,'colaborador.colaborador as operador'
                                        ,'fazenda.fazenda'
                                        ,'transferencia.horimetro_inicial'
                                        ,'transferencia.horimetro_final'
                                        ,'tanque.tanque'
                                        ,'transferencia.obs'
                                    ]);
        return view('transferencia.listAll' , compact('transferencias'));
    }

    public function formAdd()
    {
        $comboios = comboio::orderby('tanque')->get();
        $tipo_combustiveis = tipo_combustivel::orderby('combustivel')->get();
        $colaboradores = colaborador::orderby('colaborador')->get();
        $fazendas = fazenda::orderby('fazenda')->get();
        $equipamentos = equipamento::orderby('equipamento')->get();
        return view('transferencia.add',compact('comboios','tipo_combustiveis','colaboradores','fazendas','equipamentos'));
    }
    public function strore(Request $request)
    {
        try{
            $transferencia = new transferencia([
                "id"                    => $request->id
                ,"data"                 => $request->data
                ,"origem"               => $request->origem
                ,"destino"              => $request->destino
                ,"litros"               => $request->litros
                ,"fornecedor"           => $request->fornecedor
                ,"nr_doc"               => $request->nr_doc
                ,"combustivel"          => $request->combustivel
                ,"horimetro"            => $request->horimetro
                ,"operador"             => $request->operador
                ,"fazenda"              => $request->fazenda
                ,"horimetro_inicial"    => $request->horimetro_inicial
                ,"horimetro_final"      => $request->horimetro_final
                ,"tanque"               => $request->tanque
                ,"obs"                  => $request->obs
            ]);
            $transferencia->save();
        }catch(\Exception $e){
            return response()->json($transferencia);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $transferencia = transferencia::where('id','=',$id)->first();
        $comboios = comboio::orderby('tanque')->get();
        $tipo_combustiveis = tipo_combustivel::orderby('combustivel')->get();
        $colaboradores = colaborador::orderby('colaborador')->get();
        $fazendas = fazenda::orderby('fazenda')->get();
        $equiapmentos = equipamento::orderby('equipamento')->get();
        return view('transferencia.edit' , compact('transferencia','comboios','tipo_combustiveis','colaboradores','fazendas','equipamentos'));
    }

    public function edit($id, Request $request)
    {
        try{
            $transferencia = transferencia::find($id);
            $transferencia->data                = $request->data;
            $transferencia->origem              = $request->origem;
            $transferencia->destino		        = $request->destino;
            $transferencia->litros		        = $request->litros;
            $transferencia->fornecedor	        = $request->fornecedor;
            $transferencia->nr_doc		        = $request->nr_doc;
            $transferencia->combustivel         = $request->combustivel;
            $transferencia->horimetro           = $request->horimetro;
            $transferencia->operador            = $request->operador;
            $transferencia->fazenda             = $request->fazenda;
            $transferencia->horimetro_inicial   = $request->horimetro_inicial;
            $transferencia->horimetro_final     = $request->horimetro_final;
            $transferencia->tanque              = $request->tanque;
            $transferencia->obs                 = $request->obs;
            $transferencia->save();
        }catch(\Exception $e){
            return response()->json($transferencia);
        }
        return response()->json('success');
    }
}
