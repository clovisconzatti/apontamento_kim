<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\menu;
use App\Models\menuUsuario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

class MenuController extends Controller
{
    public function listAllmenu(Request $request ){

        $menus = menu::orderBy('ordem', 'ASC')->get();
        return view('menu.listAllMenu' , compact('menus'));
    }

    public function formAddmenu()
    {
        return view('menu.addmenu');
    }
    public function stroremenu(Request $request)
    {
        try{
            $menu = new menu([
                "descricao"     => $request->descricao
                ,"ordem"        => $request->ordem
                ,"tipo"         => $request->tipo
                ,"rota"         => $request->rota
                ,"icone"        => $request->icone
            ]);
            $menu->save();
        }catch(\Exception $e){
            return response()->json($menu);
        }
        return response()->json('success');
    }

    public function formEditmenu($id)
    {
        $menu = menu::where('id','=',$id)->first();

        return view('menu.editmenu' , compact('menu'));
    }

    public function edit($id, Request $request)
    {
        try{
            $menu = menu::find($id);
            $menu->descricao    = $request->descricao;
            $menu->ordem		= $request->ordem;
            $menu->tipo		    = $request->tipo;
            $menu->rota		    = $request->rota;
            $menu->icone	    = $request->icone;
            $menu->save();
        }catch(\Exception $e){
            return response()->json($menu);
        }
        return response()->json('success');
    }

    public function menuUsuario(Request $request)
    {
        $usuarios = User::orderBy('name')->get();
        $menuDisponivel = menuUsuario::leftJoin('menu','menu.id','=','menuUsuario.menuId')
                                        ->get([
                                            'menu.ordem'
                                            ,'menu.descricao'
                                            ,'menu.tipo'
                                        ]);


        return view('menu.menuUsuario', compact('usuarios','menuDisponivel'));
    }

    public function disponivel (Request $request)
    {
        $usuario = $request->usuario;
        $sql = "
                SELECT
                    menu.ordem
                    ,menu.descricao
                    ,menu.tipo
                    ,menu.id as disponivelId
                    ,menuUsuario.id as selecionadoId
                    ,CASE
                        WHEN coalesce(menuUsuario.id,0)>0 THEN 'checked'
                        ELSE ''
                    END AS selecionado

                FROM menu
                LEFT JOIN menuUsuario ON menuUsuario.menuId = menu.id AND menuUsuario.usuarioId = $usuario
                order by menu.ordem
        ";
        $itens = DB::select($sql);
        $menuDisponivel=[];
        foreach($itens as $item)
        {
            $menuDisponivel[]=array(
                "ordem"         => $item->ordem,
                "descricao"     => $item->descricao,
                "tipo"          => $item->tipo,
                "disponivelId"  => $item->disponivelId,
                "selecionadoId" => $item->selecionadoId,
                "selecionado"   => $item->selecionado
            );
        };
        return response()->json($menuDisponivel);
    }


    public function menuLiberado (Request $request)
    {
        $usuario = $request->usuario;
        $menuLiberado = menuUsuario::leftJoin('menu','menu.id','=','menuUsuario.menuId')
                                    ->where('usuarioId','=',$usuario)
                                    ->orderBy('ordem')
                                    ->get([
                                        'menu.ordem'
                                        ,'menu.descricao'
                                        ,'menu.tipo'
                                        ,'menuUsuario.id as liberadoId'
                                    ]);
        return response()->json($menuLiberado);

    }

    public function addMenuUsuario (Request $request)
    {
        try{
            $menuUsuario = new menuUsuario([
                "usuarioId"     => $request->usuario
                , "menuId"      => $request->disponivelId
            ]);
            $menuUsuario->save();
        }catch(\Exception $e){
            return response()->json($menuUsuario);
        }
        return response()->json('success');
    }

    public function removeMenuUsuario (Request $request)
    {
        $liberadoId = $request->liberadoId;
        try{
            $sql = "DELETE FROM menuUsuario WHERE id = $liberadoId";
            $menuUsuario = DB::delete($sql);
        }catch(\Exception $e){
            return response()->json($menuUsuario);
        }
        return response()->json('success');
    }
}
