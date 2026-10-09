<?php

namespace App\Http\Controllers\tipo;

use App\Http\Controllers\Controller;
use App\Models\tipo;
use Illuminate\Http\Request;

class tipoController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Tipo de veículo'],
        ];
        $filtros = $this->lerFiltros($request, 'tipo', $camposFiltro);

        $query = tipo::orderBy('tipo', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['tipo']);
        $tipos = $query->get();
        $totalRegistros = $tipos->count();

        return view('tipo.listAll' , compact('tipos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('tipo.add');
    }
    public function strore(Request $request)
    {
        try{
            $tipo = new tipo([
                "id"            => $request->id
                ,"tipo"        => $request->tipo
            ]);
            $tipo->save();
        }catch(\Exception $e){
            return response()->json($tipo);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $tipo = tipo::where('id','=',$id)->first();

        return view('tipo.edit' , compact('tipo'));
    }

    public function edit($id, Request $request)
    {
        try{
            $tipo = tipo::find($id);
            $tipo->tipo		    = $request->tipo;
            $tipo->save();
        }catch(\Exception $e){
            return response()->json($tipo);
        }
        return response()->json('success');
    }
}
