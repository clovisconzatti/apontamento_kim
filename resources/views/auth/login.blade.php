@extends('layouts.app')

@section('telaCheia', true)

@section('content')
<div class="kim-login">
    <div class="kim-login-foto">
        <h2>Colheita e transporte de madeira com <span>controle de ponta a ponta</span></h2>
        <p>Apontamento diário, abastecimento, manutenção e transferências da frota em um só lugar.</p>
    </div>

    <div class="kim-login-painel">
        <div class="kim-login-caixa">
            <img class="kim-login-logo" src="{{ asset('img/logo.png') }}" alt="Kim Logística">
            <h1>Acesse sua conta</h1>
            <div class="kim-login-sub">Sistema de apontamento Kim Logística</div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                           value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="seu.email@empresa.com.br">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message == trans('auth.failed') || $message == 'These credentials do not match our records.' ? 'E-mail ou senha incorretos.' : $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                           required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember" style="font-weight: 500;">Manter conectado</label>
                </div>

                <button type="submit" class="btn btn-entrar">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>

            <div class="kim-login-rodape">
                &copy; {{ date('Y') }} Kim Logística
            </div>
        </div>
    </div>
</div>
@endsection
