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
    <h3 class=""><i class="far fa-map"></i></i> Fazendas</h3>
    <form action="" id="cadastro-fazenda" nome="cadastro-fazenda" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/fazenda/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="fazenda">

        <div class="row">
            <div class="form-group col-md-4">
                Fazenda:
                <input class="form-control" type="text" name="fazenda" id="fazenda"  value="" >
            </div>
             <div class="form-group col-md-2">
                Estado:
                <select class="form-control limpar" type="text" name="uf" id="uf" >
                    <option value="">Selecione</option>
                    <option value="SC">SC</option>
                    <option value="RS">RS</option>
                    <option value="PR">PR</option>
                </select>
            </div>
             <div class="form-group col-md-4">
                Apontador:
                <select class="form-control limpar" type="text" name="apontador" id="apontador">
                    <option value="%">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}">{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                 Ativo:
                <select class="form-control limpar" type="text" name="ativa" id="ativa" >
                    <option value="">Selecione</option>
                    <option value="Sim">Sim</option>
                    <option value="Nao">Não</option>
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
    </form>

    <script>
        $(document).ready(function(){

            $('button#sair').click(function(){
                $(location).attr('href',url+'/fazenda');
            })


        })
    </script>

@endsection
