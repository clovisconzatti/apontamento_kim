<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\equipamento\EquipamentoController;
use App\Http\Controllers\apontamento\ApontamentoController;
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


});


