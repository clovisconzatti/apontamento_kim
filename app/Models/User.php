<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\{HasMany,BelongsToMany};

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(menu::class, 'menuUsuario', 'usuarioId', 'menuId')->orderBy('menu.ordem', );
    }

    public static function montarMenu()
    {
        // Tela atual (ex.: "informacao.formEdit" -> "informacao") para destacar o item no menu
        $rotaAtual = (string) \Illuminate\Support\Facades\Route::currentRouteName();
        $moduloAtual = explode('.', $rotaAtual)[0];

        $menu = '<aside>';
            $menu.='<div id="sidebar" class="nav-collapse">';
                $menu.='<ul class="sidebar-menu">';
                    $menu.='<li class="'.($rotaAtual == 'home' ? 'kim-ativo' : '').'">';
                        $menu.='<a class="" href="'.route('home').'">';
                            $menu.='<i class="fas fa-tachometer-alt"></i>';
                            $menu.='<span class="text">Painel</span>';
                        $menu.='</a>';
                    $menu.='</li>';

                    // Monta cada seção (Título + links) separadamente para saber se contém a tela atual
                    $secoes = [];
                    $atual = null;
                    foreach (auth()->user()->menus as $item){
                        if($item->tipo=='Título'){
                            if($atual){
                                $secoes[] = $atual;
                            }
                            $atual = ['titulo' => $item->descricao, 'links' => '', 'aberta' => false];
                        }elseif($item->tipo=='Link' && $item->rota && \Illuminate\Support\Facades\Route::has($item->rota)){
                            $ativo = $moduloAtual !== '' && explode('.', $item->rota)[0] == $moduloAtual;
                            $link  = '<li class="'.($ativo ? 'kim-ativo' : '').'">';
                                $link.=' <a class="" href="'.route($item->rota).'">';
                                    $link.='<i class="'.e($item->icone).'"></i>';
                                    $link.='<span class="text">'.e($item->descricao).'</span>';
                                $link.='</a>';
                            $link.='</li>';
                            if(!$atual){
                                $atual = ['titulo' => null, 'links' => '', 'aberta' => false];
                            }
                            $atual['links'] .= $link;
                            $atual['aberta'] = $atual['aberta'] || $ativo;
                        }
                    }
                    if($atual){
                        $secoes[] = $atual;
                    }

                    foreach ($secoes as $secao){
                        if($secao['titulo'] === null){
                            $menu.=$secao['links'];
                            continue;
                        }
                        $menu.=' <li class="sub-menu">';
                            $menu.='<a href="javascript:;" class="">';
                                $menu.='<i class="fa fa-angle-double-down"></i>';
                                $menu.='<span class="text">'.e($secao['titulo']).'</span>';
                                $menu.='<span class="menu-arrow arrow_carrot-right"></span>';
                            $menu.='</a>';
                            $menu.='<ul class="sub"'.($secao['aberta'] ? ' style="display:block"' : '').'>';
                                $menu.=$secao['links'];
                            $menu.='</ul>';
                        $menu.='</li>';
                    }
                $menu.='</ul>';
            $menu.='</div>';
        $menu .= '</aside>';

        return $menu;
    }

}
