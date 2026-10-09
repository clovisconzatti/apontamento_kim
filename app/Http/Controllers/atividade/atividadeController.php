<?php

namespace App\Http\Controllers\atividade;

use App\Http\Controllers\Controller;
use App\Models\atividade;
use Illuminate\Http\Request;

class atividadeController extends Controller
{
    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Atividade'],
        ];
        $filtros = $this->lerFiltros($request, 'atividade', $camposFiltro);

        $query = atividade::orderBy('atividade', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['atividade']);
        $atividades = $query->get();
        $totalRegistros = $atividades->count();

        return view('atividade.listAll' , compact('atividades', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('atividade.add');
    }
    public function strore(Request $request)
    {
        try{
            $atividade = new atividade([
                "id"            => $request->id
                ,"atividade"      => $request->atividade
            ]);
            $atividade->save();
        }catch(\Exception $e){
            return response()->json($atividade);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $atividade = atividade::where('id','=',$id)->first();

        return view('atividade.edit' , compact('atividade'));
    }

    public function edit($id, Request $request)
    {
        try{
            $atividade = atividade::find($id);
            $atividade->atividade		    = $request->atividade;
            $atividade->save();
        }catch(\Exception $e){
            return response()->json($atividade);
        }
        return response()->json('success');
    }
}
