<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kim Logística</title>
    <link rel="shortcut icon" href="{{ asset('img/icone.png') }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/kim-theme.css') }}?v={{ @filemtime(public_path('css/kim-theme.css')) }}" rel="stylesheet">
</head>
<body class="kim-auth">
    <div id="app">
        @hasSection('telaCheia')
            @yield('content')
        @else
            {{-- Demais telas de autenticação (recuperar senha, verificar e-mail...) --}}
            <nav class="kim-auth-topo">
                <a href="{{ url('/') }}" class="kim-marca" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                    <span class="kim-logo-selo"><img src="{{ asset('img/logo.png') }}" alt="Kim Logística"></span>
                    <span class="kim-marca-texto">
                        <span class="kim-marca-nome">KIM <span>Logística</span></span>
                        <span class="kim-marca-sub">Colheita e transporte de madeira</span>
                    </span>
                </a>
            </nav>
            <main class="py-5" style="background: var(--kim-fundo); min-height: calc(100vh - 70px);">
                @yield('content')
            </main>
        @endif
    </div>
</body>
</html>
