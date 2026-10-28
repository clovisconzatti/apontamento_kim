<?php

namespace App\Http\Controllers\comboio;

use App\Http\Controllers\Controller;
use App\Models\comboio;
use App\Models\fazenda;
use Illuminate\Http\Request;

class comboioController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca'   => ['label' => 'Tanque', 'tipo' => 'texto', 'col' => 4],
            'fazenda' => ['label' => 'Fazenda', 'tipo' => 'select', 'col' => 4, 'opcoes' => $this->opcoesTabela('fazenda', 'fazenda')],
            'uf'      => ['label' => 'UF', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesDistintas('comboio', 'uf')],
            'ativo'   => ['label' => 'Ativo', 'tipo' => 'select', 'col' => 2, 'opcoes' => ['Sim' => 'Sim', 'Nao' => 'Não']],
            'tipo'    => ['label' => 'Tipo', 'tipo' => 'select', 'col' => 2, 'opcoes' => ['proprio' => 'Tanque próprio', 'posto' => 'Posto externo']],
        ];
        $filtros = $this->lerFiltros($request, 'comboio', $camposFiltro);

        $query = comboio::leftJoin('fazenda','fazenda.id','comboio.fazenda');
        $this->filtrarTexto($query, $filtros['busca'], ['comboio.tanque']);
        $this->filtrarIgual($query, $filtros, ['fazenda' => 'comboio.fazenda', 'uf' => 'comboio.uf', 'ativo' => 'comboio.ativo', 'tipo' => 'comboio.tipo']);

        $comboios = $query
                                    ->orderBy('comboio.tanque', 'ASC')
                                    ->get([
                                        'comboio.id'
                                        ,'comboio.tanque'
                                        ,'comboio.capacidade'
                                        ,'comboio.uf'
                                        ,'comboio.obs'
                                        ,'comboio.ativo'
                                        ,'comboio.tipo'
                                        ,'comboio.saldo_inicial'
                                        ,'comboio.data_saldo_inicial'
                                        ,'fazenda.fazenda'
                                    ]);
        $totalRegistros = $comboios->count();

        return view('comboio.listAll' , compact('comboios', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        $fazendas = fazenda::orderby('fazenda')->get();
        return view('comboio.add',compact('fazendas'));
    }
    public function strore(Request $request)
    {
        $this->validarSaldoInicial($request);
        try{
            $comboio = new comboio([
                "id"                => $request->id
                ,"tanque"           => $request->tanque
                ,"capacidade"       => $request->capacidade
                ,"fazenda"          => $request->fazenda
                ,"uf"               => $request->uf
                ,"obs"              => $request->obs
                ,"ativo"            => $request->ativo === 'Nao' ? 'Nao' : 'Sim'
                ,"tipo"             => $request->tipo === 'posto' ? 'posto' : 'proprio'
                ,"saldo_inicial"    => is_numeric($request->saldo_inicial) ? (float) $request->saldo_inicial : null
                ,"data_saldo_inicial" => $request->data_saldo_inicial ?: null
            ]);
            $comboio->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
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
        $this->validarSaldoInicial($request);
        try{
            $comboio = comboio::find($id);
            $comboio->tanque		    = $request->tanque;
            $comboio->capacidade		= $request->capacidade;
            $comboio->fazenda		    = $request->fazenda;
            $comboio->uf		        = $request->uf;
            $comboio->obs		        = $request->obs;
            $comboio->ativo		    = $request->ativo === 'Nao' ? 'Nao' : 'Sim';
            $comboio->tipo		    = $request->tipo === 'posto' ? 'posto' : 'proprio';
            $comboio->saldo_inicial	    = is_numeric($request->saldo_inicial) ? (float) $request->saldo_inicial : null;
            $comboio->data_saldo_inicial = $request->data_saldo_inicial ?: null;
            $comboio->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    // Saldo inicial e a data da medição andam juntos (o saldo do tanque parte deles)
    private function validarSaldoInicial(Request $request)
    {
        $request->validate([
            'saldo_inicial'      => 'nullable|numeric|required_with:data_saldo_inicial',
            'data_saldo_inicial' => 'nullable|date|required_with:saldo_inicial',
        ], [
            'saldo_inicial.numeric'            => 'O saldo inicial deve ser um número (litros).',
            'saldo_inicial.required_with'      => 'Informe o saldo inicial (litros) medido na data.',
            'data_saldo_inicial.required_with' => 'Informe a data em que o saldo inicial foi medido.',
        ]);
    }
}
