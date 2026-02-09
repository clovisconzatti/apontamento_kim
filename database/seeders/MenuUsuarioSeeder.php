<?php

namespace Database\Seeders;

use App\Models\menuUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuUsuarioSeeder extends Seeder
{

    public function run()
    {
        menuUsuario::truncate();
        $id_user = User::get()->first()->id;
        if($id_user){
            $menu = "INSERT menuUsuario(usuarioId, menuId) SELECT $id_user, id FROM menu";
            DB::insert($menu);
        }
    }
}
