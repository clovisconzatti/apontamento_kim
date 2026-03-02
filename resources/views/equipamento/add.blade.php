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
    <h3 class=""><i class="fas fa-truck"></i> Equipamento</h3>
    <form action="" id="cadastro-equipamento" nome="cadastro-equipamento" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/equipamento/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="equipamento">

        <div class="row">
            <div class="form-group col-md-2">
                Placa:
                <input class="form-control" type="text" name="placa" id="placa"  value="" >
            </div>
            <div class="form-group col-md-5">
                Equipamento:
                <input class="form-control" type="text" name="equipamento" id="equipamento"  value="" >
            </div>
            <div class="form-group col-md-1">
                Ano:
                <input class="form-control" type="text" name="ano" id="ano"  value="" >
            </div>
            <div class="form-group col-md-2">
                Ativo:
                <select class="form-control limpar" type="text" name="ativo" id="ativo" >
                    <option value="">Selecione</option>
                    <option value="Sim">Sim</option>
                    <option value="Nao">Não</option>
                </select>
            </div>
            <div class="form-group col-md-1">
                Estado:
                <select class="form-control limpar" type="text" name="uf" id="uf" >
                    <option value="">Selecione</option>
                    <option value="SC">SC</option>
                    <option value="RS">RS</option>
                    <option value="PR">PR</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                Data da Partida:
                <input class="form-control" type="date" name="data_partida" id="data_partida"  value="{{date('Y-m-d')}}" >
            </div>
            <div class="form-group col-md-3">
                Tipo:
                <select class="form-control limpar" type="text" name="tipo" id="tipo">
                    <option value="%">Todas</option>
                    @foreach ($tipos as $tipo )
                        <option value="{{ $tipo->id }}">{{ $tipo->tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                Ativiade:
                <select class="form-control limpar" type="text" name="atividade" id="atividade">
                    <option value="%">Todas</option>
                    @foreach ($atividades as $atividade )
                        <option value="{{ $atividade->id }}">{{ $atividade->atividade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Cilindros:
                <input class="form-control" type="number" name="cilindros" id="cilindros"  value="" >
            </div>
            <div class="form-group col-md-4">
                Operação:
                <select class="form-control limpar" type="text" name="operacao" id="operacao">
                    <option value="%">Todas</option>
                    @foreach ($operacoes as $operacao )
                        <option value="{{ $operacao->id }}">{{ $operacao->operacao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Consumo Minimo:
                <input class="form-control" type="number" step="any" name="consumo_minimo" id="consumo_minimo"  value="" >
            </div>
            <div class="form-group col-md-2">
                Consimo Máximo:
                <input class="form-control" type="number" step="any" name="consumo_maximo" id="consumo_maximo"  value="" >
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
                $(location).attr('href',url+'/equipamento');
            })


        })
    </script>

@endsection
