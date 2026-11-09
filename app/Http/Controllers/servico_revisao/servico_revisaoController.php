<?php

namespace App\Http\Controllers\servico_revisao;

use App\Http\Controllers\Controller;
use App\Models\servico_revisao;
use Illuminate\Http\Request;

// Cadastro de Serviços de Revisão (ex.: Troca de Óleo do Motor), usados na lista de serviços do Tipo de Revisão
class servico_revisaoController extends Controller
{
    public function listAll(Request $request)
    {
        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Serviço'],
        ];
        $filtros = $this->lerFiltros($request, 'servico_revisao', $camposFiltro);

        $query = servico_revisao::orderBy('servico', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['servico']);
        $servicos = $query->get();
        $totalRegistros = $servicos->count();

        return view('servico_revisao.listAll', compact('servicos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('servico_revisao.add');
    }

    public function store(Request $request)
    {
        $this->validar($request);
        try{
            $servico = new servico_revisao([
                "servico" => trim($request->servico)
            ]);
            $servico->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $servico = servico_revisao::findOrFail($id);

        return view('servico_revisao.edit', compact('servico'));
    }

    public function edit($id, Request $request)
    {
        $this->validar($request, $id);
        try{
            $servico = servico_revisao::findOrFail($id);
            $servico->servico = trim($request->servico);
            $servico->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    private function validar(Request $request, $id = null)
    {
        $request->validate([
            'servico' => 'required|max:150|unique:servico_revisao,servico,'.($id ?: 'NULL').',id,deleted_at,NULL',
        ], [
            'servico.required' => 'Informe o serviço.',
            'servico.max'      => 'O serviço pode ter no máximo 150 caracteres.',
            'servico.unique'   => 'Este serviço já está cadastrado.',
        ]);
    }
}
