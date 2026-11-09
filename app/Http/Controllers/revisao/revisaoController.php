<?php

namespace App\Http\Controllers\revisao;

use App\Http\Controllers\Controller;
use App\Models\equipamento;
use App\Models\intervalo_revisao;
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
            'tipo_revisao' => ['label' => 'Revisão atual', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('tipo_revisao', 'descricao')],
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
                                    ->leftJoin('tipo_revisao as proxima','proxima.id','revisao.proxima_revisao')
                                    ->leftJoin('intervalo_revisao','intervalo_revisao.id','revisao.intervalo_revisao')
                                    ->orderBy('revisao.data', 'DESC')
                                    ->orderBy('revisao.id', 'DESC')
                                    ->get([
                                        'revisao.id'
                                        ,'revisao.data'
                                        ,'equipamento.placa'
                                        ,'equipamento.equipamento'
                                        ,'tipo_revisao.descricao as tipo_revisao'
                                        ,'proxima.descricao as proxima_revisao'
                                        ,'intervalo_revisao.horas as intervalo_horas'
                                        ,'revisao.nr_os'
                                        ,'revisao.hora_inicial'
                                        ,'revisao.hora_final'
                                        ,'revisao.custo_terceiro'
                                        ,'revisao.custo_hora'
                                        ,'revisao.custo_deslocamento'
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
            'intervalos_revisao' => intervalo_revisao::orderby('horas')->get(),
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
        [$intervalo, $pecas, $lubrificantes] = $this->validar($request);
        try{
            DB::transaction(function () use ($request, $intervalo, $pecas, $lubrificantes) {
                $revisao = new revisao([
                    "data"                  => $request->data
                    ,"equipamento"          => $request->equipamento
                    ,"horimetro_atual"      => $request->horimetro_atual
                    ,"tipo_revisao"         => $request->tipo_revisao
                    ,"intervalo_revisao"    => $request->intervalo_revisao
                    ,"proxima_revisao"      => $request->proxima_revisao
                    ,"nr_os"                => $request->nr_os
                    ,"hora_inicial"         => $request->hora_inicial ?: null
                    ,"hora_final"           => $request->hora_final ?: null
                    ,"custo_terceiro"       => $this->valor($request->custo_terceiro)
                    ,"custo_hora"           => $this->valor($request->custo_hora)
                    ,"custo_deslocamento"   => $this->valor($request->custo_deslocamento)
                    ,"horimetro_proxima"    => $this->proximaRevisao($request, $intervalo)
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
        [$intervalo, $pecas, $lubrificantes] = $this->validar($request);
        try{
            DB::transaction(function () use ($id, $request, $intervalo, $pecas, $lubrificantes) {
                $revisao = revisao::findOrFail($id);
                $revisao->data                  = $request->data;
                $revisao->equipamento           = $request->equipamento;
                $revisao->horimetro_atual       = $request->horimetro_atual;
                $revisao->tipo_revisao          = $request->tipo_revisao;
                $revisao->intervalo_revisao     = $request->intervalo_revisao;
                $revisao->proxima_revisao       = $request->proxima_revisao;
                $revisao->nr_os                 = $request->nr_os;
                $revisao->hora_inicial          = $request->hora_inicial ?: null;
                $revisao->hora_final            = $request->hora_final ?: null;
                $revisao->custo_terceiro        = $this->valor($request->custo_terceiro);
                $revisao->custo_hora            = $this->valor($request->custo_hora);
                $revisao->custo_deslocamento    = $this->valor($request->custo_deslocamento);
                $revisao->horimetro_proxima     = $this->proximaRevisao($request, $intervalo);
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

    // Valida os campos e devolve [intervalo de revisão, peças, lubrificantes]
    private function validar(Request $request)
    {
        $request->validate([
            'data'              => 'required|date',
            'equipamento'       => 'required|integer|exists:equipamento,id',
            'horimetro_atual'   => 'required|numeric|min:0',
            'tipo_revisao'      => 'required|integer|exists:tipo_revisao,id',
            'intervalo_revisao' => 'required|integer|exists:intervalo_revisao,id,deleted_at,NULL',
            'proxima_revisao'   => 'required|integer|exists:tipo_revisao,id',
            'nr_os'             => 'nullable|max:30',
            'hora_inicial'      => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'hora_final'        => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'custo_terceiro'    => 'nullable|numeric|min:0',
            'custo_hora'        => 'nullable|numeric|min:0',
            'custo_deslocamento' => 'nullable|numeric|min:0',
            'obs'               => 'nullable|max:255',
        ], [
            'hora_inicial.regex'       => 'Informe a hora de início da revisão no formato hh:mm.',
            'hora_final.regex'         => 'Informe a hora final da revisão no formato hh:mm.',
            'custo_terceiro.numeric'   => 'O custo de terceiro deve ser um valor numérico.',
            'custo_terceiro.min'       => 'O custo de terceiro não pode ser negativo.',
            'custo_hora.numeric'       => 'O custo hora deve ser um valor numérico.',
            'custo_hora.min'           => 'O custo hora não pode ser negativo.',
            'custo_deslocamento.numeric' => 'O custo de deslocamento deve ser um valor numérico.',
            'custo_deslocamento.min'     => 'O custo de deslocamento não pode ser negativo.',
            'data.required'            => 'Informe a data da revisão.',
            'equipamento.required'     => 'Selecione o veículo/equipamento.',
            'equipamento.exists'       => 'Selecione o veículo/equipamento.',
            'horimetro_atual.required' => 'Informe o horímetro atual.',
            'horimetro_atual.numeric'  => 'O horímetro atual deve ser um número.',
            'tipo_revisao.required'    => 'Selecione a revisão atual.',
            'tipo_revisao.exists'      => 'Selecione a revisão atual.',
            'intervalo_revisao.required' => 'Selecione o intervalo de revisão.',
            'intervalo_revisao.exists'   => 'Selecione o intervalo de revisão.',
            'proxima_revisao.required'   => 'Selecione a próxima revisão.',
            'proxima_revisao.exists'     => 'Selecione a próxima revisão.',
            'nr_os.max'                => 'O número da OS pode ter no máximo 30 caracteres.',
            'obs.max'                  => 'A observação pode ter no máximo 255 caracteres.',
        ]);

        $erros = [];
        $pecas = $this->lerItens($request->input('pecas'), 'peca', 'Peça', 'pecas', $erros);
        $lubrificantes = $this->lerItens($request->input('lubrificantes'), 'lubrificante', 'Lubrificante', 'lubrificantes', $erros);
        if ($erros) {
            throw ValidationException::withMessages($erros);
        }

        return [intervalo_revisao::find($request->intervalo_revisao), $pecas, $lubrificantes];
    }

    // Valor em R$ opcional: vazio grava nulo
    private function valor($valor)
    {
        return $valor === null || $valor === '' ? null : (float) $valor;
    }

    // Horímetro da próxima revisão = horímetro atual + horas do intervalo de revisão.
    // Calculado aqui também: o valor da tela é só uma prévia.
    private function proximaRevisao(Request $request, $intervalo)
    {
        return (float) $request->horimetro_atual + (float) optional($intervalo)->horas;
    }
}
