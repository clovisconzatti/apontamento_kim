<?php

namespace App\Http\Controllers\colaborador;

use App\Http\Controllers\Controller;
use App\Models\colaborador;
use Illuminate\Http\Request;

class colaboradorController extends Controller
{

    public function listAll(Request $request ){

        $camposFiltro = [
            'busca'   => ['label' => 'Nome ou código', 'tipo' => 'texto', 'col' => 4],
            'empresa' => ['label' => 'Empresa', 'tipo' => 'select', 'opcoes' => $this->opcoesDistintas('colaborador', 'empresa')],
            'uf'      => ['label' => 'UF', 'tipo' => 'select', 'col' => 2, 'opcoes' => $this->opcoesDistintas('colaborador', 'uf')],
            'ativo'   => ['label' => 'Ativo', 'tipo' => 'select', 'col' => 2, 'opcoes' => ['Sim' => 'Sim', 'Nao' => 'Não']],
        ];
        $filtros = $this->lerFiltros($request, 'colaborador', $camposFiltro);

        $query = colaborador::orderBy('colaborador', 'ASC');
        $this->filtrarTexto($query, $filtros['busca'], ['colaborador', 'cod']);
        $this->filtrarIgual($query, $filtros, ['empresa' => 'empresa', 'uf' => 'uf', 'ativo' => 'ativo']);
        $colaboradores = $query->get();
        $totalRegistros = $colaboradores->count();

        return view('colaborador.listAll' , compact('colaboradores', 'camposFiltro', 'filtros', 'totalRegistros'));
    }

    public function formAdd()
    {
        return view('colaborador.add');
    }
    public function strore(Request $request)
    {
        try{
            $colaborador = new colaborador([
                "id"            => $request->id
                ,"colaborador"      => $request->colaborador
                ,"uf"               => $request->uf
                ,"ativo"            => $request->ativo
                ,"cod"              => $request->cod
                ,"empresa"          => $request->empresa
            ]);
            $colaborador->save();
        }catch(\Exception $e){
            return response()->json($colaborador);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $colaborador = colaborador::where('id','=',$id)->first();

        return view('colaborador.edit' , compact('colaborador'));
    }

    public function edit($id, Request $request)
    {
        try{
            $colaborador = colaborador::find($id);
            $colaborador->colaborador		    = $request->colaborador;
            $colaborador->uf		            = $request->uf;
            $colaborador->ativo		            = $request->ativo;
            $colaborador->cod		            = $request->cod;
            $colaborador->empresa		        = $request->empresa;
            $colaborador->save();
        }catch(\Exception $e){
            return response()->json($colaborador);
        }
        return response()->json('success');
    }
}
