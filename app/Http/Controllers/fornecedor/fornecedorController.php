<?php

namespace App\Http\Controllers\fornecedor;

use App\Http\Controllers\Controller;
use App\Models\fornecedor;
use Illuminate\Http\Request;

class fornecedorController extends Controller
{

    public function listAll(Request $request ){

        $fornecedores = fornecedor::orderBy('fornecedor', 'ASC')->get();
        return view('fornecedor.listAll' , compact('fornecedores'));
    }

    public function formAdd()
    {
        return view('fornecedor.add');
    }
    public function strore(Request $request)
    {
        try{
            $fornecedor = new fornecedor([
                "id"            => $request->id
                ,"fornecedor"        => $request->fornecedor
                ,"cod_cargo"         => $request->cod_cargo
            ]);
            $fornecedor->save();
        }catch(\Exception $e){
            return response()->json($fornecedor);
        }
        return response()->json('success');
    }

    public function formEdit($id)
    {
        $fornecedores = fornecedor::where('id','=',$id)->first();

        return view('fornecedor.edit' , compact('fornecedores'));
    }

    public function edit($id, Request $request)
    {
        try{
            $fornecedor = fornecedor::find($id);
            $fornecedor->fornecedor		    = $request->fornecedor;
            $fornecedor->cod_cargo		    = $request->cod_cargo;
            $fornecedor->save();
        }catch(\Exception $e){
            return response()->json($fornecedor);
        }
        return response()->json('success');
    }
}
