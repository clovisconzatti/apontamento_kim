<?php

namespace App\Http\Controllers\revisao;

use App\Http\Controllers\Controller;
use App\Models\equipamento;
use App\Models\revisao;
use App\Models\tipo_revisao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class revisaoController extends Controller
{
    public function listAll(Request $request ){

        $camposFiltro = [
            'dtInicial'    => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'      => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'equipamento'  => ['label' => 'Equipamento', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('equipamento', 'equipamento')],
            'tipo_revisao' => ['label' => 'Tipo de revisão', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('tipo_revisao', 'descricao')],
            'busca'        => ['label' => 'Nº OS', 'tipo' => 'texto', 'col' => 2],
        ];
        $filtros = $this->lerFiltros($request, 'revisao', $camposFiltro);

        $query = revisao::query();
        $this->filtrarPeriodo($query, $filtros, 'revisao.data');
        $this->filtrarIgual($query, $filtros, [
            'equipamento'  => 'revisao.equipamento',
            'tipo_revisao' => 'revisao.tipo_revisao',
        ]);
        $this->filtrarTexto($query, $filtros['busca'], ['revisao.nr_os']);

        $revisoes = $query->leftJoin('equipamento','equipamento.id','revisao.equipamento')
                                    ->leftJoin('tipo_revisao','tipo_revisao.id','revisao.tipo_revisao')
                                    ->orderBy('revisao.data', 'DESC')
                                    ->orderBy('revisao.id', 'DESC')
                                    ->get([
                                        'revisao.id'
                                        ,'revisao.data'
                                        ,'equipamento.placa'
                                        ,'equipamento.equipamento'
                                        ,'tipo_revisao.descricao as tipo_revisao'
                                        ,'revisao.nr_os'
                                        ,'revisao.horimetro_atual'
                                        ,'revisao.horimetro_proxima'
                                    ]);
        $totalRegistros = $revisoes->count();

        return view('revisao.listAll' , compact('revisoes', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Listas dos selects do formulário
    private function listasFormulario()
    {
        return [
            'equipamentos'   => equipamento::orderby('equipamento')->get(),
            'tipos_revisao'  => tipo_revisao::orderby('descricao')->get(),
            'opcoesPecas'          => $this->opcoesPecas(),
            'opcoesLubrificantes'  => $this->opcoesLubrificantes(),
        ];
    }

    public function formAdd()
    {
        return view('revisao.add', $this->listasFormulario());
    }

    public function store(Request $request)
    {
        [$tipo, $pecas, $lubrificantes] = $this->validar($request);
        try{
            DB::transaction(function () use ($request, $tipo, $pecas, $lubrificantes) {
                $revisao = new revisao([
                    "data"                  => $request->data
                    ,"equipamento"          => $request->equipamento
                    ,"horimetro_atual"      => $request->horimetro_atual
                    ,"tipo_revisao"         => $request->tipo_revisao
                    ,"nr_os"                => $request->nr_os
                    ,"horimetro_proxima"    => $this->proximaRevisao($request, $tipo)
                    ,"obs"                  => $request->obs
                ]);
                $revisao->save();
                $this->gravarItens($revisao, $pecas, $lubrificantes);
            });
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $revisao = revisao::with(['pecas', 'lubrificantes'])->findOrFail($id);
        return view('revisao.edit', array_merge([
            'revisao'              => $revisao,
            'linhasPecas'          => $this->linhasItens($revisao->pecas, 'peca'),
            'linhasLubrificantes'  => $this->linhasItens($revisao->lubrificantes, 'lubrificante'),
        ], $this->listasFormulario()));
    }

    public function edit($id, Request $request)
    {
        [$tipo, $pecas, $lubrificantes] = $this->validar($request);
        try{
            DB::transaction(function () use ($id, $request, $tipo, $pecas, $lubrificantes) {
                $revisao = revisao::findOrFail($id);
                $revisao->data                  = $request->data;
                $revisao->equipamento           = $request->equipamento;
                $revisao->horimetro_atual       = $request->horimetro_atual;
                $revisao->tipo_revisao          = $request->tipo_revisao;
                $revisao->nr_os                 = $request->nr_os;
                $revisao->horimetro_proxima     = $this->proximaRevisao($request, $tipo);
                $revisao->obs                   = $request->obs;
                $revisao->save();
                $this->gravarItens($revisao, $pecas, $lubrificantes);
            });
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    // Substitui as peças e os lubrificantes da revisão pelas linhas enviadas
    private function gravarItens(revisao $revisao, array $pecas, array $lubrificantes)
    {
        $revisao->pecas()->delete();
        foreach ($pecas as $item) {
            $revisao->pecas()->create(['peca' => $item['id'], 'quantidade' => $item['quantidade']]);
        }

        $revisao->lubrificantes()->delete();
        foreach ($lubrificantes as $item) {
            $revisao->lubrificantes()->create(['lubrificante' => $item['id'], 'quantidade' => $item['quantidade']]);
        }
    }

    // Valida os campos e devolve [tipo de revisão, peças, lubrificantes]
    private function validar(Request $request)
    {
        $request->validate([
            'data'            => 'required|date',
            'equipamento'     => 'required|integer|exists:equipamento,id',
            'horimetro_atual' => 'required|numeric|min:0',
            'tipo_revisao'    => 'required|integer|exists:tipo_revisao,id',
            'nr_os'           => 'nullable|max:30',
            'obs'             => 'nullable|max:255',
        ], [
            'data.required'            => 'Informe a data da revisão.',
            'equipamento.required'     => 'Selecione o veículo/equipamento.',
            'equipamento.exists'       => 'Selecione o veículo/equipamento.',
            'horimetro_atual.required' => 'Informe o horímetro atual.',
            'horimetro_atual.numeric'  => 'O horímetro atual deve ser um número.',
            'tipo_revisao.required'    => 'Selecione o tipo da revisão.',
            'tipo_revisao.exists'      => 'Selecione o tipo da revisão.',
            'nr_os.max'                => 'O número da OS pode ter no máximo 30 caracteres.',
            'obs.max'                  => 'A observação pode ter no máximo 255 caracteres.',
        ]);

        $erros = [];
        $pecas = $this->lerItens($request->input('pecas'), 'peca', 'Peça', 'pecas', $erros);
        $lubrificantes = $this->lerItens($request->input('lubrificantes'), 'lubrificante', 'Lubrificante', 'lubrificantes', $erros);
        if ($erros) {
            throw ValidationException::withMessages($erros);
        }

        return [tipo_revisao::find($request->tipo_revisao), $pecas, $lubrificantes];
    }

    // Horímetro da próxima revisão = horímetro atual + KM/Horas do tipo de revisão.
    // Calculado aqui também: o valor da tela é só uma prévia.
    private function proximaRevisao(Request $request, $tipo)
    {
        return (float) $request->horimetro_atual + (float) optional($tipo)->intervalo;
    }
}
