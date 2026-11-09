{{--
    Formulário do Serviço de Revisão (inclusão e alteração).
    Variáveis: $servico (null na inclusão), $route, $type.
--}}
@php
    $servico = $servico ?? null;
@endphp
<h3 class=""><i class="fas fa-tools"></i> Serviço de Revisão</h3>
<form action="" id="cadastro-servico_revisao" nome="cadastro-servico_revisao" method="post">
    @csrf
    @method('patch')
    <input type="hidden" name="route" id="route" value="{{ $route }}">
    <input type="hidden" name="type" id="type" value="{{ $type }}">
    <input type="hidden" name="origem" id="origem" value="servico_revisao">

    <div class="row">
        <div class="form-group col-md-6">
            <label for="servico">* Serviço:</label>
            <input class="form-control" type="text" maxlength="150" name="servico" id="servico" value="{{ optional($servico)->servico }}" placeholder="Ex.: Troca de Óleo do Motor">
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
            window.location.href = "{{ route('servico_revisao.listAll') }}";
        })
    })
</script>
