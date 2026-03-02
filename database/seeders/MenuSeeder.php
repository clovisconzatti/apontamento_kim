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
                , 'descricao'   =>'Arrasto'
                , 'tipo'        =>'Link'
                , 'rota'        =>'arrasto.listAll'
                , 'icone'       =>'fas fa-tractor'
            ],
            [
                'ordem'         =>'01.002'
                , 'descricao'   =>'Baldeio'
                , 'tipo'        =>'Link'
                , 'rota'        =>'baldeio.listAll'
                , 'icone'       =>'fas fa-arrow-right-arrow-left'
            ],
            [
                'ordem'         =>'01.003'
                , 'descricao'   =>'Colaborador'
                , 'tipo'        =>'Link'
                , 'rota'        =>'colaborador.listAll'
                , 'icone'       =>'fas fa-id-badge'
            ],
            [
                'ordem'         =>'01.004'
                , 'descricao'   =>'Comboio'
                , 'tipo'        =>'Link'
                , 'rota'        =>'comboio.listAll'
                , 'icone'       =>'fas fa-gas-pump'
            ],
            [
                'ordem'         =>'01.005'
                , 'descricao'   =>'Fazenda'
                , 'tipo'        =>'Link'
                , 'rota'        =>'fazenda.listAll'
                , 'icone'       =>'far fa-map'
            ],
            [
                'ordem'         =>'01.006'
                , 'descricao'   =>'Corte'
                , 'tipo'        =>'Link'
                , 'rota'        =>'corte.listAll'
                , 'icone'       =>'fas fa-tree'
            ],
            [
                'ordem'         =>'01.007'
                , 'descricao'   =>'Compr.Madeira'
                , 'tipo'        =>'Link'
                , 'rota'        =>'comprimento_madeira.listAll'
                , 'icone'       =>'fas fa-ruler-horizontal'
            ],
            [
                'ordem'         =>'01.008'
                , 'descricao'   =>'Fornecedor'
                , 'tipo'        =>'Link'
                , 'rota'        =>'fornecedor.listAll'
                , 'icone'       =>'far fa-address-card'
            ],
            [
                'ordem'         =>'01.009'
                , 'descricao'   =>'Clima'
                , 'tipo'        =>'Link'
                , 'rota'        =>'clima.listAll'
                , 'icone'       =>'fas fa-cloud-sun'
            ],
            [
                'ordem'         =>'01.010'
                , 'descricao'   =>'Terreno'
                , 'tipo'        =>'Link'
                , 'rota'        =>'terreno.listAll'
                , 'icone'       =>'fas fa-tree'
            ],
            [
                'ordem'         =>'02.000'
                , 'descricao'   =>'Veiculos'
                , 'tipo'        =>'Título'
                , 'rota'        =>''
                , 'icone'       =>''
            ],
            [
                'ordem'         =>'02.001'
                , 'descricao'   =>'Equipamento'
                , 'tipo'        =>'Link'
                , 'rota'        =>'equipamento.listAll'
                , 'icone'       =>'fas fa-truck'
            ],
            [
                'ordem'         =>'02.002'
                , 'descricao'   =>'Tipo Veiculos'
                , 'tipo'        =>'Link'
                , 'rota'        =>'tipo.listAll'
                , 'icone'       =>'fas fa-snowplow'
            ],
            [
                'ordem'         =>'02.003'
                , 'descricao'   =>'Operaçoes'
                , 'tipo'        =>'Link'
                , 'rota'        =>'operacao.listAll'
                , 'icone'       =>'far fa-clipboard'
            ],
            [
                'ordem'         =>'02.004'
                , 'descricao'   =>'Situação Manut'
                , 'tipo'        =>'Link'
                , 'rota'        =>'situacao_manutencao.listAll'
                , 'icone'       =>'fas fa-tools'
            ],
            [
                'ordem'         =>'02.005'
                , 'descricao'   =>'Tipo Manut'
                , 'tipo'        =>'Link'
                , 'rota'        =>'tipo_manutencao.listAll'
                , 'icone'       =>'far fa-sticky-note'
            ],
            [
                'ordem'         =>'02.006'
                , 'descricao'   =>'Combustiveis'
                , 'tipo'        =>'Link'
                , 'rota'        =>'tipo_combustivel.listAll'
                , 'icone'       =>'fas fa-gas-pump'
            ],
            [
                'ordem'         =>'02.007'
                , 'descricao'   =>'Lubrificantes'
                , 'tipo'        =>'Link'
                , 'rota'        =>'lubrificante.listAll'
                , 'icone'       =>'fas fa-flask'
            ],
            [
                'ordem'         =>'02.008'
                , 'descricao'   =>'Peças'
                , 'tipo'        =>'Link'
                , 'rota'        =>'peca.listAll'
                , 'icone'       =>'fas fa-wrench'
            ],
            [
                'ordem'         =>'02.009'
                , 'descricao'   =>'Atividades'
                , 'tipo'        =>'Link'
                , 'rota'        =>'atividade.listAll'
                , 'icone'       =>'far fa-keyboard'
            ],
            [
                'ordem'         =>'03.000'
                , 'descricao'   =>'Apontamento'
                , 'tipo'        =>'Título'
                , 'rota'        =>''
                , 'icone'       =>''
            ],
            [
                'ordem'         =>'03.001'
                , 'descricao'   =>'Abastecimento'
                , 'tipo'        =>'Link'
                , 'rota'        =>'apontamento.listAll'
                , 'icone'       =>'fas fa-gas-pump'
            ],
            [
                'ordem'         =>'03.002'
                , 'descricao'   =>'Transferencia'
                , 'tipo'        =>'Link'
                , 'rota'        =>'transferencia.listAll'
                , 'icone'       =>'fas fa-arrow-right-arrow-left'
            ],
        ];
        menu::insert($menus);
    }

}
