<?php

namespace App\Http\Controllers\manutencao;

use App\Http\Controllers\Controller;
use App\Models\colaborador;
use App\Models\equipamento;
use App\Models\fazenda;
use App\Models\manutencao;
use App\Models\situacao_manutencao;
use App\Models\tipo_manutencao;
use Illuminate\Http\Request;

class manutencaoController extends Controller
{
    public function listAll(Request $request ){

        $manutencoes = manutencao::leftJoin('fazenda','fazenda.id','manutencao.fazenda')
                                    ->leftJoin('equipamento','equipamento.id','manutencao.equipamento')
                                    ->leftJoin('colaborador','colaborador.id','manutencao.operador')
                                    ->leftJoin('tipo_manutencao','tipo_manutencao.id','manutencao.tipo_manutencao')
                                    ->leftJoin('situacao_manutencao','situacao_manutencao.id','manutencao.situacao')
                                    ->orderBy('manutencao.id', 'ASC')
                                    ->get([
                                        'manutencao.id'
                                        ,'manutencao.ord_servico'
                                        ,'manutencao.data'
                                        ,'fazenda.fazenda'
                                        , 'equipamento.equipamento'
                                        , 'colaborador.colaborador'
                                        , 'manutencao.hora_inicial'
                                        , 'manutencao.hora_final'
                                        , 'manutencao.horimetro'
                                        , 'tipo_manutencao.tipo'
                                        , 'manutencao.custo'
                                        , 'manutencao.manutencao_diaria'
                                        , 'situacao_manutencao.situacao'
                                        , 'manutencao.obs'
                                    ]);
        return view('manutencao.listAll' , compact('manutencoes'));
    }

    public function formAdd()
    {
        $colaboradores          = colaborador::orderby('colaborador')->get();
        $fazendas               = fazenda::orderby      ('fazenda')->get();
        $equipamentos           = equipamento::orderby('equipamento')->get();
        $tipos_manutencao       = tipo_manutencao::orderby('tipo')->get();
        $situacoes_manutencao   = situacao_manutencao::orderby('situacao')->get();
        return view('manutencao.add',compact('colaboradores','fazendas','equipamentos','tipos_manutencao','situacoes_manutencao'));
    }
    public function store(Request $request)
    {
        try{
            $manutencao = new manutencao([
                "data"                          => $request->data
                , "ord_servico"                 => $request->ord_servico
                , "fazenda"                     => $request->fazenda
                , "equipamento"                 => $request->equipamento
                , "operador"                    => $request->operador
                , "hora_inicial"                => $request->hora_inicial
                , "hora_final"                  => $request->hora_final
                , "horimetro"                   => $request->horimetro
                , "tipo_manutencao"             => $request->tipo_manutencao
                , "custo"                       => $request->custo
                , "manutencao_diaria"           => $request->manutencao_diaria
                , "situacao"                    => $request->situacao
                , "obs"                         => $request->obs
            ]);
            $manutencao->save();
        }catch(\Exception $e){
            return response()->json($manutencao);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $manutencao = manutencao::where('id','=',$id)->first();

        return view('manutencao.edit' , compact('manutencao'));
    }

    public function edit($id, Request $request)
    {
        try{
            $manutencao = manutencao::find($id);
            $manutencao->data		                = $request->data;
            $manutencao->ord_servico		        = $request->ord_servico;
            $manutencao->fazenda		            = $request->fazenda;
            $manutencao->equipamento		        = $request->equipamento;
            $manutencao->operador		            = $request->operador;
            $manutencao->hora_inicial		        = $request->hora_inicial;
            $manutencao->hora_final		            = $request->hora_final;
            $manutencao->horimetro		            = $request->horimetro;
            $manutencao->tipo_manutencao		    = $request->tipo_manutencao;
            $manutencao->custo			            = $request->custo;
            $manutencao->manutencao_diaria		    = $request->manutencao_diaria;
            $manutencao->situacao		            = $request->situacao;
            $manutencao->obs		                = $request->obs;

            $manutencao->save();
        }catch(\Exception $e){
            return response()->json($manutencao);
        }
        return response()->json('success');
    }
}
