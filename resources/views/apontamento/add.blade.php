@extends('layouts.model')

@section('content')
    <h3 class=""><i class="fas fa-gas-pump"></i> Abastecimento</h3>
    <form action="" id="cadastro-apontamento" nome="cadastro-apontamento" method="post">
        @csrf
        <input type="hidden" name="route" id="route" value="/apontamento/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="apontamento">
        <input type="hidden" name="idApontamento" id="idApontamento" value="">
        <input type="hidden" name="ultimoKm" id="ultimoKm" value="">
        <input type="hidden" name="ultimaHora" id="ultimaHora" value="">

        @include('apontamento.campos', ['apontamento' => null])

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
                $(location).attr('href',url+'/apontamento');
            })
        })
    </script>

@endsection
