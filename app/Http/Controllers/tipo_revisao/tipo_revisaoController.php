<?php

namespace App\Http\Controllers\tipo_revisao;

use App\Http\Controllers\Controller;
use App\Models\servico_revisao;
use App\Models\tipo_revisao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class tipo_revisaoController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca' => ['label' => 'Pesquisar', 'tipo' => 'texto', 'col' => 6, 'placeholder' => 'Tipo de revisão'],
        ];
        $filtros = $this->lerFiltros($request, 'tipo_revisao', $camposFiltro);

        $query = tipo_revisao::withCount(['servicos', 'pecas', 'lubrificantes'])->orderBy('descricao', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['descricao', 'obs']);
        $tipos_revisao = $query->get();
        $totalRegistros = $tipos_revisao->count();

        return view('tipo_revisao.listAll' , compact('tipos_revisao', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Opções das listas de serviços e peças do formulário
    private function listasFormulario()
    {
        return [
            'opcoesServicos' => servico_revisao::orderBy('servico')->pluck('servico', 'id')->all(),
            'opcoesPecas'    => $this->opcoesPecas(),
            'opcoesLubrificantes' => $this->opcoesLubrificantes(),
        ];
    }

    public function formAdd()
    {
        return view('tipo_revisao.add', $this->listasFormulario());
    }

    public function store(Request $request)
    {
        [$servicos, $pecas, $lubrificantes] = $this->validar($request);
        try{
            DB::transaction(function () use ($request, $servicos, $pecas, $lubrificantes) {
                $tipo_revisao = new tipo_revisao([
                    "descricao"     => $request->descricao
                    ,"intervalo"    => $request->intervalo
                    ,"obs"          => $request->obs
                ]);
                $tipo_revisao->save();
                $this->gravarItens($tipo_revisao, $servicos, $pecas, $lubrificantes);
            });
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $tipo_revisao = tipo_revisao::with(['servicos', 'pecas', 'lubrificantes'])->findOrFail($id);

        return view('tipo_revisao.edit', array_merge([
            'tipo_revisao'   => $tipo_revisao,
            'linhasServicos' => $this->linhasItens($tipo_revisao->servicos, 'servico'),
            'linhasPecas'    => $this->linhasItens($tipo_revisao->pecas, 'peca'),
            'linhasLubrificantes' => $this->linhasItens($tipo_revisao->lubrificantes, 'lubrificante'),
        ], $this->listasFormulario()));
    }

    public function edit($id, Request $request)
    {
        [$servicos, $pecas, $lubrificantes] = $this->validar($request);
        try{
            DB::transaction(function () use ($id, $request, $servicos, $pecas, $lubrificantes) {
                $tipo_revisao = tipo_revisao::findOrFail($id);
                $tipo_revisao->descricao    = $request->descricao;
                $tipo_revisao->intervalo    = $request->intervalo;
                $tipo_revisao->obs          = $request->obs;
                $tipo_revisao->save();
                $this->gravarItens($tipo_revisao, $servicos, $pecas, $lubrificantes);
            });
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    // Substitui os serviços, as peças e os lubrificantes do tipo de revisão pelas linhas enviadas
    private function gravarItens(tipo_revisao $tipo_revisao, array $servicos, array $pecas, array $lubrificantes)
    {
        $tipo_revisao->lubrificantes()->delete();
        foreach ($lubrificantes as $item) {
            $tipo_revisao->lubrificantes()->create(['lubrificante' => $item['id'], 'quantidade' => $item['quantidade']]);
        }

        $tipo_revisao->servicos()->delete();
        foreach ($servicos as $item) {
            $tipo_revisao->servicos()->create(['servico' => $item['id']]);
        }

        $tipo_revisao->pecas()->delete();
        foreach ($pecas as $item) {
            $tipo_revisao->pecas()->create(['peca' => $item['id'], 'quantidade' => $item['quantidade']]);
        }
    }

    // Valida os campos e devolve [serviços, peças, lubrificantes]
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

        $erros = [];
        $servicos = $this->lerItens($request->input('servicos'), 'servico_revisao', 'Serviço', 'servicos', $erros, false);
        $pecas = $this->lerItens($request->input('pecas'), 'peca', 'Peça', 'pecas', $erros);
        $lubrificantes = $this->lerItens($request->input('lubrificantes'), 'lubrificante', 'Lubrificante', 'lubrificantes', $erros);
        if ($erros) {
            throw ValidationException::withMessages($erros);
        }
        return [$servicos, $pecas, $lubrificantes];
    }
}
