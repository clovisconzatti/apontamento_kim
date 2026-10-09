<?php

namespace App\Http\Controllers\clima;

use App\Http\Controllers\Controller;
use App\Models\clima;
use Illuminate\Http\Request;

class climaController extends Controller
{
    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Clima'],
        ];
        $filtros = $this->lerFiltros($request, 'clima', $camposFiltro);

        $query = clima::orderBy('clima', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['clima']);
        $climas = $query->get();
        $totalRegistros = $climas->count();

        return view('clima.listAll' , compact('climas', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('clima.add');
    }
    public function strore(Request $request)
    {
        try{
            $clima = new clima([
                "id"            => $request->id
                ,"clima"      => $request->clima
            ]);
            $clima->save();
        }catch(\Exception $e){
            return response()->json($clima);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $clima = clima::where('id','=',$id)->first();

        return view('clima.edit' , compact('clima'));
    }

    public function edit($id, Request $request)
    {
        try{
            $clima = clima::find($id);
            $clima->clima		    = $request->clima;
            $clima->save();
        }catch(\Exception $e){
            return response()->json($clima);
        }
        return response()->json('success');
    }
}
