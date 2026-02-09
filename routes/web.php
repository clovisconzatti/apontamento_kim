<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\equipamento\EquipamentoController;
use App\Http\Controllers\apontamento\ApontamentoController;
use App\Http\Controllers\arrasto\arrastoController;
use App\Http\Controllers\baldeio\baldeioController;
use App\Http\Controllers\colaborador\colaboradorController;
use App\Http\Controllers\comboio\comboioController;
use App\Http\Controllers\comprimento_madeira\comprimento_madeiraController;
use App\Http\Controllers\corte\corteController;
use App\Http\Controllers\fazenda\fazendaController;
use App\Http\Controllers\fornecedor\fornecedorController;
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

});


