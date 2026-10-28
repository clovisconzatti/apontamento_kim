<?php

namespace App\Http\Controllers\tipo_revisao;

use App\Http\Controllers\Controller;
use App\Models\tipo_revisao;
use Illuminate\Http\Request;

class tipo_revisaoController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Tipo de revisão'],
        ];
        $filtros = $this->lerFiltros($request, 'tipo_revisao', $camposFiltro);

        $query = tipo_revisao::orderBy('descricao', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['descricao', 'obs']);
        $tipos_revisao = $query->get();
        $totalRegistros = $tipos_revisao->count();

        return view('tipo_revisao.listAll' , compact('tipos_revisao', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('tipo_revisao.add');
    }

    public function store(Request $request)
    {
        $this->validar($request);
        try{
            $tipo_revisao = new tipo_revisao([
                "descricao"     => $request->descricao
                ,"intervalo"    => $request->intervalo
                ,"obs"          => $request->obs
            ]);
            $tipo_revisao->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $tipo_revisao = tipo_revisao::findOrFail($id);

        return view('tipo_revisao.edit' , compact('tipo_revisao'));
    }

    public function edit($id, Request $request)
    {
        $this->validar($request);
        try{
            $tipo_revisao = tipo_revisao::findOrFail($id);
            $tipo_revisao->descricao    = $request->descricao;
            $tipo_revisao->intervalo    = $request->intervalo;
            $tipo_revisao->obs          = $request->obs;
            $tipo_revisao->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    private function validar(Request $request)
    {
        $request->validate([
            'descricao' => 'required|max:100',
            'intervalo' => 'required|numeric|gt:0',
            'obs'       => 'nullable|max:255',
        ], [
            'descricao.required' => 'Informe a descrição do tipo de revisão.',
            'intervalo.required' => 'Informe o KM/Horas da revisão.',
            'intervalo.numeric'  => 'O KM/Horas da revisão deve ser um número.',
            'intervalo.gt'       => 'O KM/Horas da revisão deve ser maior que zero.',
        ]);
    }
}
