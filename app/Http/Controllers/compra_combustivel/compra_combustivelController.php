<?php

namespace App\Http\Controllers\compra_combustivel;

use App\Http\Controllers\Controller;
use App\Models\comboio;
use App\Models\compra_combustivel;
use App\Models\fornecedor;
use App\Models\tipo_combustivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class compra_combustivelController extends Controller
{
    public function listAll(Request $request ){

        $camposFiltro = [
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'fornecedor'  => ['label' => 'Fornecedor', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('fornecedor', 'fornecedor')],
            'tanque'      => ['label' => 'Tanque de destino', 'tipo' => 'select', 'col' => 3, 'opcoes' => $this->opcoesTabela('comboio', 'tanque')],
            'combustivel' => ['label' => 'Combustível', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesTabela('tipo_combustivel', 'combustivel')],
            'busca'       => ['label' => 'Nº NF', 'tipo' => 'texto', 'col' => 2],
        ];
        $filtros = $this->lerFiltros($request, 'compra_combustivel', $camposFiltro);

        $query = compra_combustivel::query();
        $this->filtrarPeriodo($query, $filtros, 'compra_combustivel.data');
        $this->filtrarIgual($query, $filtros, [
            'fornecedor'  => 'compra_combustivel.fornecedor',
            'tanque'      => 'compra_combustivel.tanque',
            'combustivel' => 'compra_combustivel.combustivel',
        ]);
        $this->filtrarTexto($query, $filtros['busca'], ['compra_combustivel.nr_nf']);

        $compras = $query->leftJoin('fornecedor', 'fornecedor.id', 'compra_combustivel.fornecedor')
                            ->leftJoin('comboio', 'comboio.id', 'compra_combustivel.tanque')
                            ->leftJoin('tipo_combustivel', 'tipo_combustivel.id', 'compra_combustivel.combustivel')
                            ->orderBy('compra_combustivel.data', 'DESC')
                            ->orderBy('compra_combustivel.id', 'DESC')
                            ->get([
                                'compra_combustivel.id'
                                ,'compra_combustivel.data'
                                ,'fornecedor.fornecedor'
                                ,'compra_combustivel.nr_nf'
                                ,'tipo_combustivel.combustivel'
                                ,'comboio.tanque'
                                ,'compra_combustivel.litros'
                                ,'compra_combustivel.valor_litro'
                                ,'compra_combustivel.valor_total'
                            ]);
        $totalRegistros = $compras->count();

        return view('compra_combustivel.listAll', compact('compras', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    // Listas dos selects do formulário
    private function listasFormulario()
    {
        return [
            'fornecedores'      => fornecedor::orderby('fornecedor')->get(),
            'tanques'           => comboio::orderby('tanque')->get(),
            'tipos_combustivel' => tipo_combustivel::orderby('combustivel')->get(),
        ];
    }

    public function formAdd()
    {
        return view('compra_combustivel.add', $this->listasFormulario());
    }

    public function store(Request $request)
    {
        $this->validar($request);
        try{
            $compra = new compra_combustivel($this->dados($request));
            $compra->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $compra = compra_combustivel::findOrFail($id);
        return view('compra_combustivel.edit', array_merge(['compra' => $compra], $this->listasFormulario()));
    }

    public function edit($id, Request $request)
    {
        $this->validar($request, $id);
        try{
            $compra = compra_combustivel::findOrFail($id);
            $compra->fill($this->dados($request));
            $compra->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    // Campos gravados; o custo por litro é sempre valor total da NF ÷ quantidade (o da tela é só uma prévia)
    private function dados(Request $request)
    {
        return [
            'data'        => $request->data,
            'fornecedor'  => $request->fornecedor,
            'nr_nf'       => trim((string) $request->nr_nf) ?: null,
            'combustivel' => $request->combustivel,
            'tanque'      => $request->tanque,
            'litros'      => (float) $request->litros,
            'valor_total' => round((float) $request->valor_total, 2),
            'valor_litro' => round((float) $request->valor_total / (float) $request->litros, 4),
        ];
    }

    private function validar(Request $request, $id = null)
    {
        $request->validate([
            'data'        => 'required|date',
            'fornecedor'  => 'required|integer|exists:fornecedor,id',
            'nr_nf'       => 'nullable|max:30',
            'combustivel' => 'required|integer|exists:tipo_combustivel,id',
            'tanque'      => 'required|integer|exists:comboio,id',
            'litros'      => 'required|numeric|gt:0',
            'valor_total' => 'required|numeric|gt:0',
        ], [
            'data.required'        => 'Informe a data da compra.',
            'fornecedor.required'  => 'Selecione o fornecedor.',
            'fornecedor.exists'    => 'Selecione o fornecedor.',
            'nr_nf.max'            => 'O número da NF pode ter no máximo 30 caracteres.',
            'combustivel.required' => 'Selecione o tipo de combustível.',
            'combustivel.exists'   => 'Selecione o tipo de combustível.',
            'tanque.required'      => 'Selecione o tanque de destino.',
            'tanque.exists'        => 'Selecione o tanque de destino.',
            'litros.required'      => 'Informe a quantidade de litros.',
            'litros.gt'            => 'A quantidade de litros deve ser maior que zero.',
            'valor_total.required' => 'Informe o valor total da NF.',
            'valor_total.gt'       => 'O valor total da NF deve ser maior que zero.',
        ]);

        // A mesma NF não pode ser lançada duas vezes para o mesmo fornecedor
        $nf = trim((string) $request->nr_nf);
        if ($nf !== '') {
            $repetida = DB::table('compra_combustivel')
                            ->whereNull('deleted_at')
                            ->where('fornecedor', $request->fornecedor)
                            ->where('nr_nf', $nf)
                            ->when($id, fn($q) => $q->where('id', '<>', $id))
                            ->first(['data']);
            if ($repetida) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nr_nf' => 'A NF '.$nf.' deste fornecedor já foi lançada em '.date('d/m/Y', strtotime($repetida->data)).'.',
                ]);
            }
        }
    }
}
