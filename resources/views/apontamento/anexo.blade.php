@extends('layouts.model')
@section('content')
    <h3 class=""><i class="fa fa-upload"></i> Anexo do abastecimento -> {{ $abastecida->placa }} - {{ $abastecida->equipamento }}
        <small class="text-muted">(#{{ $abastecida->id }} - {{ date('d/m/Y', strtotime($abastecida->data)) }})</small>
    </h3>
    <hr>

    @if (session()->get('success'))
        <div class="alert alert-success">{{ session()->get('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="apontamento" id="apontamento" value="{{ $abastecida->id }}">
        <div class="row">
            <div class="form-group col-md-6">
                Arquivo (PDF ou imagem, até 10 MB):<br>
                <input type="file" name="arquivo" id="arquivo" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                @if ($abastecida->anexo)
                    <br><small class="text-muted">Enviar um novo arquivo substitui o anexo atual.</small>
                @endif
            </div>
            <div class="form-group col-md-4">
                <br>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-upload"></i> Enviar arquivo
                </button>
            </div>
        </div>
    </form>
    <hr>

    <div class="row">
        <div class="col-md-12">
            @if ($urlAnexo)
                <p><a href="{{ $urlAnexo }}" target="_blank"><i class="fa fa-external-link-alt"></i> Abrir em nova aba</a></p>
                <embed src="{{ $urlAnexo }}" style="height: 500px; width: 100%">
            @elseif ($abastecida->anexo)
                <div class="alert alert-warning">O arquivo {{ $abastecida->anexo }} está registrado, mas não foi encontrado no servidor.</div>
            @else
                <div class="alert alert-info">Nenhum anexo enviado para este abastecimento.</div>
            @endif
        </div>
    </div>
@endsection
