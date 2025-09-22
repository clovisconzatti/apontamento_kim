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
    <form action="" id="cadastro-apontamento" nome="cadastro-apontamento" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/apontamento/edit/{{$apontamento->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="apontamento">
        {{-- {{ dd($apontamento) }} --}}
        <div class="row">
            <div class="form-group col-md-3">
                Data:
                <input class="form-control" type="date" name="data" id="data"  value="{{ $apontamento->data }}" >
            </div>
            <div class="form-group col-md-4">
                Placa:
                <select class="form-control limpar" type="text" name="placa" id="placa">
                    <option value="%">Todas</option>
                    @foreach ($placas as $placa )
                        <option value="{{ $placa->id }}" {{ $placa->id == $apontamento->equipamento ? 'selected' : '' }}>{{ $placa->placa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Total Litros:
                <input class="form-control" type="text" name="litros" id="litros"  value="{{ $apontamento->litros }}" >
            </div>
            <div class="form-group col-md-2">
                Km Atual:
                <input class="form-control" type="text" name="km" id="km"  value="{{ $apontamento->km }}" >
            </div>
        </div>
        <div class="row" >
            <div class="form-group col-md-2">
                Hora Atual:
                <input class="form-control" type="text" name="horas" id="horas"  value="{{ $apontamento->horas }}" >
            </div>

            <div class="form-group col-md-2">
                Combustivel:
                <select class="form-control" type="text" name="combustivel" id="combustivel" >
                    <option value="">Selecione</option>
                    <option value="Gasolina" {{($apontamento->combustivel=='Gasolina')? 'selected' : ''}}>Gasolina</option>
                    <option value="Etanol"  {{($apontamento->combustivel=='Etanol')? 'selected' : ''}}>Etanol</option>
                    <option value="Diesel-S10" {{($apontamento->combustivel=='Diesel-S10')? 'selected' : ''}}>Diesel-S10</option>
                    <option value="Diesel-S500" {{($apontamento->combustivel=='Diesel-S500')? 'selected' : ''}}>Diesel-S500</option>
                    <option value="Arla" {{($apontamento->combustivel=='Arla')? 'selected' : ''}}>Arla</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                Origem:
                <select class="form-control" type="text" name="origemAbastecimento" id="origemAbastecimento" >
                    <option value="">Selecione</option>
                    <option value="Interno" {{ ($apontamento->origem=='Interno') ? 'selected' : '' }}>Interno/Frigorifico</option>
                    <option value="Externo" {{ ($apontamento->origem=='Externo') ? 'selected' : '' }}>Externo/Posto</option>
                </select>
            </div>
            <div class="form-group col-md-7">
                Observações:
                <input class="form-control" type="text" name="obs" id="obs"  value="{{ $apontamento->obs }}" >
            </div>
        </div>
        <div class = row>
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
                $(location).attr('href',url+'/apontamento');
            })
        })
    </script>

@endsection
