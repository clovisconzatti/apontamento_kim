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
    <h3 class=""><i class="far fa-map"></i></i> Alteração de Fazendas</h3>
    <form action="" id="cadastro-fazenda" nome="cadastro-fazenda" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/fazenda/edit/{{$fazenda->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="fazenda">

        <div class="row">
            <div class="form-group col-md-4">
                Fazenda:
                <input class="form-control" type="text" name="fazenda" id="fazenda" value="{{$fazenda->fazenda}}" >
            </div>
            <div class="form-group col-md-2">
                Estado:
                <select class="form-control limpar" name="uf" id="uf">
                    <option value="">Selecione</option>
                    <option value="SC" {{ $fazenda->uf == 'SC' ? 'selected' : '' }}>SC</option>
                    <option value="RS" {{ $fazenda->uf == 'RS' ? 'selected' : '' }}>RS</option>
                    <option value="PR" {{ $fazenda->uf == 'PR' ? 'selected' : '' }}>PR</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                Apontador:
                 <select class="form-control limpar" type="text" name="apontador" id="apontador">
                    <option value="%">Todas</option>
                    @foreach ($colaboradores as $colaborador )
                        <option value="{{ $colaborador->id }}" {{ $colaborador->id == $fazenda->apontador ? 'selected' : '' }}>{{ $colaborador->colaborador }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                Ativa:
                 <select class="form-control limpar" type="text" name="ativa" id="ativa" value="{{ $colaborador->ativa }}" >
                    <option value="">Selecione</option>
                    <option value="Sim" {{ $fazenda->ativa == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Nao" {{ $fazenda->ativa == 'Nao' ? 'selected' : '' }}>Não</option>
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
