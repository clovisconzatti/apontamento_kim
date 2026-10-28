{{--
    Formulário do Tipo de Revisão (inclusão e alteração).
    Variáveis: $tipo_revisao (null na inclusão), $route, $type.
--}}
@php
    $tipo_revisao = $tipo_revisao ?? null;
@endphp
<h3 class=""><i class="fas fa-clipboard-check"></i> Tipo de Revisão</h3>
<form action="" id="cadastro-tipo_revisao" nome="cadastro-tipo_revisao" method="post">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="tipo_revisao">

    <div class="row">
        <div class="form-group col-md-6">
            <label for="descricao">* Descrição do Tipo de Revisão:</label>
            <input class="form-control" type="text" maxlength="100" name="descricao" id="descricao" value="{{ optional($tipo_revisao)->descricao }}" placeholder="Ex.: Revisão 250 horas">
        </div>
        <div class="form-group col-md-3">
            <label for="intervalo">* KM/Horas Revisão:</label>
            <input class="form-control" type="number" step="any" min="0" name="intervalo" id="intervalo" value="{{ optional($tipo_revisao)->intervalo }}" placeholder="Ex.: 250">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-9">
            <label for="obs">Observação:</label>
            <input class="form-control" type="text" maxlength="255" name="obs" id="obs" value="{{ optional($tipo_revisao)->obs }}">
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
            window.location.href = "{{ route('tipo_revisao.listAll') }}";
        })
    })
</script>
