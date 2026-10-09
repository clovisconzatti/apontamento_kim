<?php

namespace App\Http\Controllers\lubrificante;

use App\Http\Controllers\Controller;
use App\Models\lubrificante;
use Illuminate\Http\Request;

class lubrificanteController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Lubrificante'],
        ];
        $filtros = $this->lerFiltros($request, 'lubrificante', $camposFiltro);

        $query = lubrificante::orderBy('lubrificante', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['lubrificante']);
        $lubrificantes = $query->get();
        $totalRegistros = $lubrificantes->count();

        return view('lubrificante.listAll' , compact('lubrificantes', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('lubrificante.add');
    }
    public function strore(Request $request)
    {
        try{
            $lubrificante = new lubrificante([
                "id"            => $request->id
                ,"lubrificante"        => $request->lubrificante
            ]);
            $lubrificante->save();
        }catch(\Exception $e){
            return response()->json($lubrificante);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $lubrificante = lubrificante::where('id','=',$id)->first();

        return view('lubrificante.edit' , compact('lubrificante'));
    }

    public function edit($id, Request $request)
    {
        try{
            $lubrificante = lubrificante::find($id);
            $lubrificante->lubrificante		    = $request->lubrificante;
            $lubrificante->save();
        }catch(\Exception $e){
            return response()->json($lubrificante);
        }
        return response()->json('success');
    }
}
