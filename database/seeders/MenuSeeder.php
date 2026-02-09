<?php

namespace Database\Seeders;

use App\Models\menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        menu::truncate();
        $menus=[
            [
                'ordem'         =>'01.000'
                , 'descricao'   =>'Cadastro'
                , 'tipo'        =>'Título'
                , 'rota'        =>''
                , 'icone'       =>''
            ],
             [
                'ordem'         =>'01.001'
                , 'descricao'   =>'Equipamento'
                , 'tipo'        =>'Link'
                , 'rota'        =>'equipamento.listAll'
                , 'icone'       =>'fas fa-cubes'
            ],
            [
                'ordem'         =>'01.002'
                , 'descricao'   =>'Arrasto'
                , 'tipo'        =>'Link'
                , 'rota'        =>'arrasto.listAll'
                , 'icone'       =>'fas fa-tractor'
            ],
            [
                'ordem'         =>'01.003'
                , 'descricao'   =>'Baldeio'
                , 'tipo'        =>'Link'
                , 'rota'        =>'baldeio.listAll'
                , 'icone'       =>'fas fa-arrow-right-arrow-left'
            ],
             [
                'ordem'         =>'01.004'
                , 'descricao'   =>'Colaborador'
                , 'tipo'        =>'Link'
                , 'rota'        =>'colaborador.listAll'
                , 'icone'       =>'fas fa-id-badge'
            ],
            [
                'ordem'         =>'01.005'
                , 'descricao'   =>'Comboio'
                , 'tipo'        =>'Link'
                , 'rota'        =>'comboio.listAll'
                , 'icone'       =>'fas fa-gas-pump'
            ],
             [
                'ordem'         =>'01.006'
                , 'descricao'   =>'Fazenda'
                , 'tipo'        =>'Link'
                , 'rota'        =>'fazenda.listAll'
                , 'icone'       =>'far fa-map'
            ],
            [
                'ordem'         =>'01.007'
                , 'descricao'   =>'Corte'
                , 'tipo'        =>'Link'
                , 'rota'        =>'corte.listAll'
                , 'icone'       =>'far fa-map'
            ],
             [
                'ordem'         =>'01.008'
                , 'descricao'   =>'Compr.Madeira'
                , 'tipo'        =>'Link'
                , 'rota'        =>'comprimento_madeira.listAll'
                , 'icone'       =>'far fa-map'
            ],
            [
                'ordem'         =>'01.009'
                , 'descricao'   =>'Fornecedor'
                , 'tipo'        =>'Link'
                , 'rota'        =>'fornecedor.listAll'
                , 'icone'       =>'far fa-map'
            ],
            [
                'ordem'         =>'02.000'
                , 'descricao'   =>'Apontamento'
                , 'tipo'        =>'Título'
                , 'rota'        =>''
                , 'icone'       =>''
            ],
            [
                'ordem'         =>'02.001'
                , 'descricao'   =>'Abastecimento'
                , 'tipo'        =>'Link'
                , 'rota'        =>'apontamento.listAll'
                , 'icone'       =>'fas fa-gas-pump'
            ],
            [
                'ordem'         =>'02.002'
                , 'descricao'   =>'Transferencia'
                , 'tipo'        =>'Link'
                , 'rota'        =>'transferencia.listAll'
                , 'icone'       =>'fas fa-arrows-alt-'
            ],
        ];
        menu::insert($menus);
    }

}
