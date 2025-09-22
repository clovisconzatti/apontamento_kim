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
    <h3 class=""><i class="fas fa-truck"></i> Transferencia</h3>
    <form action="" id="cadastro-transferencia" nome="cadastro-transferencia" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/transferencia/edit/{{$transferencia->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="transferencia">

        <div class="row">
            <div class="form-group col-md-3">
                Data:
                <input class="form-control" type="date" name="data" id="data"  value="" >
            </div>
            <div class="form-group col-md-3">
            Combustivel:
                <select class="form-control limpar" type="text" name="combustivel" id="combustivel" >
                    <option value="">Selecione</option>
                    <option value="Diesel-S10">Diesel-S10</option>
                    <option value="Diesel-S500">Diesel-S500</option>
                    <option value="Arla">Arla</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                Origem:
                <select class="form-control limpar" type="text" name="origem" id="origem" >
                    <option value="">Selecione</option>
                    <option value="Externo">Externo</option>
                    <option value="Principal">Tanque Principal</option>
                    <option value="Secundario">Tanque Secundario</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                Destino:
                <select class="form-control limpar" type="text" name="destino" id="destino" >
                    <option value="">Selecione</option>
                    <option value="Extorno">Externo</option>
                    <option value="Principal">Tanque Principal</option>
                    <option value="Secundario">Tanque Secundario</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                Total de Litros:
                <input class="form-control limpar" type="number" step="any" name="litros" id="litros"  value="" >
            </div>
            <div class="form-group col-md-6">
                Fornecedor:
                <input class="form-control" type="text" name="fornecedor" id="fornecedor"  value="" >
            </div>
            <div class="form-group col-md-2">
                Nr Documento:
                <input class="form-control limpar" type="number" step="any" name="nr_doc" id="nr_doc"  value="" >
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
