<?php

namespace App\Http\Controllers\intervalo_revisao;

use App\Http\Controllers\Controller;
use App\Models\intervalo_revisao;
use Illuminate\Http\Request;

// Cadastro de Intervalo de Revisão: horas entre uma revisão e a próxima (somadas ao horímetro na Revisão)
class intervalo_revisaoController extends Controller
{
    public function listAll()
    {
        $intervalos = intervalo_revisao::orderBy('horas', 'ASC')->get();
        $totalRegistros = $intervalos->count();

        return view('intervalo_revisao.listAll', compact('intervalos', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('intervalo_revisao.add');
    }

    public function store(Request $request)
    {
        $this->validar($request);
        try{
            $intervalo = new intervalo_revisao([
                "horas" => $request->horas
            ]);
            $intervalo->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $intervalo = intervalo_revisao::findOrFail($id);

        return view('intervalo_revisao.edit', compact('intervalo'));
    }

    public function edit($id, Request $request)
    {
        $this->validar($request, $id);
        try{
            $intervalo = intervalo_revisao::findOrFail($id);
            $intervalo->horas = $request->horas;
            $intervalo->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    private function validar(Request $request, $id = null)
    {
        $request->validate([
            'horas' => 'required|numeric|gt:0|unique:intervalo_revisao,horas,'.($id ?: 'NULL').',id,deleted_at,NULL',
        ], [
            'horas.required' => 'Informe as horas de intervalo.',
            'horas.numeric'  => 'As horas de intervalo devem ser um número.',
            'horas.gt'       => 'As horas de intervalo devem ser maiores que zero.',
            'horas.unique'   => 'Já existe um intervalo de revisão com essas horas.',
        ]);
    }
}
