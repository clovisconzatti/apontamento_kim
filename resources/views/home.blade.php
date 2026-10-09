@extends('layouts.model')

@section('content')
@php
    $meses = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    $dias  = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
    $hora  = (int) date('H');
    $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
    $primeiroNome = explode(' ', trim(Auth::user()->name))[0];
@endphp

<div class="kim-hero">
    <h1>{{ $saudacao }}, <span>{{ $primeiroNome }}</span></h1>
    <p>Sistema de apontamento da Kim Logística — colheita, baldeio e transporte de madeira.</p>
    <span class="kim-hero-data">
        <i class="far fa-calendar-alt"></i>
        {{ $dias[date('w')] }}, {{ date('j') }} de {{ $meses[(int) date('n')] }} de {{ date('Y') }}
    </span>
</div>

<div class="row mt-4">
    @foreach ($indicadores as $indicador)
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            @if (Route::has($indicador['rota']))
                <a href="{{ route($indicador['rota']) }}" style="text-decoration: none;">
            @endif
                <div class="kim-indicador {{ $indicador['cor'] }}">
                    <div class="kim-indicador-icone"><i class="{{ $indicador['icone'] }}"></i></div>
                    <div>
                        <div class="kim-indicador-valor">{{ number_format($indicador['valor'], 0, ',', '.') }}</div>
                        <div class="kim-indicador-rotulo">{{ $indicador['rotulo'] }}</div>
                    </div>
                </div>
            @if (Route::has($indicador['rota']))
                </a>
            @endif
        </div>
    @endforeach
</div>

@foreach ($secoes as $titulo => $links)
    <div class="kim-secao-titulo">{{ $titulo }}</div>
    <div class="row">
        @foreach ($links as $link)
            <div class="col-12 col-sm-6 col-lg-3">
                <a class="kim-atalho" href="{{ route($link->rota) }}">
                    <i class="{{ $link->icone ?: 'fas fa-angle-right' }}"></i>
                    <span>{{ $link->descricao }}</span>
                </a>
            </div>
        @endforeach
    </div>
@endforeach
@endsection
