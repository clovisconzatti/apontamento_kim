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
    <h3 class=""><i class="fas fa-gas-pump"></i> Cadastro de Combustivel</h3>
    <form action="" id="cadastro-tipo_combustivel" nome="cadastro-tipo_combustivel" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/tipo_combustivel/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="tipo_combustivel">

        <div class="row">
            <div class="form-group col-md-5">
                Combustivel:
                <input class="form-control" type="text" name="combustivel" id="combustivel"  value="" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2">
                Unidade de Medida:
                <input class="form-control" type="text" name="unidade" id="unidade"  value="" >
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
    </form>

    <script>
        $(document).ready(function(){

            $('button#sair').click(function(){
                $(location).attr('href',url+'/tipo_combustivel');
            })


        })
    </script>

@endsection
