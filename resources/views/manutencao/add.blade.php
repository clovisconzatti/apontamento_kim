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
    <h3 class=""><i class="fas fa-tree"></i> Informações de Manutenções - Atenção "*" Informação Obrigatória</h3>
    <form action="" id="cadastro-manutencao" nome="cadastro-manutencao" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/manutencao/store">
        <input type="hidden" name="type" id="type" value="POST">
        <input type="hidden" name="origem" id="origem" value="manutencao">

        <div class="row">
            <div class="form-group col-md-3">
                * Data
                <input class="form-control" type="date" name="data" id="data"  value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                * Veiculo/Equipamento
                <select class="form-control limpar" type="text" name="equipamento" id="equipamento">
                    <option value="0">Todas</option>
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
                <select class="form-control limpar" type="text" name="operador" id="operador">
                    <option value="0">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}">{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
           <div class="form-group col-md-2">
                * Ordem de Serviço
                <input class="form-control" type="number" step="any" name="ord_servico" id="ord_servico"  value="" >
            </div>
            <div class="form-group col-md-2">
                * Hora Inicial
                <input class="form-control" type="time" name="hora_inicial" id="hora_inicial"  value="" >
            </div>
            <div class="form-group col-md-2">
                * Hora Final
                <input class="form-control" type="time" name="hora_final" id="hora_final"  value="" >
            </div>
            <div class="form-group col-md-3">
                Horimetro
                <input class="form-control" type="number" step="any" name="horimetro" id="horimetro"  value="" >
            </div>
            <div class="form-group col-md-3">
                Tipo da Manutenção
                <select class="form-control limpar" type="text" name="tipo_manutencao" id="tipo_manutencao">
                    <option value="0">Todas</option>
                    @foreach ($tipos_manutencao as $tipo_manutencao )
                        <option value="{{ $tipo_manutencao->id }}">{{ $tipo_manutencao->tipo }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Custo da Manutenção
                <input class="form-control" type="number" step="any" name="custo" id="custo"  value="" >
            </div>
            <div class="form-group col-md-3">
                Manutenção Diária
                <input class="form-control" type="number" step="any" name="manutencao_diaria" id="manutencao_diaria"  value="" >
            </div>
            <div class="form-group col-md-3">
                Situação da Manutenção
                <select class="form-control limpar" type="text" name="situacao" id="situacao">
                    <option value="0">Todas</option>
                    @foreach ($situacoes_manutencao as $situacao_manutencao )
                        <option value="{{ $situacao_manutencao->id }}">{{ $situacao_manutencao->situacao }}</option>
                    @endforeach
                </select>
            </div>
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
                $(location).attr('href',url+'/manutencao');
            })


        })
    </script>

@endsection
