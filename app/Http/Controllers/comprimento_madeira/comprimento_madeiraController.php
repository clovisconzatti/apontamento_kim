<?php

namespace App\Http\Controllers\comprimento_madeira;

use App\Http\Controllers\Controller;
use App\Models\comprimento_madeira;
use Illuminate\Http\Request;

class comprimento_madeiraController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Comprimento'],
        ];
        $filtros = $this->lerFiltros($request, 'comprimento_madeira', $camposFiltro);

        $query = comprimento_madeira::orderBy('comprimento', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['comprimento']);
        $comprimento_madeiras = $query->get();
        $totalRegistros = $comprimento_madeiras->count();

        return view('comprimento_madeira.listAll' , compact('comprimento_madeiras', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('comprimento_madeira.add');
    }
    public function strore(Request $request)
    {
        try{
            $comprimento_madeira = new comprimento_madeira([
                "id"            => $request->id
                ,"comprimento"        => $request->comprimento
            ]);
            $comprimento_madeira->save();
        }catch(\Exception $e){
            return response()->json($comprimento_madeira);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $comprimento_madeira = comprimento_madeira::where('id','=',$id)->first();

        return view('comprimento_madeira.edit' , compact('comprimento_madeira'));
    }

    public function edit($id, Request $request)
    {
        try{
            $comprimento_madeira = comprimento_madeira::find($id);
            $comprimento_madeira->comprimento		    = $request->comprimento;
            $comprimento_madeira->save();
        }catch(\Exception $e){
            return response()->json($comprimento_madeira);
        }
        return response()->json('success');
    }
}
