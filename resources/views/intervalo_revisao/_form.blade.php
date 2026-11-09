{{--
    Formulário do Intervalo de Revisão (inclusão e alteração).
    Variáveis: $intervalo (null na inclusão), $route, $type.
--}}
@php
    $intervalo = $intervalo ?? null;
@endphp
<h3 class=""><i class="fas fa-history"></i> Intervalo de Revisão</h3>
<form action="" id="cadastro-intervalo_revisao" nome="cadastro-intervalo_revisao" method="post">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="intervalo_revisao">

    <div class="row">
        <div class="form-group col-md-3">
            <label for="horas">* Horas de Intervalo:</label>
            <input class="form-control" type="number" step="any" min="0" name="horas" id="horas" value="{{ optional($intervalo)->horas }}" placeholder="Ex.: 250">
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
            window.location.href = "{{ route('intervalo_revisao.listAll') }}";
        })
    })
</script>
