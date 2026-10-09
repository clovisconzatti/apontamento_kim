<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Painel inicial: indicadores rápidos e atalhos para as telas liberadas ao usuário.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $inicioMes = date('Y-m-01');

        // Situações que não estão encerradas contam como manutenção em aberto
        $situacoesAbertas = DB::table('situacao_manutencao')
                                ->whereNull('deleted_at')
                                ->where('situacao', 'not like', 'ENCERRAD%')
                                ->pluck('id');

        $indicadores = [
            [
                'valor'  => DB::table('informacao')->whereNull('deleted_at')->where('data', '>=', $inicioMes)->count(),
                'rotulo' => 'Informações diárias no mês',
                'icone'  => 'fas fa-clipboard-list',
                'cor'    => '',
                'rota'   => 'informacao.listAll',
            ],
            [
                'valor'  => DB::table('manutencao')->whereNull('deleted_at')->whereIn('situacao', $situacoesAbertas)->count(),
                'rotulo' => 'Manutenções em aberto',
                'icone'  => 'fas fa-tools',
                'cor'    => 'vermelho',
                'rota'   => 'manutencao.listAll',
            ],
            [
                'valor'  => DB::table('equipamento')->whereNull('deleted_at')->where('ativo', 'Sim')->count(),
                'rotulo' => 'Equipamentos ativos',
                'icone'  => 'fas fa-truck-moving',
                'cor'    => 'escuro',
                'rota'   => 'equipamento.listAll',
            ],
            [
                'valor'  => DB::table('colaborador')->whereNull('deleted_at')->where('ativo', 'Sim')->count(),
                'rotulo' => 'Colaboradores ativos',
                'icone'  => 'fas fa-hard-hat',
                'cor'    => 'amarelo',
                'rota'   => 'colaborador.listAll',
            ],
        ];

        // Atalhos agrupados pelas seções do menu do usuário
        $secoes = [];
        $secaoAtual = 'Acesso rápido';
        foreach (auth()->user()->menus as $item) {
            if ($item->tipo == 'Título') {
                $secaoAtual = $item->descricao;
            } elseif ($item->tipo == 'Link' && $item->rota && Route::has($item->rota)) {
                $secoes[$secaoAtual][] = $item;
            }
        }

        // A seção de apontamento (uso diário) aparece primeiro
        uksort($secoes, function ($a, $b) {
            return (stripos($b, 'apontamento') !== false) <=> (stripos($a, 'apontamento') !== false);
        });

        return view('home', compact('indicadores', 'secoes'));
    }
}
