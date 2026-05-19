<?php

namespace App\Http\Controllers\informacao;

use App\Http\Controllers\Controller;
use App\Models\atividade;
use App\Models\clima;
use App\Models\colaborador;
use App\Models\comboio;
use App\Models\corte;
use App\Models\equipamento;
use App\Models\fazenda;
use App\Models\informacao;
use App\Models\lubrificante;
use App\Models\terreno;
use Illuminate\Http\Request;

class informacaoController extends Controller
{
    public function listAll(Request $request ){

        $informacoes = informacao::leftJoin('fazenda','fazenda.id','informacao.fazenda')
                                    ->leftJoin('equipamento','equipamento.id','informacao.equipamento')
                                    ->leftJoin('atividade','atividade.id','informacao.atividade')
                                    ->leftJoin('colaborador','colaborador.id','informacao.colaborador')
                                    ->leftJoin('corte','corte.id','informacao.corte')
                                    ->leftJoin('comboio','comboio.id','informacao.origem_abastecimento')
                                    ->leftJoin('lubrificante','lubrificante.id','informacao.tipo_lubrificante')
                                    ->leftJoin('clima','clima.id','informacao.clima')
                                    ->leftJoin('terreno','terreno.id','informacao.terreno')
                                    ->orderBy('informacao.data','DESC','informacao.id', 'DESC')
                                    ->get([
                                        'informacao.id'
                                        ,'informacao.data'
                                        ,'fazenda.fazenda'
                                        , 'equipamento.equipamento'
                                        , 'atividade.atividade'
                                        , 'colaborador.colaborador'
                                        , 'informacao.hora_inicial'
                                        , 'informacao.hora_final'
                                        , 'informacao.horimetro_inicial'
                                        , 'informacao.horimetro_final'
                                        , 'informacao.corte'
                                        , 'informacao.fat_m'
                                        , 'comboio.tanque'
                                        , 'informacao.nr_nf'
                                        , 'informacao.qnt_diesel'
                                        , 'informacao.horimetro_abastecimento'
                                        , 'informacao.relogio_tanque_inicial'
                                        , 'informacao.relogio_tanque_final'
                                        , 'informacao.qnt_lubrificante'
                                        , 'lubrificante.lubrificante'
                                        , 'informacao.producao_terceiros'
                                        , 'informacao.comprimento_madeira'
                                        , 'informacao.baldeio_curto'
                                        , 'informacao.baldeio_medio'
                                        , 'informacao.baldeio_longo'
                                        , 'informacao.arrasto_curto'
                                        , 'informacao.arrasto_medio'
                                        , 'informacao.arrasto_longo'
                                        , 'clima.clima'
                                        , 'terreno.terreno'
                                        , 'informacao.obs'
                                        , 'informacao.carg_julieta'
                                        , 'informacao.carg_truck'
                                        , 'informacao.carg_bitrem'
                                        ]);
        return view('informacao.listAll' , compact('informacoes'));
    }

    public function formAdd()
    {
        $colaboradores  = colaborador::orderby('colaborador')->get();
        $fazendas       = fazenda::orderby      ('fazenda')->get();
        $atividades     = atividade::orderby('atividade')->get();
        $equipamentos   = equipamento::orderby('equipamento')->get();
        $cortes         = corte::orderby('corte')->get();
        $comboios       = comboio::orderby('tanque')->get();
        $lubrificantes  = lubrificante::orderby('lubrificante')->get();
        $terrenos       = terreno::orderby('terreno')->get();
        $climas         = clima::orderby('clima')->get();
        return view('informacao.add',compact('colaboradores','fazendas','atividades',
        'equipamentos','cortes','comboios','lubrificantes','terrenos','climas'));
    }
    public function store(Request $request)
    {
        try{
            $informacao = new informacao([
                "data"                          => $request->data
                , "fazenda"                     => $request->fazenda
                , "equipamento"                 => $request->equipamento
                , "atividade"                   => $request->atividade
                , "colaborador"                 => $request->colaborador
                , "hora_inicial"                => $request->hora_inicial
                , "hora_final"                  => $request->hora_final
                , "horimetro_inicial"           => $request->horimetro_inicial
                , "horimetro_final"             => $request->horimetro_final
                , "corte"                       => $request->corte
                , "fat_m"                       => $request->fat_m
                , "origem_abastecimento"        => $request->origem_abastecimento
                , "nr_nf"                       => $request->nr_nf
                , "qnt_diesel"                  => $request->qnt_diesel
                , "horimetro_abastecimento"     => $request->horimetro_abastecimento
                , "relogio_tanque_inicial"      => $request->relogio_tanque_inicial
                , "relogio_tanque_final"        => $request->relogio_tanque_final
                , "qnt_lubrificante"            => $request->qnt_lubrificante
                , "tipo_lubrificante"           => $request->tipo_lubrificante
                , "producao_terceiros"          => $request->producao_terceiros
                , "comprimento_madeira"         => $request->comprimento_madeira
                , "baldeio_curto"               => $request->baldeio_curto
                , "baldeio_medio"               => $request->baldeio_medio
                , "baldeio_longo"               => $request->baldeio_longo
                , "arrasto_curto"               => $request->arrasto_curto
                , "arrasto_medio"               => $request->arrasto_medio
                , "arrasto_longo"               => $request->arrasto_longo
                , "clima"                       => $request->clima
                , "terreno"                     => $request->terreno
                , "obs"                         => $request->obs
                , "carg_julieta"                => $request->carg_julieta
                , "carg_truck"                  => $request->carg_truck
                , "carg_bitrem"                 => $request->carg_bitrem
            ]);
            $informacao->save();
        }catch(\Exception $e){
            return response()->json($e);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {

 $informacao     = informacao::findOrFail($id);
    $colaboradores  = colaborador::orderby('colaborador')->get();
    $fazendas       = fazenda::orderby('fazenda')->get();
    $atividades     = atividade::orderby('atividade')->get();
    $equipamentos   = equipamento::orderby('equipamento')->get();
    $cortes         = corte::orderby('corte')->get();
    $comboios       = comboio::orderby('tanque')->get();
    $lubrificantes  = lubrificante::orderby('lubrificante')->get();
    $terrenos       = terreno::orderby('terreno')->get();
    $climas         = clima::orderby('clima')->get();

    return view('informacao.edit', compact(
        'informacao',
        'colaboradores',
        'fazendas',
        'atividades',
        'equipamentos',
        'cortes',
        'comboios',
        'lubrificantes',
        'terrenos',
        'climas'
    ));
}



    public function edit($id, Request $request)
    {
        try{
            $informacao = informacao::find($id);
            $informacao->data		                = $request->data;
            $informacao->fazenda		            = $request->fazenda;
            $informacao->equipamento		        = $request->equipamento;
            $informacao->atividade		            = $request->atividade;
            $informacao->colaborador		        = $request->colaborador;
            $informacao->hora_inicial		        = $request->hora_inicial;
            $informacao->hora_final		            = $request->hora_final;
            $informacao->horimetro_inicial		    = $request->horimetro_inicial;
            $informacao->horimetro_final		    = $request->horimetro_final;
            $informacao->corte		                = $request->corte;
            $informacao->fat_m		                = $request->fat_m;
            $informacao->origem_abastecimento		= $request->origem_abastecimento;
            $informacao->nr_nf		                = $request->nr_nf;
            $informacao->qnt_diesel		            = $request->qnt_diesel;
            $informacao->horimetro_abastecimento	= $request->horimetro_abastecimento;
            $informacao->relogio_tanque_inicial		= $request->relogio_tanque_inicial;
            $informacao->relogio_tanque_final		= $request->relogio_tanque_final;
            $informacao->qnt_lubrificante		    = $request->qnt_lubrificante;
            $informacao->tipo_lubrificante		    = $request->tipo_lubrificante;
            $informacao->producao_terceiros		    = $request->producao_terceiros;
            $informacao->comprimento_madeira		= $request->comprimento_madeira;
            $informacao->baldeio_curto		        = $request->baldeio_curto;
            $informacao->baldeio_medio		        = $request->baldeio_medio;
            $informacao->baldeio_longo		        = $request->baldeio_longo;
            $informacao->arrasto_curto		        = $request->arrasto_curto;
            $informacao->arrasto_medio		        = $request->arrasto_medio;
            $informacao->arrasto_longo		        = $request->arrasto_longo;
            $informacao->clima		                = $request->clima;
            $informacao->terreno		            = $request->terreno;
            $informacao->obs		                = $request->obs;
            $informacao->carg_julieta		        = $request->carg_julieta;
            $informacao->carg_truck		            = $request->carg_truck;
            $informacao->carg_bitrem		        = $request->carg_bitrem;
            $informacao->save();
        }catch(\Exception $e){
            return response()->json($informacao);
        }
        return response()->json('success');
    }
}
