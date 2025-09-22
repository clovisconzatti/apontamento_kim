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
    <h3 class=""><i class="fas fa-gas-pump"></i> Abastecimento</h3>
    <form action="" id="cadastro-combustivel" nome="cadastro-combustivel" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/combustivel/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="combustivel">
        <input type="hidden" name="ultimoKm" id="ultimoKm" value="">
        <input type="hidden" name="ultimaHora" id="ultimaHora" value="">


        <div class="row">
            <div class="form-group col-md-3">
                Data:
                <input class="form-control" type="date" name="data" id="data"  value="" >
            </div>
            <div class="form-group col-md-3">
                Placa:
                <select class="form-control limpar" type="text" name="placa" id="placa">
                    <option value="%">Todas</option>
                    @foreach ($placas as $placa )
                        <option value="{{ $placa->id }}">{{ $placa->placa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Total Litros:
                <input class="form-control limpar" type="number" step="any" name="litros" id="litros"  value="" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Km Atual:
                <input class="form-control limpar" type="number" step="any" name="km" id="km"  value="" >
            </div>
            <div class="form-group col-md-3">
                Hora Atual:
                <input class="form-control limpar" type="number" step="any" name="horas" id="horas"  value="" >
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
            <div class="form-group col-md-9">
                Observação:
                <input class="form-control limpar" type="text" name="obs" id="obs"  value="" >
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
                $(location).attr('href',url+'/combustivel');
            })


        })
    </script>

@endsection
