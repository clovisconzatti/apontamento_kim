{{--
    Carrega o painel-interativo.js (gráficos que filtram uns aos outros). Incluir antes do <script> de configuração da página.
    A versão pela data do arquivo faz o navegador baixar de novo quando o script muda (sem precisar de Ctrl+F5).
--}}
<script src="{{ asset('js/painel-interativo.js') }}?v={{ filemtime(public_path('js/painel-interativo.js')) }}"></script>
