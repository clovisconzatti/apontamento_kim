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

        $camposFiltro = [
            'busca'     => ['label' => 'Placa ou equipamento', 'tipo' => 'texto', 'col' => 4],
            'tipo'      => ['label' => 'Tipo', 'tipo' => 'select', 'opcoes' => $this->opcoesTabela('tipo', 'tipo')],
            'atividade' => ['label' => 'Atividade', 'tipo' => 'select', 'opcoes' => $this->opcoesTabela('atividade', 'atividade')],
            'operacao'  => ['label' => 'Operação', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('operacao', 'operacao')],
            'ativo'     => ['label' => 'Ativo', 'tipo' => 'select', 'col' => 2, 'opcoes' => ['Sim' => 'Sim', 'Nao' => 'Não']],
            'uf'        => ['label' => 'UF', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesDistintas('equipamento', 'uf')],
        ];
        $filtros = $this->lerFiltros($request, 'equipamento', $camposFiltro);

        $query = equipamento::leftJoin('tipo','tipo.id','equipamento.tipo')
                                    ->leftJoin('atividade','atividade.id','equipamento.atividade')
                                    ->leftJoin('operacao','operacao.id','equipamento.operacao');
        $this->filtrarTexto($query, $filtros['busca'], ['equipamento.placa', 'equipamento.equipamento']);
        $this->filtrarIgual($query, $filtros, [
            'tipo'      => 'equipamento.tipo',
            'atividade' => 'equipamento.atividade',
            'operacao'  => 'equipamento.operacao',
            'ativo'     => 'equipamento.ativo',
            'uf'        => 'equipamento.uf',
        ]);

        $equipamentos = $query
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
        $totalRegistros = $equipamentos->count();

        return view('equipamento.listAll' , compact('equipamentos', 'camposFiltro', 'filtros', 'totalRegistros'));
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
