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
    <h3 class=""><i class="fas fa-tree"></i> Alteração de Transferência</h3>
    <form action="" id="cadastro-transferencia" nome="cadastro-transferencia" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/transferencia/edit/{{$transferencia->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="transferencia">

        <div class="row">
            <div class="form-group col-md-3">
                * Data:
                <input class="form-control" type="date" name="data" id="data"  value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                * Origem:
                <select class="form-control limpar" type="text" name="origem" id="origem">
                    <option value="">Todas</option>
                    @foreach ($comboios as $comboio )
                        <option value="{{ $comboio->id }}" {{ $comboio->id == $transferencia->origem ? 'selected' : '' }}>{{ $comboio->tanque }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Tanque/Comboio:
                <select class="form-control limpar" type="text" name="tanque" id="tanque" >
                    <option value="">Todas</option>
                    @foreach ($comboios as $comboio )
                        <option value="{{ $comboio->id }}" {{ $comboio->id == $transferencia->tanque ? 'selected' : '' }}>{{ $comboio->tanque }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Equipamento/Caminhão:
                <select class="form-control limpar" type="text" name="destino" id="destino" >
                    <option value="">Todas</option>
                    @foreach ($equipamentos as $equipamento )
                        <option value="{{ $equipamento->id }}" {{ $equipamento->id == $transferencia->destino ? 'selected' : '' }}>{{ $equipamento->equipamento }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                * Total de Litros:
                <input class="form-control limpar" type="number" step="any" name="litros" id="litros"  value="{{ $transferencia->litros }}" >
            </div>
            <div class="form-group col-md-2">
                Nr Documento:
                <input class="form-control limpar" type="number" step="any" name="nr_doc" id="nr_doc"  value="{{ $transferencia->nr_doc }}" >
            </div>
            <div class="form-group col-md-3">
            * Combustivel:
                <select class="form-control limpar" type="text" name="combustivel" id="combustivel" >
                    <option value="">Todas</option>
                    @foreach ($tipo_combustiveis as $tipo_combustivel )
                        <option value="{{ $tipo_combustivel->id }}" {{ $tipo_combustivel->id == $transferencia->combustivel ? 'selected' : '' }}>{{ $tipo_combustivel->combustivel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Horimetro Equipamento:
                <input class="form-control limpar" type="number" step="any" name="horimetro" id="horimetro"  value="{{ $transferencia->horimetro }}" >
            </div>
            <div class="form-group col-md-3">
            Operador:
                <select class="form-control limpar" type="text" name="operador" id="operador" >
                    <option value="">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}" {{ $colaborador->id == $transferencia->operador ? 'selected' : '' }}>{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
            Fazenda:
                <select class="form-control limpar" type="text" name="fazenda" id="fazenda" >
                    <option value="">Todas</option>
                    @foreach ($fazendas as $fazenda )
                        <option value="{{ $fazenda->id }}" {{ $fazenda->id == $transferencia->fazenda ? 'selected' : '' }}>{{ $fazenda->fazenda }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Horimetro Inicial:
                <input class="form-control limpar" type="number" step="any" name="horimetro_inicial" id="horimetro_inicial"  value="{{ $transferencia->horimetro_inicial }}" >
            </div>
            <div class="form-group col-md-3">
                Horimetro Final:
                <input class="form-control limpar" type="number" step="any" name="horimetro_final" id="horimetro_final"  value="{{ $transferencia->horimetro_final }}" >
            </div>
            <div class="form-group col-md-11">
                Obs:
                <input class="form-control limpar" type="text" name="obs" id="obs"  value="{{ $transferencia->obs }}" >
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
        $(document).ready(function () {

            $('#sair').on('click', function () {
                window.location.href = "{{ route('transferencia.listAll') }}";
            });

        });
    </script>

@endsection
