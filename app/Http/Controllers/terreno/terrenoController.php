<?php

namespace App\Http\Controllers\terreno;

use App\Http\Controllers\Controller;
use App\Models\terreno;
use Illuminate\Http\Request;

class terrenoController extends Controller
{
    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Terreno'],
        ];
        $filtros = $this->lerFiltros($request, 'terreno', $camposFiltro);

        $query = terreno::orderBy('terreno', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['terreno']);
        $terrenos = $query->get();
        $totalRegistros = $terrenos->count();

        return view('terreno.listAll' , compact('terrenos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('terreno.add');
    }
    public function strore(Request $request)
    {
        try{
            $terreno = new terreno([
                "id"            => $request->id
                ,"terreno"      => $request->terreno
            ]);
            $terreno->save();
        }catch(\Exception $e){
            return response()->json($terreno);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $terreno = terreno::where('id','=',$id)->first();

        return view('terreno.edit' , compact('terreno'));
    }

    public function edit($id, Request $request)
    {
        try{
            $terreno = terreno::find($id);
            $terreno->terreno		    = $request->terreno;
            $terreno->save();
        }catch(\Exception $e){
            return response()->json($terreno);
        }
        return response()->json('success');
    }
}
