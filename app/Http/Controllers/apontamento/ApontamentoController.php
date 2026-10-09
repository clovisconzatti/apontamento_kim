<?php

namespace App\Http\Controllers\apontamento;

use App\Http\Controllers\Controller;
use App\Models\apontamento;
use App\Models\equipamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApontamentoController extends Controller
{
    const POR_PAGINA = 50;

    public function listAll(Request $request)
    {
        $placas = equipamento::orderBy('placa')->get(['id', 'placa', 'equipamento']);
        $opcoesEquipamento = [];
        foreach ($placas as $placa) {
            $opcoesEquipamento[$placa->id] = $placa->placa.' - '.$placa->equipamento;
        }

        $camposFiltro = [
            'dtInicial'   => ['label' => 'Data inicial', 'tipo' => 'data', 'col' => 2],
            'dtFinal'     => ['label' => 'Data final', 'tipo' => 'data', 'col' => 2],
            'equipamento' => ['label' => 'Equipamento', 'tipo' => 'select', 'col' => 4, 'opcoes' => $opcoesEquipamento],
            'placa'       => ['label' => 'Placa / descrição contém', 'tipo' => 'texto', 'col' => 4],
            'combustivel' => ['label' => 'Combustível', 'tipo' => 'select', 'opcoes' => array_combine(apontamento::COMBUSTIVEIS, apontamento::COMBUSTIVEIS)],
            'origem'      => ['label' => 'Comboio', 'tipo' => 'select', 'opcoes' => $this->opcoesComboio()],
            'anexo'       => ['label' => 'Anexo', 'tipo' => 'select', 'col' => 2, 'opcoes' => ['com' => 'Com anexo', 'sem' => 'Sem anexo']],
        ];
        // Padrão: mês atual (evita carregar todo o histórico)
        $filtros = $this->lerFiltros($request, 'apontamento', $camposFiltro, [
            'dtInicial' => date('Y-m-01'),
            'dtFinal'   => date('Y-m-d'),
        ]);

        $query = apontamento::leftJoin('equipamento', 'equipamento.id', 'apontamento.equipamento');

        if ($filtros['equipamento']) {
            $query->where('apontamento.equipamento', $filtros['equipamento']);
        }
        if ($filtros['placa']) {
            $query->where(function ($q) use ($filtros) {
                $q->where('equipamento.placa', 'like', '%'.$filtros['placa'].'%')
                  ->orWhere('equipamento.equipamento', 'like', '%'.$filtros['placa'].'%');
            });
        }
        if ($filtros['dtInicial']) {
            $query->where('apontamento.data', '>=', $filtros['dtInicial']);
        }
        if ($filtros['dtFinal']) {
            $query->where('apontamento.data', '<=', $filtros['dtFinal']);
        }
        if ($filtros['combustivel']) {
            $query->where('apontamento.combustivel', $filtros['combustivel']);
        }
        if ($filtros['origem']) {
            $query->where('apontamento.origem', $filtros['origem']);
        }
        if ($filtros['anexo'] === 'com') {
            $query->whereNotNull('apontamento.anexo')->where('apontamento.anexo', '<>', '');
        } elseif ($filtros['anexo'] === 'sem') {
            $query->where(function ($q) {
                $q->whereNull('apontamento.anexo')->orWhere('apontamento.anexo', '');
            });
        }

        // Totais do período filtrado (todas as páginas), por combustível
        $totais = (clone $query)
                    ->groupBy('apontamento.combustivel')
                    ->orderBy('apontamento.combustivel')
                    ->get([
                        'apontamento.combustivel'
                        ,DB::raw('COUNT(*) AS qtde')
                        ,DB::raw('SUM(apontamento.litros) AS litros')
                    ]);

        // Registro anterior do mesmo equipamento (ignora Arla, que não entra no cálculo de consumo)
        $anterior = "FROM apontamento AS apto
                     WHERE apto.equipamento = apontamento.equipamento
                       AND apto.deleted_at IS NULL
                       AND COALESCE(apto.combustivel, '') <> 'Arla'
                       AND (apto.data < apontamento.data OR (apto.data = apontamento.data AND apto.id < apontamento.id))
                     ORDER BY apto.data DESC, apto.id DESC LIMIT 1";

        $apontamentos = $query
                        ->orderBy('apontamento.data', 'DESC')
                        ->orderBy('apontamento.id', 'DESC')
                        ->select([
                            'apontamento.id'
                            ,'equipamento.id as id_equipamento'
                            ,'apontamento.data'
                            ,'equipamento.equipamento'
                            ,'equipamento.placa'
                            ,'apontamento.litros'
                            ,'apontamento.km'
                            ,'apontamento.horas'
                            ,'apontamento.combustivel'
                            ,'apontamento.obs'
                            ,'apontamento.origem'
                            ,'apontamento.anexo'
                            ,DB::raw("(SELECT apto.km $anterior) AS km_anterior")
                            ,DB::raw("(SELECT apto.horas $anterior) AS hora_anterior")
                        ])
                        ->paginate(self::POR_PAGINA);

        $totalRegistros = $apontamentos->total();

        return view('apontamento.listAll', compact('apontamentos', 'totais', 'filtros', 'camposFiltro', 'totalRegistros'));
    }

    public function formAdd()
    {
        $placas       = equipamento::orderBy('placa')->get();
        $combustiveis = apontamento::COMBUSTIVEIS;
        $comboios     = apontamento::COMBOIOS;
        return view('apontamento.add', compact('placas', 'combustiveis', 'comboios'));
    }

    public function strore(Request $request)
    {
        $dados = $this->validar($request);

        try {
            $apontamento = new apontamento($dados);
            $apontamento->ultimo_km = optional($this->registroAnterior($dados['equipamento'], $dados['data']))->km;
            $apontamento->save();
        } catch (\Exception $e) {
            Log::error('Erro ao gravar apontamento: '.$e->getMessage());
            return response()->json(['message' => 'Erro ao gravar o abastecimento.'], 500);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $apontamento  = apontamento::findOrFail($id);
        $placas       = equipamento::orderBy('placa')->get();
        $combustiveis = apontamento::COMBUSTIVEIS;
        $comboios     = apontamento::COMBOIOS;

        // Mantém valores antigos (ex.: "Interno"/"Externo") para não perdê-los ao salvar
        if ($apontamento->origem && !in_array($apontamento->origem, $comboios)) {
            $comboios[] = $apontamento->origem;
        }
        if ($apontamento->combustivel && !in_array($apontamento->combustivel, $combustiveis)) {
            $combustiveis[] = $apontamento->combustivel;
        }

        return view('apontamento.edit', compact('apontamento', 'placas', 'combustiveis', 'comboios'));
    }

    public function edit($id, Request $request)
    {
        $apontamento = apontamento::findOrFail($id);
        $dados = $this->validar($request, $apontamento);

        try {
            $apontamento->fill($dados);
            $apontamento->ultimo_km = optional($this->registroAnterior($dados['equipamento'], $dados['data'], $apontamento->id))->km;
            $apontamento->save();
        } catch (\Exception $e) {
            Log::error('Erro ao alterar apontamento '.$id.': '.$e->getMessage());
            return response()->json(['message' => 'Erro ao alterar o abastecimento.'], 500);
        }
        return response()->json('success');
    }

    public function destroy($id)
    {
        $apontamento = apontamento::findOrFail($id);
        $apontamento->delete();
        return redirect()->route('apontamento.listAll')->with('success', 'Abastecimento excluído com sucesso.');
    }

    public function checaKm(Request $request)
    {
        return response()->json($this->ultimaLeitura($request));
    }

    public function checaHora(Request $request)
    {
        return response()->json($this->ultimaLeitura($request));
    }

    public function apontamentoAnexo($apontamento)
    {
        $abastecida = apontamento::select([
                                    'equipamento.id as id_equipamento'
                                    ,'apontamento.id'
                                    ,'apontamento.data'
                                    ,'equipamento.equipamento'
                                    ,'equipamento.placa'
                                    ,'apontamento.anexo'
                                ])
                                ->leftJoin('equipamento', 'equipamento.id', 'apontamento.equipamento')
                                ->findOrFail($apontamento);

        $urlAnexo = null;
        if ($abastecida->anexo) {
            $caminho = $this->caminhoAnexo($abastecida->placa, $abastecida->anexo);
            if ($caminho) {
                $urlAnexo = asset('storage/'.substr($caminho, strlen('public/')));
            }
        }

        return view('apontamento.anexo', compact('abastecida', 'urlAnexo'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'apontamento' => 'required|integer|exists:apontamento,id',
            'arquivo'     => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'arquivo.required' => 'Selecione um arquivo.',
            'arquivo.mimes'    => 'O arquivo deve ser PDF ou imagem (jpg, png, webp).',
            'arquivo.max'      => 'O arquivo deve ter no máximo 10 MB.',
        ]);

        $apontamento = apontamento::with('equipamentoRel')->findOrFail($request->apontamento);

        // Pasta pela placa cadastrada no banco (não confia no valor vindo do formulário)
        $pasta = $this->pastaPlaca(optional($apontamento->equipamentoRel)->placa);
        $nomeArquivo = 'Anexo_'.$apontamento->id.'.'.$request->file('arquivo')->guessExtension();

        // Remove o anexo anterior se tiver outro nome/extensão
        if ($apontamento->anexo && $apontamento->anexo !== $nomeArquivo) {
            $antigo = $this->caminhoAnexo(optional($apontamento->equipamentoRel)->placa, $apontamento->anexo);
            if ($antigo) {
                Storage::delete($antigo);
            }
        }

        $request->file('arquivo')->storeAs('public/'.$pasta, $nomeArquivo);
        $apontamento->anexo = $nomeArquivo;
        $apontamento->save();

        return redirect()->route('apontamento.apontamentoAnexo', ['apontamento' => $apontamento->id])
                         ->with('success', 'Arquivo enviado com sucesso.');
    }

    /*********************************** auxiliares ***********************************/

    private function validar(Request $request, apontamento $atual = null)
    {
        $comboios = apontamento::COMBOIOS;
        if ($atual && $atual->origem) {
            $comboios[] = $atual->origem;
        }
        $combustiveis = apontamento::COMBUSTIVEIS;
        if ($atual && $atual->combustivel) {
            $combustiveis[] = $atual->combustivel;
        }

        $request->validate([
            'data'                => 'required|date',
            'equipamento'         => 'required|integer|exists:equipamento,id',
            'litros'              => 'required|numeric|gt:0',
            'km'                  => 'nullable|required_without:horas|numeric|min:0',
            'horas'               => 'nullable|required_without:km|numeric|min:0',
            'combustivel'         => 'required|in:'.implode(',', $combustiveis),
            'origemAbastecimento' => 'nullable|in:'.implode(',', $comboios),
            'obs'                 => 'nullable|string|max:50',
        ], [
            'equipamento.required'  => 'Selecione a placa/equipamento.',
            'equipamento.exists'    => 'Equipamento não encontrado.',
            'litros.gt'             => 'O total de litros deve ser maior que zero.',
            'km.required_without'   => 'Informe o Km atual ou a Hora atual.',
            'horas.required_without'=> 'Informe o Km atual ou a Hora atual.',
            'combustivel.in'        => 'Combustível inválido.',
            'origemAbastecimento.in'=> 'Comboio inválido.',
            'obs.max'               => 'A observação deve ter no máximo 50 caracteres.',
        ]);

        return [
            'data'        => $request->data,
            'equipamento' => $request->equipamento,
            'litros'      => $request->litros,
            'km'          => $request->filled('km') ? $request->km : null,
            'horas'       => $request->filled('horas') ? $request->horas : null,
            'combustivel' => $request->combustivel,
            'obs'         => $request->obs,
            'origem'      => $request->origemAbastecimento,
        ];
    }

    // Último abastecimento do equipamento antes da data informada (ignorando o próprio registro)
    private function registroAnterior($equipamento, $data = null, $ignorarId = null)
    {
        $query = apontamento::where('equipamento', $equipamento)
                    ->whereRaw("COALESCE(combustivel, '') <> 'Arla'");
        if ($data) {
            $query->where('data', '<=', $data);
        }
        if ($ignorarId) {
            $query->where('id', '<>', $ignorarId);
        }
        return $query->orderBy('data', 'desc')->orderBy('id', 'desc')->first();
    }

    // Maior km e maior hora já lançados para o equipamento (usado no aviso do formulário)
    private function ultimaLeitura(Request $request)
    {
        $query = apontamento::where('equipamento', $request->equipamento);
        if ($request->filled('id')) {
            $query->where('id', '<>', $request->id);
        }
        if ($request->filled('data')) {
            $query->where('data', '<=', $request->data);
        }
        $ultimo = (clone $query)->orderBy('data', 'desc')->orderBy('id', 'desc')->first();

        return [
            'km'    => (clone $query)->whereNotNull('km')->orderBy('data', 'desc')->orderBy('id', 'desc')->value('km'),
            'horas' => (clone $query)->whereNotNull('horas')->orderBy('data', 'desc')->orderBy('id', 'desc')->value('horas'),
            'data'  => optional($ultimo)->data,
        ];
    }

    private function opcoesComboio()
    {
        $usados = apontamento::whereNotNull('origem')->where('origem', '<>', '')
                    ->distinct()->orderBy('origem')->pluck('origem')->all();
        $todos = array_values(array_unique(array_merge(apontamento::COMBOIOS, $usados)));
        return array_combine($todos, $todos);
    }

    private function pastaPlaca($placa)
    {
        $pasta = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $placa);
        return $pasta !== '' ? $pasta : 'sem_placa';
    }

    // Localiza o arquivo (pasta nova sanitizada ou pasta antiga com a placa original)
    private function caminhoAnexo($placa, $anexo)
    {
        $anexo = basename($anexo);
        foreach (array_unique([$this->pastaPlaca($placa), (string) $placa]) as $pasta) {
            $caminho = 'public/'.($pasta !== '' ? $pasta.'/' : '').$anexo;
            if (Storage::exists($caminho)) {
                return $caminho;
            }
        }
        return null;
    }
}
