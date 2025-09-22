@extends('layouts.model')
@section('content')
    <h3 class=""><i class="fa fa-upload"></i> abastecida -> {{$abastecida->placa}} ({{$abastecida->id}}) </h4>
    <hr>
    <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="form-group col-md-4">
                Arquivo:<br>
                <div class="input-group mb-3">
                    <div class="">
                        <input type="file" class="" name="arquivo" id="arquivo" aria-describedby="" required>
                        {{-- <label class="custom-file-label" for="validatedCustomFile">Selecione....</label> --}}
                        <input type="hidden" name="nomeArquivo" id="nomeArquivo" value="Anexo">
                        <input type="hidden" name="apontamento" id="apontamento" value="{{$abastecida->id}}">
                        <input type="hidden" name="placa" id="placa" value="{{$abastecida->placa}}">

                    </div>
                </div>
            </div>

            <div class="form-group col-md-4">
                <br>
                <button type="submit" class="btn btn-primary">Enviar arquivo</button>
            </div>
        </div>
    </form><hr>
           <div class="row">
            <table class="table table-condensed table-bordered">
                <thead>
                    <tr>
                        <th>Anexo</th>
                    </tr>
                </thead>

                <tbody>
                    <td>
                        <embed src="{{ asset('storage/'.$abastecida->placa.'/'.$abastecida->anexo)  }}" type=""  style="height: 500px; width: 100%">
                    </td>
                </tbody>
            </table>
        </div>
@endsection
