{{--
    Importar a Ordem de Serviço em PDF para o formulário aberto (Manutenção ou Revisão). Nada é gravado até salvar.
    Variáveis: $tela ('manutencao' | 'revisao') e $id (lançamento em alteração ou null). Incluir depois do formulário não é
    necessário: o script lê os campos só na hora de importar.
--}}
<div id="importarOs" style="background: #fff; border: 1px dashed #9fb3a5; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px;"
     data-tela="{{ $tela }}" data-id="{{ $id }}" data-rota="{{ route('importacao_os.conferir') }}" data-token="{{ csrf_token() }}"
     data-pdfjs="{{ asset('js/pdfjs/pdf.min.js') }}" data-worker="{{ asset('js/pdfjs/pdf.worker.min.js') }}">
    <input type="file" id="osArquivo" accept="application/pdf,.pdf" style="display: none;">
    <button type="button" class="btn btn-danger" id="osImportar"><span class="fas fa-file-pdf"></span> Importar OS (PDF)</button>
    <span class="text-muted" style="margin-left: 8px;">Preenche as peças e os serviços da Ordem de Serviço. Confira e clique em Salvar.</span>
    <div id="osResultado" aria-live="polite"></div>
</div>
<script src="{{ asset('js/os-pdf.js') }}?v={{ filemtime(public_path('js/os-pdf.js')) }}"></script>
