<?php

namespace App\Http\Controllers\tipo_combustivel;

use App\Http\Controllers\Controller;
use App\Models\tipo_combustivel;
use Illuminate\Http\Request;

class tipo_combustivelController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Combustível ou unidade'],
        ];
        $filtros = $this->lerFiltros($request, 'tipo_combustivel', $camposFiltro);

        $query = tipo_combustivel::orderBy('combustivel', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['combustivel', 'unidade']);
        $tipo_combustiveis = $query->get();
        $totalRegistros = $tipo_combustiveis->count();

        return view('tipo_combustivel.listAll' , compact('tipo_combustiveis', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('tipo_combustivel.add');
    }
    public function strore(Request $request)
    {
        try{
            $tipo_combustivel = new tipo_combustivel([
                "id"                => $request->id
                ,"combustivel"      => $request->combustivel
                ,"unidade"          => $request->unidade
            ]);
            $tipo_combustivel->save();
        }catch(\Exception $e){
            return response()->json($tipo_combustivel);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $tipo_combustivel = tipo_combustivel::where('id','=',$id)->first();

        return view('tipo_combustivel.edit' , compact('tipo_combustivel'));
    }

    public function edit($id, Request $request)
    {
        try{
            $tipo_combustivel = tipo_combustivel::find($id);
            $tipo_combustivel->combustivel	    = $request->combustivel;
            $tipo_combustivel->unidade		    = $request->unidade;
            $tipo_combustivel->save();
        }catch(\Exception $e){
            return response()->json($tipo_combustivel);
        }
        return response()->json('success');
    }
}
