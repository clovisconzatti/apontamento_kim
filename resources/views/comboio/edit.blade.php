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
                $(location).attr('href',url+'/menu');
            })
        })
    </script>

@endsection
