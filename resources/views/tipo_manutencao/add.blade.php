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
    <h3 class=""><i class="far fa-sticky-note"></i> Tipo da Manutenção</h3>
    <form action="" id="cadastro-tipo_manutencao" nome="cadastro-tipo_manutencao" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/tipo_manutencao/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="tipo_manutencao">

        <div class="row">
            <div class="form-group col-md-5">
                Tipo da Manutenção:
                <input class="form-control" type="text" name="tipo" id="tipo"  value="" >
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
                $(location).attr('href',url+'/tipo_manutencao');
            })


        })
    </script>

@endsection
