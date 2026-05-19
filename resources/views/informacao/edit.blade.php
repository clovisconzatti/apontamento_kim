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
    <h3 class=""><i class="fas fa-tree"></i> Alteração das Informações</h3>
    <form action="" id="cadastro-informacao" nome="cadastro-informacao" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/informacao/edit/{{$informacao->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
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
                        <option value="{{ $equipamento->id }}" {{ $equipamento->id == $informacao->equipamento ? 'selected' : '' }}>{{ $equipamento->placa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Fazenda
                <select class="form-control limpar" type="text" name="fazenda" id="fazenda">
                    <option value="">Todas</option>
                    @foreach ($fazendas as $fazenda )
                        <option value="{{ $fazenda->id }}" {{ $fazenda->id == $informacao->fazenda ? 'selected' : '' }}>{{ $fazenda->fazenda }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Colaborador
                <select class="form-control limpar" type="text" name="colaborador" id="colaborador">
                    <option value="0">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}" {{ $colaborador->id == $informacao->colaborador ? 'selected' : '' }}>{{ $colaborador->colaborador }}</option>
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
                        <option value="{{ $atividade->id }}" {{ $atividade->id == $informacao->atividade ? 'selected' : '' }}>{{ $atividade->atividade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Hora Inicial
                <input class="form-control" type="time" name="hora_inicial" id="hora_inicial"  value="{{ $informacao->hora_inicial }}" >
            </div>
            <div class="form-group col-md-2">
                Hora Final
                <input class="form-control" type="time" name="hora_final" id="hora_final"  value="{{ $informacao->hora_final }}" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Inicial
                <input class="form-control" type="number" step="any" name="horimetro_inicial" id="horimetro_inicial"  value="{{ $informacao->horimetro_inicial }}" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Final
                <input class="form-control" type="number" step="any" name="horimetro_final" id="horimetro_final"  value="{{ $informacao->horimetro_final }}" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Tipo do Corte
                <select class="form-control limpar" type="text" name="corte" id="corte">
                    <option value="0">Todas</option>
                    @foreach ($cortes as $corte )
                        <option value="{{ $corte->id }}" {{ $corte->id == $informacao->corte ? 'selected' : '' }}>{{ $corte->corte }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Faturamento em M²
                <input class="form-control" type="number" step="any" name="fat_m" id="fat_m"  value="{{ $informacao->fat_m }}" >
            </div>
            <div class="form-group col-md-3">
                Tanque de Origem
                <select class="form-control limpar" type="text" name="origem_abastecimento" id="origem_abastecimento">
                    <option value="0">Todas</option>
                    @foreach ($comboios as $comboio )
                        <option value="{{ $comboio->id }}" {{ $comboio->id == $informacao->origem_abastecimento ? 'selected' : '' }}>{{ $comboio->tanque }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Número NF
                <input class="form-control" type="number" step="any" name="nr_nf" id="nr_nf" value="{{ $informacao->nr_nf }}" >
            </div>
            <div class="form-group col-md-2">
                Total Diesel (litros)
                <input class="form-control" type="number" step="any" name="qnt_diesel" id="qnt_diesel" value="{{ $informacao->qnt_diesel }}" >            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2">
                Horimetro Abastecimento
                <input class="form-control" type="number" step="any" name="horimetro_abastecimento" id="horimetro_abastecimento" value="{{ $informacao->horimetro_abastecimento }}" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Tanque Inicial
                <input class="form-control" type="number" step="any" name="relogio_tanque_inicial" id="relogio_tanque_inicial" value="{{ $informacao->relogio_tanque_inicial }}" >
            </div>
            <div class="form-group col-md-2">
                Horimetro Tanque Final
                <input class="form-control" type="number" step="any" name="relogio_tanque_final" id="relogio_tanque_final" value="{{ $informacao->relogio_tanque_final }}" >
            </div>
            <div class="form-group col-md-2">
                Lubrificante
                <select class="form-control limpar" type="text" name="tipo_lubrificante" id="tipo_lubrificante">
                    <option value="0">Todas</option>
                    @foreach ($lubrificantes as $lubrificante )
                        <option value="{{ $lubrificante->id }}" {{ $lubrificante->id == $informacao->tipo_lubrificante ? 'selected' : '' }}>{{ $lubrificante->lubrificante }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Quantidade Lubrificante
                <input class="form-control" type="number" step="any" name="qnt_lubrificante" id="qnt_lubrificante" value="{{ $informacao->qnt_lubrificante }}" >
            </div>
            <div class="form-group col-md-2">
                Produção de Terceiros (m³)
                <input class="form-control" type="number" step="any" name="producao_terceiros" id="producao_terceiros" value="{{ $informacao->producao_terceiros }}" >
            </div>
             </div>
        <div class="row">
            <div class="form-group col-md-2">
                Comprimento da Madeira (m)
                <input class="form-control" type="number" step="any" name="comprimento_madeira" id="comprimento_madeira" value="{{ $informacao->comprimento_madeira }}" >
            </div>
            <div class="form-group col-md-2">
                Carreg. Julieta
                <input class="form-control" type="number" step="any" name="carg_julieta" id="carg_julieta" value="{{ $informacao->carg_julieta }}" >
            </div>
            <div class="form-group col-md-2">
                Carreg. Truck
                <input class="form-control" type="number" step="any" name="carg_truck" id="carg_truck" value="{{ $informacao->carg_truck }}" >
            </div>
            <div class="form-group col-md-2">
                Carreg. Bitrem
                <input class="form-control" type="number" step="any" name="carg_bitrem" id="carg_bitrem" value="{{ $informacao->carg_bitrem }}" >
            </div>
            <div class="form-group col-md-2">
                Tipo do Terreno
                <select class="form-control limpar" type="text" name="terreno" id="terreno">
                    <option value="0">Todas</option>
                    @foreach ($terrenos as $terreno )
                        <option value="{{ $terreno->id }}" {{ $terreno->id == $informacao->terreno ? 'selected' : '' }}>{{ $terreno->terreno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Clima
                <select class="form-control limpar" type="text" name="clima" id="clima">
                    <option value="0">Todas</option>
                    @foreach ($climas as $clima )
                        <option value="{{ $clima->id }}" {{ $clima->id == $informacao->clima ? 'selected' : '' }} >{{ $clima->clima }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2">
                Baldeio Curto
                <input class="form-control" type="number" step="any" name="baldeio_curto" id="baldeio_curto" value="{{ $informacao->baldeio_curto }}" >
            </div>
            <div class="form-group col-md-2">
                Baldeio Medio
                <input class="form-control" type="number" step="any" name="baldeio_medio" id="baldeio_medio" value="{{ $informacao->baldeio_medio }}" >
            </div>
            <div class="form-group col-md-2">
                Baldeio Longo
                <input class="form-control" type="number" step="any" name="baldeio_longo" id="baldeio_longo" value="{{ $informacao->baldeio_longo }}" >
            </div>
            <div class="form-group col-md-2">
                Arrasto Curto
                <input class="form-control" type="number" step="any" name="arrasto_curto" id="arrasto_curto" value="{{ $informacao->arrasto_curto }}" >
            </div>
            <div class="form-group col-md-2">
                Arrasto Medio
                <input class="form-control" type="number" step="any" name="arrasto_medio" id="arrasto_medio" value="{{ $informacao->arrasto_medio }}" >
            </div>
            <div class="form-group col-md-2">
                Arrasto Longo
                <input class="form-control" type="number" step="any" name="arrasto_longo" id="arrasto_longo" value="{{ $informacao->arrasto_longo }}" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                Observação
                <input class="form-control" type="text" name="obs" id="obs" value="{{ $informacao->obs }}  " >
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
                window.location.href = "{{ route('informacao.listAll') }}";
            });

        });
    </script>

@endsection
