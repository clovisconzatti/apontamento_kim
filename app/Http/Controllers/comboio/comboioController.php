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
        ];
        $filtros = $this->lerFiltros($request, 'comboio', $camposFiltro);

        $query = comboio::leftJoin('fazenda','fazenda.id','comboio.fazenda');
        $this->filtrarTexto($query, $filtros['busca'], ['comboio.tanque']);
        $this->filtrarIgual($query, $filtros, ['fazenda' => 'comboio.fazenda', 'uf' => 'comboio.uf']);

        $comboios = $query
                                    ->orderBy('comboio.tanque', 'ASC')
                                    ->get([
                                        'comboio.id'
                                        ,'comboio.tanque'
                                        ,'comboio.capacidade'
                                        ,'comboio.uf'
                                        ,'comboio.obs'
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
