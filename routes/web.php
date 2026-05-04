<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\equipamento\EquipamentoController;
use App\Http\Controllers\apontamento\ApontamentoController;
use App\Http\Controllers\arrasto\arrastoController;
use App\Http\Controllers\atividade\atividadeController;
use App\Http\Controllers\baldeio\baldeioController;
use App\Http\Controllers\clima\climaController;
use App\Http\Controllers\colaborador\colaboradorController;
use App\Http\Controllers\comboio\comboioController;
use App\Http\Controllers\comprimento_madeira\comprimento_madeiraController;
use App\Http\Controllers\corte\corteController;
use App\Http\Controllers\fazenda\fazendaController;
use App\Http\Controllers\fornecedor\fornecedorController;
use App\Http\Controllers\informacao\informacaoController;
use App\Http\Controllers\lubrificante\lubrificanteController;
use App\Http\Controllers\manutencao\manutencaoController;
use App\Http\Controllers\operacao\operacaoController;
use App\Http\Controllers\peca\pecaController;
use App\Http\Controllers\situacao_manutencao\situacao_manutencaoController;
use App\Http\Controllers\terreno\terrenoController;
use App\Http\Controllers\tipo\tipoController;
use App\Http\Controllers\tipo_combustivel\tipo_combustivelController;
use App\Http\Controllers\tipo_manutencao\tipo_manutencaoController;
use App\Http\Controllers\transferencia\transferenciaController;
use Illuminate\Support\Facades\Auth;

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', function () {
    return view('auth/login');
});
Route::group(['middleware' => ['auth']], function () {

    Route::get('/',[HomeController::class,'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/public', [HomeController::class, 'index']);

    /********************************** menu ***************************************************************/
    Route::group(['namespace' => 'menu'], function () {
        Route::get('menu',[MenuController::class,'listAllmenu'])->name('menu.listAll');
        Route::get('menu/novo',[MenuController::class,'formAddmenu'])->name('menu.formAddmenu');
        Route::get('menu/editar/{menu}',[MenuController::class,'formEditmenu'])->name('menu.formEditmenu');
        Route::post('menu/store',[MenuController::class,'stroremenu'])->name('menu.store');
        Route::patch('menu/edit/{menu}',[MenuController::class,'edit'])->name('menu.edit');
        Route::delete('menu/destroy/{menu}',[MenuController::class,'destroy'])->name('menu.destroy');

        Route::get('menu/menuUsuario',[MenuController::class,'menuUsuario'])->name('menu.menuUsuario');
        Route::post('menu/disponivel',[MenuController::class,'disponivel'])->name('menu.disponivel');
        Route::post('menu/menuLiberado',[MenuController::class,'menuLiberado'])->name('menu.menuLiberado');

        Route::post('menu/addMenuUsuario',[MenuController::class,'addMenuUsuario'])->name('menu.addMenuUsuario');
        Route::post('menu/removeMenuUsuario',[MenuController::class,'removeMenuUsuario'])->name('menu.removeMenuUsuario');

    });


    /********************************** equipamento ***************************************************************/
    Route::group(['namespace' => 'equipamento'], function () {
        Route::get('equipamento',[equipamentoController::class,'listAll'])->name('equipamento.listAll');
        Route::get('equipamento/novo',[equipamentoController::class,'formAdd'])->name('equipamento.formAdd');
        Route::get('equipamento/editar/{equipamento}',[equipamentoController::class,'formEdit'])->name('equipamento.formEdit');
        Route::post('equipamento/store',[equipamentoController::class,'strore'])->name('equipamento.store');
        Route::patch('equipamento/edit/{equipamento}',[equipamentoController::class,'edit'])->name('equipamento.edit');
        Route::delete('equipamento/destroy/{equipamento}',[equipamentoController::class,'destroy'])->name('equipamento.destroy');
    });

    /********************************** apontamento ***************************************************************/
    Route::group(['namespace' => 'apontamento'], function () {
        Route::get('apontamento',[apontamentoController::class,'listAll'])->name('apontamento.listAll');
        Route::get('apontamento/novo',[apontamentoController::class,'formAdd'])->name('apontamento.formAdd');
        Route::get('apontamento/editar/{apontamento}',[apontamentoController::class,'formEdit'])->name('apontamento.formEdit');
        Route::post('apontamento/store',[apontamentoController::class,'strore'])->name('apontamento.store');
        Route::patch('apontamento/edit/{apontamento}',[apontamentoController::class,'edit'])->name('apontamento.edit');
        // Route::delete('apontamento/destroy/{apontamento}',[apontamentoController::class,'destroy'])->name('apontamento.destroy');

        Route::post('apontamento/checaKm',[apontamentoController::class,'checaKm'])->name('apontamento.checaKm');
        Route::post('apontamento/checaHora',[apontamentoController::class,'checaHora'])->name('apontamento.checaHora');

        Route::get('apontamento/apontamentoAnexo/{apontamento}',[apontamentoController::class,'apontamentoAnexo'])->name('apontamento.apontamentoAnexo');
        Route::post('apontamento/upload',[apontamentoController::class,'upload'])->name('upload');

    });
     /********************************** transferencia ***************************************************************/
    Route::group(['namespace' => 'transferencia'], function () {
        Route::get('transferencia',[transferenciaController::class,'listAll'])->name('transferencia.listAll');
        Route::get('transferencia/novo',[transferenciaController::class,'formAdd'])->name('transferencia.formAdd');
        Route::get('transferencia/editar/{transferencia}',[transferenciaController::class,'formEdit'])->name('transferencia.formEdit');
        Route::post('transferencia/store',[transferenciaController::class,'strore'])->name('transferencia.store');
        Route::patch('transferencia/edit/{transferencia}',[transferenciaController::class,'edit'])->name('transferencia.edit');
        Route::delete('transferencia/destroy/{transferencia}',[transferenciaController::class,'destroy'])->name('transferencia.destroy');
    });

     /********************************** arrasto ***************************************************************/
    Route::group(['namespace' => 'arrasto'], function () {
        Route::get('arrasto',[arrastoController::class,'listAll'])->name('arrasto.listAll');
        Route::get('arrasto/novo',[arrastoController::class,'formAdd'])->name('arrasto.formAdd');
        Route::get('arrasto/editar/{arrasto}',[arrastoController::class,'formEdit'])->name('arrasto.formEdit');
        Route::post('arrasto/store',[arrastoController::class,'strore'])->name('arrasto.store');
        Route::patch('arrasto/edit/{arrasto}',[arrastoController::class,'edit'])->name('arrasto.edit');
        Route::delete('arrasto/destroy/{arrasto}',[arrastoController::class,'destroy'])->name('arrasto.destroy');
    });

     /********************************** baldeio ***************************************************************/
    Route::group(['namespace' => 'baldeio'], function () {
        Route::get('baldeio',[baldeioController::class,'listAll'])->name('baldeio.listAll');
        Route::get('baldeio/novo',[baldeioController::class,'formAdd'])->name('baldeio.formAdd');
        Route::get('baldeio/editar/{baldeio}',[baldeioController::class,'formEdit'])->name('baldeio.formEdit');
        Route::post('baldeio/store',[baldeioController::class,'strore'])->name('baldeio.store');
        Route::patch('baldeio/edit/{baldeio}',[baldeioController::class,'edit'])->name('baldeio.edit');
        Route::delete('baldeio/destroy/{baldeio}',[baldeioController::class,'destroy'])->name('baldeio.destroy');
    });

     /********************************** colaborador ***************************************************************/
    Route::group(['namespace' => 'colaborador'], function () {
        Route::get('colaborador',[colaboradorController::class,'listAll'])->name('colaborador.listAll');
        Route::get('colaborador/novo',[colaboradorController::class,'formAdd'])->name('colaborador.formAdd');
        Route::get('colaborador/editar/{colaborador}',[colaboradorController::class,'formEdit'])->name('colaborador.formEdit');
        Route::post('colaborador/store',[colaboradorController::class,'strore'])->name('colaborador.store');
        Route::patch('colaborador/edit/{colaborador}',[colaboradorController::class,'edit'])->name('colaborador.edit');
        Route::delete('colaborador/destroy/{colaborador}',[colaboradorController::class,'destroy'])->name('colaborador.destroy');
    });

      /********************************** comboio ***************************************************************/
    Route::group(['namespace' => 'comboio'], function () {
        Route::get('comboio',[comboioController::class,'listAll'])->name('comboio.listAll');
        Route::get('comboio/novo',[comboioController::class,'formAdd'])->name('comboio.formAdd');
        Route::get('comboio/editar/{comboio}',[comboioController::class,'formEdit'])->name('comboio.formEdit');
        Route::post('comboio/store',[comboioController::class,'strore'])->name('comboio.store');
        Route::patch('comboio/edit/{comboio}',[comboioController::class,'edit'])->name('comboio.edit');
        Route::delete('comboio/destroy/{comboio}',[comboioController::class,'destroy'])->name('comboio.destroy');
    });

      /********************************** fazenda ***************************************************************/
    Route::group(['namespace' => 'fazenda'], function () {
        Route::get('fazenda',[fazendaController::class,'listAll'])->name('fazenda.listAll');
        Route::get('fazenda/novo',[fazendaController::class,'formAdd'])->name('fazenda.formAdd');
        Route::get('fazenda/editar/{fazenda}',[fazendaController::class,'formEdit'])->name('fazenda.formEdit');
        Route::post('fazenda/store',[fazendaController::class,'strore'])->name('fazenda.store');
        Route::patch('fazenda/edit/{fazenda}',[fazendaController::class,'edit'])->name('fazenda.edit');
        Route::delete('fazenda/destroy/{fazenda}',[fazendaController::class,'destroy'])->name('fazenda.destroy');
    });

     /********************************** corte ***************************************************************/
    Route::group(['namespace' => 'corte'], function () {
        Route::get('corte',[corteController::class,'listAll'])->name('corte.listAll');
        Route::get('corte/novo',[corteController::class,'formAdd'])->name('corte.formAdd');
        Route::get('corte/editar/{corte}',[corteController::class,'formEdit'])->name('corte.formEdit');
        Route::post('corte/store',[corteController::class,'strore'])->name('corte.store');
        Route::patch('corte/edit/{corte}',[corteController::class,'edit'])->name('corte.edit');
        Route::delete('corte/destroy/{corte}',[corteController::class,'destroy'])->name('corte.destroy');
    });

    /********************************** comprimento_madeira ***************************************************************/
    Route::group(['namespace' => 'comprimento_madeira'], function () {
        Route::get('comprimento_madeira',[comprimento_madeiraController::class,'listAll'])->name('comprimento_madeira.listAll');
        Route::get('comprimento_madeira/novo',[comprimento_madeiraController::class,'formAdd'])->name('comprimento_madeira.formAdd');
        Route::get('comprimento_madeira/editar/{comprimento_madeira}',[comprimento_madeiraController::class,'formEdit'])->name('comprimento_madeira.formEdit');
        Route::post('comprimento_madeira/store',[comprimento_madeiraController::class,'strore'])->name('comprimento_madeira.store');
        Route::patch('comprimento_madeira/edit/{comprimento_madeira}',[comprimento_madeiraController::class,'edit'])->name('comprimento_madeira.edit');
        Route::delete('comprimento_madeira/destroy/{comprimento_madeira}',[comprimento_madeiraController::class,'destroy'])->name('comprimento_madeira.destroy');
    });

    /********************************** fornecedor ***************************************************************/
    Route::group(['namespace' => 'fornecedor'], function () {
        Route::get('fornecedor',[fornecedorController::class,'listAll'])->name('fornecedor.listAll');
        Route::get('fornecedor/novo',[fornecedorController::class,'formAdd'])->name('fornecedor.formAdd');
        Route::get('fornecedor/editar/{fornecedor}',[fornecedorController::class,'formEdit'])->name('fornecedor.formEdit');
        Route::post('fornecedor/store',[fornecedorController::class,'strore'])->name('fornecedor.store');
        Route::patch('fornecedor/edit/{fornecedor}',[fornecedorController::class,'edit'])->name('fornecedor.edit');
        Route::delete('fornecedor/destroy/{fornecedor}',[fornecedorController::class,'destroy'])->name('fornecedor.destroy');
    });
    /********************************** lubrificante ***************************************************************/
    Route::group(['namespace' => 'lubrificante'], function () {
        Route::get('lubrificante',[lubrificanteController::class,'listAll'])->name('lubrificante.listAll');
        Route::get('lubrificante/novo',[lubrificanteController::class,'formAdd'])->name('lubrificante.formAdd');
        Route::get('lubrificante/editar/{lubrificante}',[lubrificanteController::class,'formEdit'])->name('lubrificante.formEdit');
        Route::post('lubrificante/store',[lubrificanteController::class,'strore'])->name('lubrificante.store');
        Route::patch('lubrificante/edit/{lubrificante}',[lubrificanteController::class,'edit'])->name('lubrificante.edit');
        Route::delete('lubrificante/destroy/{lubrificante}',[lubrificanteController::class,'destroy'])->name('lubrificante.destroy');
    });
    /********************************** peca ***************************************************************/
    Route::group(['namespace' => 'peca'], function () {
        Route::get('peca',[pecaController::class,'listAll'])->name('peca.listAll');
        Route::get('peca/novo',[pecaController::class,'formAdd'])->name('peca.formAdd');
        Route::get('peca/editar/{peca}',[pecaController::class,'formEdit'])->name('peca.formEdit');
        Route::post('peca/store',[pecaController::class,'strore'])->name('peca.store');
        Route::patch('peca/edit/{peca}',[pecaController::class,'edit'])->name('peca.edit');
        Route::delete('peca/destroy/{peca}',[pecaController::class,'destroy'])->name('peca.destroy');
    });
    /********************************** situacao_manutencao ***************************************************************/
    Route::group(['namespace' => 'situacao_manutencao'], function () {
        Route::get('situacao_manutencao',[situacao_manutencaoController::class,'listAll'])->name('situacao_manutencao.listAll');
        Route::get('situacao_manutencao/novo',[situacao_manutencaoController::class,'formAdd'])->name('situacao_manutencao.formAdd');
        Route::get('situacao_manutencao/editar/{situacao_manutencao}',[situacao_manutencaoController::class,'formEdit'])->name('situacao_manutencao.formEdit');
        Route::post('situacao_manutencao/store',[situacao_manutencaoController::class,'strore'])->name('situacao_manutencao.store');
        Route::patch('situacao_manutencao/edit/{situacao_manutencao}',[situacao_manutencaoController::class,'edit'])->name('situacao_manutencao.edit');
        Route::delete('situacao_manutencao/destroy/{situacao_manutencao}',[situacao_manutencaoController::class,'destroy'])->name('situacao_manutencao.destroy');
    });
    /********************************** tipo_manutencao ***************************************************************/
    Route::group(['namespace' => 'tipo_manutencao'], function () {
        Route::get('tipo_manutencao',[tipo_manutencaoController::class,'listAll'])->name('tipo_manutencao.listAll');
        Route::get('tipo_manutencao/novo',[tipo_manutencaoController::class,'formAdd'])->name('tipo_manutencao.formAdd');
        Route::get('tipo_manutencao/editar/{tipo_manutencao}',[tipo_manutencaoController::class,'formEdit'])->name('tipo_manutencao.formEdit');
        Route::post('tipo_manutencao/store',[tipo_manutencaoController::class,'strore'])->name('tipo_manutencao.store');
        Route::patch('tipo_manutencao/edit/{tipo_manutencao}',[tipo_manutencaoController::class,'edit'])->name('tipo_manutencao.edit');
        Route::delete('tipo_manutencao/destroy/{tipo_manutencao}',[tipo_manutencaoController::class,'destroy'])->name('tipo_manutencao.destroy');
    });
/********************************** operacao ***************************************************************/
    Route::group(['namespace' => 'operacao'], function () {
        Route::get('operacao',[operacaoController::class,'listAll'])->name('operacao.listAll');
        Route::get('operacao/novo',[operacaoController::class,'formAdd'])->name('operacao.formAdd');
        Route::get('operacao/editar/{operacao}',[operacaoController::class,'formEdit'])->name('operacao.formEdit');
        Route::post('operacao/store',[operacaoController::class,'strore'])->name('operacao.store');
        Route::patch('operacao/edit/{operacao}',[operacaoController::class,'edit'])->name('operacao.edit');
        Route::delete('operacao/destroy/{operacao}',[operacaoController::class,'destroy'])->name('operacao.destroy');
    });
/********************************** tipo ***************************************************************/
    Route::group(['namespace' => 'tipo'], function () {
        Route::get('tipo',[tipoController::class,'listAll'])->name('tipo.listAll');
        Route::get('tipo/novo',[tipoController::class,'formAdd'])->name('tipo.formAdd');
        Route::get('tipo/editar/{tipo}',[tipoController::class,'formEdit'])->name('tipo.formEdit');
        Route::post('tipo/store',[tipoController::class,'strore'])->name('tipo.store');
        Route::patch('tipo/edit/{tipo}',[tipoController::class,'edit'])->name('tipo.edit');
        Route::delete('tipo/destroy/{tipo}',[tipoController::class,'destroy'])->name('tipo.destroy');
    });
    /********************************** atividade ***************************************************************/
    Route::group(['namespace' => 'atividade'], function () {
        Route::get('atividade',[atividadeController::class,'listAll'])->name('atividade.listAll');
        Route::get('atividade/novo',[atividadeController::class,'formAdd'])->name('atividade.formAdd');
        Route::get('atividade/editar/{atividade}',[atividadeController::class,'formEdit'])->name('atividade.formEdit');
        Route::post('atividade/store',[atividadeController::class,'strore'])->name('atividade.store');
        Route::patch('atividade/edit/{atividade}',[atividadeController::class,'edit'])->name('atividade.edit');
        Route::delete('atividade/destroy/{atividade}',[atividadeController::class,'destroy'])->name('atividade.destroy');
    });
     /********************************** tipo_combustivel ***************************************************************/
    Route::group(['namespace' => 'tipo_combustivel'], function () {
        Route::get('tipo_combustivel',[tipo_combustivelController::class,'listAll'])->name('tipo_combustivel.listAll');
        Route::get('tipo_combustivel/novo',[tipo_combustivelController::class,'formAdd'])->name('tipo_combustivel.formAdd');
        Route::get('tipo_combustivel/editar/{tipo_combustivel}',[tipo_combustivelController::class,'formEdit'])->name('tipo_combustivel.formEdit');
        Route::post('tipo_combustivel/store',[tipo_combustivelController::class,'strore'])->name('tipo_combustivel.store');
        Route::patch('tipo_combustivel/edit/{tipo_combustivel}',[tipo_combustivelController::class,'edit'])->name('tipo_combustivel.edit');
        Route::delete('tipo_combustivel/destroy/{tipo_combustivel}',[tipo_combustivelController::class,'destroy'])->name('tipo_combustivel.destroy');
    });
      /********************************** clima ***************************************************************/
    Route::group(['namespace' => 'clima'], function () {
        Route::get('clima',[climaController::class,'listAll'])->name('clima.listAll');
        Route::get('clima/novo',[climaController::class,'formAdd'])->name('clima.formAdd');
        Route::get('clima/editar/{clima}',[climaController::class,'formEdit'])->name('clima.formEdit');
        Route::post('clima/store',[climaController::class,'strore'])->name('clima.store');
        Route::patch('clima/edit/{clima}',[climaController::class,'edit'])->name('clima.edit');
        Route::delete('clima/destroy/{clima}',[climaController::class,'destroy'])->name('clima.destroy');
    });
  /********************************** terreno ***************************************************************/
    Route::group(['namespace' => 'terreno'], function () {
        Route::get('terreno',[terrenoController::class,'listAll'])->name('terreno.listAll');
        Route::get('terreno/novo',[terrenoController::class,'formAdd'])->name('terreno.formAdd');
        Route::get('terreno/editar/{terreno}',[terrenoController::class,'formEdit'])->name('terreno.formEdit');
        Route::post('terreno/store',[terrenoController::class,'strore'])->name('terreno.store');
        Route::patch('terreno/edit/{terreno}',[terrenoController::class,'edit'])->name('terreno.edit');
        Route::delete('terreno/destroy/{terreno}',[terrenoController::class,'destroy'])->name('terreno.destroy');
    });
 /********************************** informacao diária ***************************************************************/
    Route::group(['namespace' => 'informacao'], function () {
        Route::get('informacao',[informacaoController::class,'listAll'])->name('informacao.listAll');
        Route::get('informacao/novo',[informacaoController::class,'formAdd'])->name('informacao.formAdd');
        Route::get('informacao/editar/{informacao}',[informacaoController::class,'formEdit'])->name('informacao.formEdit');
        Route::post('informacao/store',[informacaoController::class,'store'])->name('informacao.store');
        Route::patch('informacao/edit/{informacao}',[informacaoController::class,'edit'])->name('informacao.edit');
        Route::delete('informacao/destroy/{informacao}',[informacaoController::class,'destroy'])->name('informacao.destroy');
    });
 /********************************** informação de manutencao ***************************************************************/
    Route::group(['namespace' => 'manutencao'], function () {
        Route::get('manutencao',[manutencaoController::class,'listAll'])->name('manutencao.listAll');
        Route::get('manutencao/novo',[manutencaoController::class,'formAdd'])->name('manutencao.formAdd');
        Route::get('manutencao/editar/{manutencao}',[manutencaoController::class,'formEdit'])->name('manutencao.formEdit');
        Route::post('manutencao/store',[manutencaoController::class,'store'])->name('manutencao.store');
        Route::patch('manutencao/edit/{manutencao}',[manutencaoController::class,'edit'])->name('manutencao.edit');
        Route::delete('manutencao/destroy/{manutencao}',[manutencaoController::class,'destroy'])->name('manutencao.destroy');
    });

});


