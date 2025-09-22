
today=new Date();
y=today.getFullYear();
m=today.getMonth();
m=("00" + m).slice(-2);
d=today.getDate();
d=("00" + d).slice(-2);

const hoje = y + '-' + m + '-' + d;

/**********************formata numero **************************************************/
const formCurrency = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2
})

/********************* busca cep cliente *****************************************/
function buscaCep(cep){
    $.ajax({
        data: {cep:cep},
        type: 'POST',
        dataType: 'JSON',
        url:url+'/cliente/buscaCep',
        beforeSend: function(){

        },
        success: function(result)
        {
            $('#cidade').val(result.localidade);
            $('#endereco').val(result.logradouro);
            $('#Bairro').val(result.bairro);
            $('#uf').val(result.uf);
        }
    });

}

function colocaChosen(){
    $(document).find('select').chosen();
}


/*****************************busca cnpj*****************************************/
function buscaCnpj(cnpj){
    $.ajax({
        data: {cnpj:cnpj},
        type: 'POST',
        dataType: 'JSON',
        url:url+'/cliente/buscaCnpj',
        beforeSend: function(){
            Swal({
                title: 'Aguarde consultado dados!',
                type: 'warning',
                timer:2000
            })
        },
        success: function(result)
        {
            console.log(result);
            $('input#nome').val(result.nome);
            $('input#cep').val(result.cep);
            $('input#telefone').val(result.telefone);
            $('input#cidade').val(result.municipio);
            $('input#email').val(result.email);
            $('input#endereco').val(result.logradouro+','+result.numero);
            $('input#bairro').val(result.bairro);
            $('input#uf').val(result.uf);
            $('input#responsavel').val(result['qsa'][0].nome);


        }
    });
}

/******************************************ordenaEntrega ********************************************/
function sortable(){
    $( ".sortable" ).sortable( {
        connectWith: ".sortable",
        placeholder: "dragHelp",
        scroll: true,
        // revert      : true,
        cursor: "move",
        update: function( event, ui ) {
            var listapedidoId = $(this).sortable('toArray').toString();
            var dadosajax={
                'listapedidoId' : listapedidoId
            }
            $.ajax({
                url :"ordenaEntrega.php",
                data:dadosajax,
                type:'POST',
                cache:false,
                success: function(result){
                    console.log(result)
                }

            });

        }
    });
}

/********************drag/drop********************************************* */
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev,id) {
    ev.preventDefault();
    var equipamento_id = ev.dataTransfer.getData("text");
    // console.log(ev)
    gravaEntregaPlaca(id,equipamento_id);
}

function onDragOver(event) {
    event.preventDefault();
}
/********************grava placa na entrega********************************************* */
function gravaEntregaPlaca(idPedido,equipamento_id){
    var dados={
            'venda_entrega_id'  : idPedido
            ,'equipamento_id'   : equipamento_id
        }
    var rota = '/programacaoEntrega/atualizaPlaca'
    $.ajax({
        url :url+rota,
        data:dados,
        type:'POST',
        cache:false,
        success: function(result){

        },
        complete: function(){
            consultaEntrega();
            consultaEquipamentos()
        }
    });
}

function desabilitaDrag(evento){
    if(evento==0){
        $('.sortable').sortable('disable');
    }else{
        $('.sortable').sortable('enable');
    }
}


/***********************************grava************************************ */
function grava(dados,route,type,origem){
    var title = 'Cadastro alterado com sucesso!';
    if(type == 'POST'){
        title = 'Cadastro efetuado com sucesso!';
    }
    $.ajax({
        data: dados,
        type: type,
        dataType: 'JSON',
        url:url+route,
        success: function(result)
        {
            console.log(result,type,origem);
            if(result=="success"){
                Swal({
                    title: title,
                    type: 'success',
                    timer:3000
                })
                if(type!='POST'){
                    window.location.replace(url+'/'+origem);
                }else{
                    $('.limpar').val('');
                    $('select').trigger('chosen:updated');
                }
            }
        }
    })
}

function liberaMenuDisponivel()
{
    var usuario = $(document).find('#usuario').val();
    var dados = {
        'usuario': usuario
    };
    var route = '/menu/disponivel'
    var linhas = '';
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend : function(){
            linhas = '';
            $('#menuDisponivel').html('');
            swal({
                title: 'Aguarde!',
                type: 'warning',
                html: '<strong>Efetuando busca</strong>',
                onOpen: () => {
                    swal.showLoading()
                }
            })
        },
        success: function (result) {
            linhas = '';
            classe = '';
            $.each(result, function (i, val) {
                if(val.tipo=='Título'){
                    classe='negrito';
                }else{
                    classe='paragrafo';
                };
                var id = 0;
                (val.selecionado=="checked")?id = val.selecionadoId : id=val.disponivelId
                linhas += '<tr>';
                    linhas += '<td class="'+classe+'"><button class="btn btn-link" value="'+val.disponivelId+'">'+val.ordem+'-'+val.descricao+'</button></td>';
                    linhas += '<td>';
                        linhas += '<label class="switch" >';
                            linhas += '<input type="checkbox" class="disponivel" id="protrang" name="protrang" '+val.selecionado+' value="'+id+'">';
                            linhas += '<span class="slider round"></span>';
                        linhas += '</label>';
                    linhas += '</td>';
                linhas += '</tr>';
            })

        },
        complete:function(){
            $('#menuDisponivel').html(linhas);
            swal.close();
        }
    })
}

function removeMenuLiberado()
{
    var usuario = $(document).find('#usuario').val();
    var dados = {
        'usuario': usuario
    };
    var route = '/menu/menuLiberado'
    var linhas = '';
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend : function(){
            linhas = '';
            $('#menuLiberado').html('');
            swal({
                title: 'Aguarde!',
                type: 'warning',
                html: '<strong>Efetuando busca</strong>',
                onOpen: () => {
                    swal.showLoading()
                }
            })
        },
        success: function (result) {
        },
        complete:function(){
            $('#menuLiberado').html(linhas);
            swal.close();
        }
    })
}

function addMenuUsuario(disponivelId,usuario){
    var dados = {
        'usuario': usuario,
        'disponivelId' : disponivelId
    };
    var route = '/menu/addMenuUsuario'
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        complete:function(){
            liberaMenuDisponivel();
            removeMenuLiberado();
        }
    })
}
function removeMenuUsuario(liberadoId){
    var dados = {
        'liberadoId' : liberadoId
    };
    var route = '/menu/removeMenuUsuario'
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        complete:function(){
            liberaMenuDisponivel();
            removeMenuLiberado();
        }
    })

}

function checaKm(equipamento){
    var dados = {
        'equipamento': equipamento
    };
    var route = '/apontamento/checaKm'
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        success:function(result){
            console.log(result);
            $('#ultimoKm').val(result.km)
        }
    })
}

function checaHora(equipamento){
    var dados = {
        'equipamento': equipamento
    };
    var route = '/apontamento/checaHora'
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        success:function(result){
            console.log(result);
            $('#ultimaHora').val(result.horas)
        }
    })
}
