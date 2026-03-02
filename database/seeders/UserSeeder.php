<?php

namespace Database\Seeders;

use App\Models\menu;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    public function run()
    {
        User::truncate();

        User::create([
            'name' => 'Clovis Dorival Conzatti',
            'email' => 'clovis@plannersolucoes.com.br',
            'password' => bcrypt('Cczt4752')
        ]);
        User::create([
            'name' => 'Emily',
            'email' => 'kimlogistica@outlook.com',
            'password' => bcrypt('12345678')
        ]);

    }
}
