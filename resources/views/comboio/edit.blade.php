@extends('layouts.model')

@section('content')
    @if (session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
        <br/>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <br/>
    @endif
    <h3 class=""><i class="fas fa-gas-pump"></i></i> Alteração de Comboio</h3>
    <form action="" id="cadastro-comboio" nome="cadastro-comboio" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/comboio/edit/{{$comboios->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="comboio">

        <div class="row">
            <div class="form-group col-md-4">
                Tanque/Comboio:
                <input class="form-control" type="text" name="tanque" id="tanque" value="{{$comboios->tanque}}" >
            </div>
            <div class="form-group col-md-2">
                Capacidade em Litros:
                <input class="form-control" type="text" name="capacidade" id="capacidade" value="{{$comboios->capacidade}}" >
            </div>
            <div class="form-group col-md-4">
                Fazenda:
                <select class="form-control limpar" type="text" name="fazenda" id="fazenda">
                    <option value="">Todas</option>
                    @foreach ($fazendas as $fazenda )
                        <option value="{{ $fazenda->id }}" {{ $fazenda->id == $comboios->fazenda ? 'selected' : '' }}>{{ $fazenda->fazenda }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-1">
                Estado:
                <select class="form-control limpar" name="uf" id="uf">
                    <option value="">Selecione</option>
                    <option value="SC" {{ $comboios->uf == 'SC' ? 'selected' : '' }}>SC</option>
                    <option value="RS" {{ $comboios->uf == 'RS' ? 'selected' : '' }}>RS</option>
                    <option value="PR" {{ $comboios->uf == 'PR' ? 'selected' : '' }}>PR</option>
                </select>
            </div>
            <div class="form-group col-md-1">
                Ativo:
                <select class="form-control limpar" name="ativo" id="ativo">
                    <option value="Sim" {{ $comboios->ativo !== 'Nao' ? 'selected' : '' }}>Sim</option>
                    <option value="Nao" {{ $comboios->ativo === 'Nao' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Tipo:
                <select class="form-control limpar" name="tipo" id="tipo">
                    <option value="proprio" {{ $comboios->tipo !== 'posto' ? 'selected' : '' }}>Tanque próprio (entra no saldo)</option>
                    <option value="posto" {{ $comboios->tipo === 'posto' ? 'selected' : '' }}>Posto externo (sem saldo)</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                Saldo inicial (litros):
                <input class="form-control" type="number" step="any" name="saldo_inicial" id="saldo_inicial" value="{{ $comboios->saldo_inicial }}">
            </div>
            <div class="form-group col-md-2">
                Data do saldo inicial:
                <input class="form-control" type="date" name="data_saldo_inicial" id="data_saldo_inicial" value="{{ $comboios->data_saldo_inicial }}">
            </div>
            <div class="form-group col-md-5 align-self-end">
                <small class="text-muted">O saldo inicial é somado a todos os lançamentos do tanque; a data registra quando ele foi informado.</small>
            </div>
            <div class="form-group col-md-12">
                Observação:
                <input class="form-control" type="text" name="obs" id="obs" value="{{$comboios->obs}}" >
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                <button type="submit" name="salvar" value="" id="salvar" class="btn btn-success btn-block">
                    <span class="fas fa-save"></span> Salvar
                </button>
            </div>
                <div class="form-group col-md-3">
                    <button type="button" name="sair" id="sair" value="" class="btn btn-danger btn-block">
                        <span class="fa fa-door-open"></span> Sair
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).ready(function(){

            $('button#sair').click(function(){
                window.location.href = "{{ route('comboio.listAll') }}";
            })

            // Atalho do Painel de Saldo (?foco=saldo_inicial): leva direto ao campo do saldo inicial
            @if (request('foco') === 'saldo_inicial')
                var campo = $('#saldo_inicial');
                $('html, body').animate({ scrollTop: campo.offset().top - 120 }, 200);
                campo.closest('.form-group').add($('#data_saldo_inicial').closest('.form-group'))
                     .css({ background: '#fff4c2', borderRadius: '8px', padding: '6px', transition: 'background 1s' });
                campo.focus();
            @endif
        })
    </script>

@endsection
