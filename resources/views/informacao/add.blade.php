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
    <h3 class=""><i class="fas fa-tree"></i> Informações Diárias</h3>
    <form action="" id="cadastro-informacao" nome="cadastro-informacao" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/informacao/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="informacao">

        <div class="row">
            <div class="form-group col-md-3">
                Data
                <input class="form-control" type="date" name="data" id="data"  value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                Veiculo/Equipamento
                <select class="form-control limpar" type="text" name="equipamento" id="equipamento">
                    <option value="">Todas</option>
                    @foreach ($equipamentos as $equipamento )
                        <option value="{{ $equipamento->id }}">{{ $equipamento->placa }} - {{ $equipamento->equipamento }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Fazenda
                <select class="form-control limpar" type="text" name="fazenda" id="fazenda">
                    <option value="0">Todas</option>
                    @foreach ($fazendas as $fazenda )
                        <option value="{{ $fazenda->id }}">{{ $fazenda->fazenda }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Colaborador
                <select class="form-control limpar" type="text" name="colaborador" id="colaborador">
                    <option value="0">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}">{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                Atividade
                <select class="form-control limpar" type="text" name="atividade" id="atividade">
                    <option value="0">Todas</option>
                    @foreach ($atividades as $atividade )
                        <option value="{{ $atividade->id }}">{{ $atividade->atividade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Hora Inicial
                <input class="form-control" type="time" name="hora_inicial" id="hora_inicial"  value="" >
            </div>
            <div class="form-group col-md-2">
                Hora Final
                <input class="form-control" type="time" name="hora_final" id="hora_final"  value="" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Inicial
                <input class="form-control" type="number" step="any" name="horimetro_inicial" id="horimetro_inicial"  value="" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Final
                <input class="form-control" type="number" step="any" name="horimetro_final" id="horimetro_final"  value="" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Tipo do Corte
                <select class="form-control limpar" type="text" name="corte" id="corte">
                    <option value="0">Todas</option>
                    @foreach ($cortes as $corte )
                        <option value="{{ $corte->id }}">{{ $corte->corte }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Faturamento em M²
                <input class="form-control" type="number" step="any" name="fat_m" id="fat_m"  value="" >
            </div>
            <div class="form-group col-md-3">
                Tanque de Origem
                <select class="form-control limpar" type="text" name="origem_abastecimento" id="origem_abastecimento">
                    <option value="0">Todas</option>
                    @foreach ($comboios as $comboio )
                        <option value="{{ $comboio->id }}">{{ $comboio->tanque }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Número NF
                <input class="form-control" type="number" step="any" name="nr_nf" id="nr_nf"  value="" >
            </div>
            <div class="form-group col-md-2">
                Total Diesel (litros)
                <input class="form-control" type="number" step="any" name="qnt_diesel" id="qnt_diesel"  value="" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Horimetro do Abastecimento
                <input class="form-control" type="number" step="any" name="horimetro_abastecimento" id="horimetro_abastecimento"  value="" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Tanque Inicial
                <input class="form-control" type="number" step="any" name="relogio_tanque_inicial" id="relogio_tanque_inicial"  value="" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Tanque Final
                <input class="form-control" type="number" step="any" name="relogio_tanque_final" id="relogio_tanque_final"  value="" >
            </div>
            <div class="form-group col-md-3">
                Lubrificante
                <select class="form-control limpar" type="text" name="tipo_lubrificante" id="tipo_lubrificante">
                    <option value="0">Todas</option>
                    @foreach ($lubrificantes as $lubrificante )
                        <option value="{{ $lubrificante->id }}">{{ $lubrificante->lubrificante }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Quantidade Lubrificante
                <input class="form-control" type="number" step="any" name="qnt_lubrificante" id="qnt_lubrificante"  value="" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Produção de Terceiros (m³)
                <input class="form-control" type="number" step="any" name="producao_terceiros" id="producao_terceiros"  value="" >
            </div>
            <div class="form-group col-md-2">
                Comprimento Madeira (m)
                <input class="form-control" type="number" step="any" name="comprimento_madeira" id="comprimento_madeira"  value="" >
            </div>
            <div class="form-group col-md-2">
                Carregamento
                <input class="form-control" type="number" step="any" name="carregamento" id="carregamento"  value="" >
            </div>
            <div class="form-group col-md-2">
                Tipo do Terreno
                <select class="form-control limpar" type="text" name="terreno" id="terreno">
                    <option value="0">Todas</option>
                    @foreach ($terrenos as $terreno )
                        <option value="{{ $terreno->id }}">{{ $terreno->terreno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Clima
                <select class="form-control limpar" type="text" name="clima" id="clima">
                    <option value="0">Todas</option>
                    @foreach ($climas as $clima )
                        <option value="{{ $clima->id }}">{{ $clima->clima }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2">
                Baldeio Curto
                <input class="form-control" type="number" step="any" name="baldeio_curto" id="baldeio_curto"  value="" >
            </div>
            <div class="form-group col-md-2">
                Baldeio Medio
                <input class="form-control" type="number" step="any" name="baldeio_medio" id="baldeio_medio"  value="" >
            </div>
            <div class="form-group col-md-2">
                Baldeio Longo
                <input class="form-control" type="number" step="any" name="baldeio_longo" id="baldeio_longo"  value="" >
            </div>
            <div class="form-group col-md-2">
                Arrasto Curto
                <input class="form-control" type="number" step="any" name="arrasto_curto" id="arrasto_curto"  value="" >
            </div>
            <div class="form-group col-md-2">
                Arrasto Medio
                <input class="form-control" type="number" step="any" name="arrasto_medio" id="arrasto_medio"  value="" >
            </div>
            <div class="form-group col-md-2">
                Arrasto Longo
                <input class="form-control" type="number" step="any" name="arrasto_longo" id="arrasto_longo"  value="" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                Observação
                <input class="form-control" type="text" name="obs" id="obs"  value="" >
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
                $(location).attr('href',url+'/informacao');
            })


        })
    </script>

@endsection
