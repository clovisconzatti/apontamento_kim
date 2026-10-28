<?php

namespace App\Http\Controllers\ajuste_combustivel;

use App\Http\Controllers\Controller;
use App\Models\ajuste_combustivel;
use App\Models\comboio;
use App\Support\SaldoCombustivel;
use Illuminate\Http\Request;

class ajuste_combustivelController extends Controller
{
    const TIPOS = [
        'falta' => 'Falta',
        'sobra' => 'Sobra',
    ];

    public function listAll(Request $request ){

        $camposFiltro = [
            'dtInicial' => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'   => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'tanque'    => ['label' => 'Tanque', 'tipo' => 'select', 'col' => 4, 'opcoes' => $this->opcoesTabela('comboio', 'tanque')],
            'tipo'      => ['label' => 'Tipo do ajuste', 'tipo' => 'select', 'col' => 2, 'opcoes' => self::TIPOS],
        ];
        $filtros = $this->lerFiltros($request, 'ajuste_combustivel', $camposFiltro);

        $query = ajuste_combustivel::query();
        $this->filtrarPeriodo($query, $filtros, 'ajuste_combustivel.data');
        $this->filtrarIgual($query, $filtros, [
            'tanque' => 'ajuste_combustivel.tanque',
            'tipo'   => 'ajuste_combustivel.tipo',
        ]);

        $ajustes = $query->leftJoin('comboio', 'comboio.id', 'ajuste_combustivel.tanque')
                            ->orderBy('ajuste_combustivel.data', 'DESC')
                            ->orderBy('ajuste_combustivel.id', 'DESC')
                            ->get([
                                'ajuste_combustivel.id'
                                ,'ajuste_combustivel.data'
                                ,'comboio.tanque'
                                ,'ajuste_combustivel.quantidade'
                                ,'ajuste_combustivel.tipo'
                            ]);
        $totalRegistros = $ajustes->count();
        $tipos = self::TIPOS;

        return view('ajuste_combustivel.listAll', compact('ajustes', 'tipos', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        $tanques = comboio::orderby('tanque')->get();
        $tipos = self::TIPOS;
        // Saldo do sistema de cada tanque próprio (para comparar com a medição e sugerir falta ou sobra)
        $saldos = SaldoCombustivel::saldos()->mapWithKeys(fn($t) => [$t->id => round($t->saldo, 2)])->all();
        return view('ajuste_combustivel.add', compact('tanques', 'tipos', 'saldos'));
    }

    public function store(Request $request)
    {
        $this->validar($request);
        try{
            $ajuste = new ajuste_combustivel($this->dados($request));
            $ajuste->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $ajuste = ajuste_combustivel::findOrFail($id);
        $tanques = comboio::orderby('tanque')->get();
        $tipos = self::TIPOS;
        return view('ajuste_combustivel.edit', compact('ajuste', 'tanques', 'tipos'));
    }

    public function edit($id, Request $request)
    {
        $this->validar($request);
        try{
            $ajuste = ajuste_combustivel::findOrFail($id);
            $ajuste->fill($this->dados($request));
            $ajuste->save();
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json('success');
    }

    private function dados(Request $request)
    {
        return [
            'data'       => $request->data,
            'tanque'     => $request->tanque,
            'quantidade' => (float) $request->quantidade,
            'tipo'       => $request->tipo,
        ];
    }

    private function validar(Request $request)
    {
        $request->validate([
            'data'       => 'required|date',
            'tanque'     => 'required|integer|exists:comboio,id',
            'quantidade' => 'required|numeric|gt:0',
            'tipo'       => 'required|in:'.implode(',', array_keys(self::TIPOS)),
        ], [
            'data.required'       => 'Informe a data do ajuste.',
            'tanque.required'     => 'Selecione o tanque a ajustar.',
            'tanque.exists'       => 'Selecione o tanque a ajustar.',
            'quantidade.required' => 'Informe a quantidade ajustada.',
            'quantidade.numeric'  => 'A quantidade deve ser um número.',
            'quantidade.gt'       => 'A quantidade deve ser maior que zero (use o tipo Falta ou Sobra para o sentido).',
            'tipo.required'       => 'Escolha o tipo do ajuste: Falta ou Sobra.',
            'tipo.in'             => 'Escolha o tipo do ajuste: Falta ou Sobra.',
        ]);
    }
}
