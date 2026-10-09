<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use illuminate\Support\Facades\View;
use App\Models\User;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        view()->composer('layouts.model', function ($view) {
            $menu = User::montarMenu();
            $view->with([
                "menu"=>$menu
            ]);
        });
    }
}
