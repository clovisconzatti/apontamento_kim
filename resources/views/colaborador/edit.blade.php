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
    <h3 class=""><i class="fas fa-id-badge"></i></i> Alteração de Colaborador</h3>
    <form action="" id="cadastro-colaborador" nome="cadastro-colaborador" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/colaborador/edit/{{$colaborador->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="colaborador">

        <div class="row">
            <div class="form-group col-md-4">
                Colaborador:
                <input class="form-control" type="text" name="colaborador" id="colaborador" value="{{$colaborador->colaborador}}" >
            </div>
            <div class="form-group col-md-1">
                Estado:
                <select class="form-control limpar" name="uf" id="uf">
                    <option value="">Selecione</option>
                    <option value="SC" {{ $colaborador->uf == 'SC' ? 'selected' : '' }}>SC</option>
                    <option value="RS" {{ $colaborador->uf == 'RS' ? 'selected' : '' }}>RS</option>
                    <option value="PR" {{ $colaborador->uf == 'PR' ? 'selected' : '' }}>PR</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                Ativo:
                 <select class="form-control limpar" type="text" name="ativo" id="ativo" value="{{ $colaborador->ativo }}" >
                    <option value="">Selecione</option>
                    <option value="Sim" {{ $colaborador->ativo == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Nao" {{ $colaborador->ativo == 'Nao' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                Cod. Contabilidade:
                <input class="form-control" type="text" name="cod" id="cod" value="{{$colaborador->cod}}" >
            </div>
            <div class="form-group col-md-3">
                Empresa:
                 <select class="form-control limpar" type="text" name="empresa" id="empresa" value="{{ $colaborador->empresa }}" >
                    <option value="">Selecione</option>
                     <option value="Kim" {{ $colaborador->empresa == 'Kim' ? 'selected' : '' }}>Kim</option>
                    <option value="TSC" {{ $colaborador->empresa == 'TSC' ? 'selected' : '' }}>TSC</option>
                    <option value="Mid" {{ $colaborador->empresa == 'Mid' ? 'selected' : '' }}>Mid</option>
                    <option value="Marumbi" {{ $colaborador->empresa == 'Marumbi' ? 'selected' : '' }}>Marumbi</option>
                </select>
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
