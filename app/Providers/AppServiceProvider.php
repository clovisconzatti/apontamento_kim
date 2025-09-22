<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use illuminate\Support\Facades\View;
use App\Models\User;

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
        view()->composer('layouts.model', function ($view) {
            $menu = User::montarMenu();
            $view->with([
                "menu"=>$menu
            ]);
        });
    }
}
