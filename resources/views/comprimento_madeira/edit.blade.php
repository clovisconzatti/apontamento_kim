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
    <h3 class=""><i class="fas fa-tractor"></i>
                 <i class="fas fa-arrow-right-arrow-left"></i> Comprimento da Madeira</h3>
    <form action="" id="cadastro-comprimento_madeira" nome="cadastro-comprimento_madeira" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/comprimento_madeira/edit/{{$comprimento_madeira->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">
        <input type="hidden" name="origem" id="origem" value="comprimento_madeira">

        <div class="row">
            <div class="form-group col-md-6">
                Comprimento da Madeira em Metros:
                <input class="form-control" type="number" step="any" name="comprimento" id="comprimento" value="{{$comprimento_madeira->comprimento}}" >
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
