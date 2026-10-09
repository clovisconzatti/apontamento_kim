<?php

namespace App\Http\Controllers\corte;

use App\Http\Controllers\Controller;
use App\Models\corte;
use Illuminate\Http\Request;

class corteController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Corte'],
        ];
        $filtros = $this->lerFiltros($request, 'corte', $camposFiltro);

        $query = corte::orderBy('corte', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['corte']);
        $cortes = $query->get();
        $totalRegistros = $cortes->count();

        return view('corte.listAll' , compact('cortes', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('corte.add');
    }
    public function strore(Request $request)
    {
        try{
            $corte = new corte([
                "id"            => $request->id
                ,"corte"        => $request->corte
            ]);
            $corte->save();
        }catch(\Exception $e){
            return response()->json($corte);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $corte = corte::where('id','=',$id)->first();

        return view('corte.edit' , compact('corte'));
    }

    public function edit($id, Request $request)
    {
        try{
            $corte = corte::find($id);
            $corte->corte		    = $request->corte;
            $corte->save();
        }catch(\Exception $e){
            return response()->json($corte);
        }
        return response()->json('success');
    }
}
