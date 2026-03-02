<?php

namespace App\Http\Controllers\equipamento;

use App\Http\Controllers\Controller;
use App\Models\atividade;
use App\Models\equipamento;
use App\Models\operacao;
use App\Models\tipo;
use Illuminate\Http\Request;

class EquipamentoController extends Controller
{
    public function listAll(Request $request ){

        $equipamentos = equipamento::leftJoin('tipo','tipo.id','equipamento.tipo')
                                    ->leftJoin('atividade','atividade.id','equipamento.atividade')
                                    ->leftJoin('operacao','operacao.id','equipamento.operacao')
                                    ->orderBy('equipamento', 'ASC')
                                    ->get([
                                        'equipamento.id'
                                        ,'equipamento.placa'
                                        ,'equipamento.equipamento'
                                        ,'equipamento.ano'
                                        ,'equipamento.ativo'
                                        ,'equipamento.uf'
                                        ,'equipamento.data_partida'
                                        ,'tipo.tipo'
                                        ,'atividade.atividade'
                                        ,'equipamento.cilindros'
                                        ,'operacao.operacao'
                                        ,'equipamento.consumo_minimo'
                                        ,'equipamento.consumo_maximo'
                                    ]);
        return view('equipamento.listAll' , compact('equipamentos'));
    }

    public function formAdd()
    {
        $tipos = tipo::orderby('tipo')->get();
        $atividades = atividade::orderby('atividade')->get();
        $operacoes = operacao::orderby('operacao')->get();
        return view('equipamento.add' ,compact('tipos','atividades','operacoes'));
    }
    public function strore(Request $request)
    {
        try{
            $equipamento = new equipamento([
                "id"                => $request->id
                ,"placa"            => $request->placa
                ,"equipamento"      => $request->equipamento
                ,"ano"              => $request->ano
                ,"ativo"            => $request->ativo
                ,"uf"               => $request->uf
                ,"data_partida"     => $request->data_partida
                ,"tipo"             => $request->tipo
                ,"atividade"        => $request->atividade
                ,"cilindros"        => $request->cilindros
                ,"operacao"         => $request->operacao
                ,"consumo_minimo"   => $request->consumo_minimo
                ,"consumo_maximo"   => $request->consumo_maximo
            ]);
            $equipamento->save();
        }catch(\Exception $e){
            dd($e);
            return response()->json($equipamento);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $equipamento = equipamento::where('id','=',$id)->first();
        $tipos = tipo::orderby('tipo')->get();
        $atividades = atividade::orderby('atividade')->get();
        $operacoes = operacao::orderby('operacao')->get();
        return view('equipamento.edit' , compact('equipamento','tipos','atividades','operacoes'));
    }

    public function edit($id, Request $request)
    {
        try{
            $equipamento = equipamento::find($id);
            $equipamento->placa             = $request->placa;
            $equipamento->equipamento       = $request->equipamento;
            $equipamento->ano		        = $request->ano;
            $equipamento->ativo		        = $request->ativo;
            $equipamento->uf		        = $request->uf;
            $equipamento->data_partida		= $request->data_partida;
            $equipamento->tipo		        = $request->tipo;
            $equipamento->atividade		    = $request->atividade;
            $equipamento->cilindros		    = $request->cilindros;
            $equipamento->operacao		    = $request->operacao;
            $equipamento->consumo_minimo	= $request->consumo_minimo;
            $equipamento->consumo_maximo	= $request->consumo_maximo;
            $equipamento->save();
        }catch(\Exception $e){
            return response()->json($equipamento);
        }
        return response()->json('success');
    }
}
