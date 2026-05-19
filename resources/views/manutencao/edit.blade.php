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
    <h3 class=""><i class="fas fa-tree"></i> Alteração de Manutenção</h3>
    <form action="" id="cadastro-manutencao" nome="cadastro-manutencao" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/manutencao/edit/{{$manutencao->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
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
                        <option value="{{ $equipamento->id }}" {{ $equipamento->id == $manutencao->equipamento ? 'selected' : '' }}>{{ $equipamento->placa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Fazenda
                <select class="form-control limpar" type="text" name="fazenda" id="fazenda">
                    <option value="0">Todas</option>
                    @foreach ($fazendas as $fazenda )
                        <option value="{{ $fazenda->id }}" {{ $fazenda->id == $manutencao->fazenda ? 'selected' : '' }}>{{ $fazenda->fazenda }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                Colaborador
                <select class="form-control limpar" type="text" name="operador" id="operador">
                    <option value="0">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}" {{ $colaborador->id == $manutencao->operador ? 'selected' : '' }}>{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
           <div class="form-group col-md-2">
                * Ordem de Serviço
                <input class="form-control" type="number" step="any" name="ord_servico" id="ord_servico"  value="{{ $manutencao->ord_servico }}" >
            </div>
            <div class="form-group col-md-2">
                * Hora Inicial
                <input class="form-control" type="time" name="hora_inicial" id="hora_inicial"  value="{{ $manutencao->hora_inicial }}" >
            </div>
            <div class="form-group col-md-2">
                * Hora Final
                <input class="form-control" type="time" name="hora_final" id="hora_final"  value="{{ $manutencao->hora_final }}" >
            </div>
            <div class="form-group col-md-3">
                Horimetro
                <input class="form-control" type="number" step="any" name="horimetro" id="horimetro"  value="{{ $manutencao->horimetro }}" >
            </div>
            <div class="form-group col-md-3">
                Tipo da Manutenção
                <select class="form-control limpar" type="text" name="tipo_manutencao" id="tipo_manutencao">
                    <option value="0">Todas</option>
                    @foreach ($tipos_manutencao as $tipo_manutencao )
                        <option value="{{ $tipo_manutencao->id }}" {{ $tipo_manutencao->id == $manutencao->tipo_manutencao ? 'selected' : '' }}>{{ $tipo_manutencao->tipo }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                Custo da Manutenção
                <input class="form-control" type="number" step="any" name="custo" id="custo"  value="{{ $manutencao->custo }}" >
            </div>
            <div class="form-group col-md-3">
                Manutenção Diária
                <input class="form-control" type="number" step="any" name="manutencao_diaria" id="manutencao_diaria"  value="{{ $manutencao->manutencao_diaria }}" >
            </div>
            <div class="form-group col-md-3">
                Situação da Manutenção
                <select class="form-control limpar" type="text" name="situacao" id="situacao">
                    <option value="0">Todas</option>
                    @foreach ($situacoes_manutencao as $situacao_manutencao )
                        <option value="{{ $situacao_manutencao->id }}" {{ $situacao_manutencao->id == $manutencao->situacao ? 'selected' : '' }}>{{ $situacao_manutencao->situacao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-12">
                Observação
                <input class="form-control" type="text" name="obs" id="obs"  value="{{ $manutencao->obs }}" >
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
                window.location.href = "{{ route('manutencao.listAll') }}";
            });

        });
    </script>

@endsection
