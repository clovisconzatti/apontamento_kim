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
    <h3 class=""><i class="far fa-address-card"></i></i> Alteração de Fornecedor</h3>
    <form action="" id="cadastro-fornecedor" nome="cadastro-fornecedor" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/fornecedor/edit/{{$fornecedores->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="fornecedor">

        <div class="row">
            <div class="form-group col-md-6">
                Fornecedor:
                <input class="form-control" type="text" name="fornecedor" id="fornecedor" value="{{$fornecedores->fornecedor}}" >
            </div>
            <div class="form-group col-md-3">
                Codigo Cargo:
                <input class="form-control" type="text" name="cod_cargo" id="cod_cargo" value="{{$fornecedores->cod_cargo}}" >
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
