$(document).ready(function(){
    $(document).find('select').chosen();

    /*********************hoje*********************************************************** */
        today=new Date();
        y=today.getFullYear();
        m=today.getMonth()+1;
        m=("00" + m).slice(-2);
        d=today.getDate();
        d=("00" + d).slice(-2);

        const hoje = y + '-' + m + '-' + d;
        $('#data1').val(hoje);


        /**********sempre que tabalhar com Ajax no Laravel tem que incluir essa tag *************/
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

/***********************colocando duas casas decimais************************************* */
    var decimal = $('.floatNumberField').attr('decimal');
    $('.floatNumberField').val(parseFloat($('.floatNumberField').val()).toFixed(decimal));

    $(".floatNumberField").on('change',function(){
        var decimal = $(this).attr('decimal');
        $(this).val(parseFloat($(this).val()).toFixed(decimal));
    });
/**********************formata numero **************************************************/
    const formCurrency = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2
    })


/*************************pegando a url do servidor**************************************/

    url = $('input#appurl').val();
    var proCodigo = $(document).find('#produto').val();

/************************ buscaCep ******************************************************/
    $(document).on('blur', 'input#cep', function(event){
        event.preventDefault() // não permite que o navegador faça o submit
        var cep = $(this).val();
        var endereco = $('input#endereco').val().trim();
        if(endereco==''){
            buscaCep(cep);
        };
    })

/************************ buscaCnpj ******************************************************/
    $(document).on('blur', 'input#cnpj', function(event){
        var cnpj=$(this).val().replace('.','').replace('/','').replace('-','');

        if(cnpj.length>=14){
            buscaCnpj(cnpj);
        };
    })


/***********************mensagem confirma exclusão **************************************/
    $(document).on('click', '.delete', function(event){
        event.preventDefault()
        Swal({
            title: 'Deseja realmente excluir?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Remover'
        }).then((result) => {
            if (result.value) {
                var form = $(this).parent()
                form.submit()
            }
        });
    })

    /**********************time intervel *********************************************************************/
    // atualizaCards();
    // setInterval(function(){
    //     atualizaCards();
    // }, 5000);

    /**********************FORMATA VALOR DIGITAR ***************************************************************/
    $('.formataValor').on('change',function(event){
        var valor  = parseFloat($(this).val().replace('.','').replace(',','.'));
        valor = formCurrency.format(valor).replace('R$','');
        $(this).val(valor);
    })

    /**********************FORMATA CNPJ DIGITAR ***************************************************************/
    $('#cnpj').on('keyup',function(){
        var cnpj = $(this).val().replaceAll('.','').replaceAll('-','').replaceAll('/','');
        $(this).val(cnpj);

        if(cnpj.length>=11 && cnpj.length<14){
            $(this).val(cnpj.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4"))
        }else if(cnpj.length>=14){
            $(this).val(cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5"))
        }
    })


    /**********************gravar menu com ajax **************************************************/
    $(document).on('submit', 'form#cadastro-menu', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var descricao           = $(this).find('input#descricao').val();
        var tipo                = $(this).find('select#tipo').val();
        var ordem               = $(this).find('input#ordem').val();
        var rota                = $(this).find('input#rota').val();
        var icone               = $(this).find('input#icone').val();


        /********************************************************************************************* */
        if(!descricao || !tipo || !ordem ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'descricao' : descricao
                ,'tipo'     : tipo
                ,'ordem'    : ordem
                ,'rota'     : rota
                ,'icone'    : icone
            }
            grava(dados,route,type,origem);
        }
    })


    /***********************liberaMenu *****************************/
    $('#usuario').on('change',function(){
        liberaMenuDisponivel();
        removeMenuLiberado();
    })

    $(document).on('click','input.disponivel',function(event){
        if($(this).is(":checked")){
            var disponivelId = $(this).val();
            var usuario = $(document).find('#usuario').val();
            addMenuUsuario(disponivelId,usuario)
        }else{
            var liberadoId = $(this).val();
            removeMenuUsuario(liberadoId)
        }
    })
    $(document).on('click','button.liberado',function(event){
        var liberadoId = $(this).val();
        removeMenuUsuario(liberadoId)
    })


    /**********************gravar equipamento **************************************************/
    $(document).on('submit', 'form#cadastro-equipamento', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var placa               = $(this).find('input#placa').val();
        var equipamento         = $(this).find('input#equipamento').val();
        var ano                 = $(this).find('input#ano').val();
        var ativo               = $(this).find('select#ativo').val();
        var uf                  = $(this).find('select#uf').val();
        var data_partida        = $(this).find('input#data_partida').val();
        var tipo                = $(this).find('select#tipo').val();
        var atividade           = $(this).find('select#atividade').val();
        var cilindros           = $(this).find('input#cilindros').val();
        var operacao            = $(this).find('select#operacao').val();
        var consumo_minimo      = $(this).find('input#consumo_minimo').val();
        var consumo_maximo      = $(this).find('input#consumo_maximo').val();


        /********************************************************************************************* */
        if(!placa || !equipamento || !ativo || !data_partida ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'placa'             : placa
                ,'equipamento'      : equipamento
                ,'ano'              : ano
                ,'ativo'            : ativo
                ,'uf'               : uf
                ,'data_partida'     : data_partida
                ,'tipo'             : tipo
                ,'atividade'        : atividade
                ,'cilindros'        : cilindros
                ,'operacao'         : operacao
                ,'consumo_minimo'   : consumo_minimo
                ,'consumo_maximo'   : consumo_maximo
            }
            // console.log(dados,route,type,origem);
            grava(dados,route,type,origem);
        }
    })


    /**********************gravar apontamento **************************************************/
    $(document).on('submit', 'form#cadastro-apontamento', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var data                = $(this).find('input#data').val();
        var equipamento         = $(this).find('select#placa').val();
        var litros              = $(this).find('input#litros').val();
        var km                  = $(this).find('input#km').val();
        var horas               = $(this).find('input#horas').val();
        var combustivel         = $(this).find('select#combustivel').val();
        var obs                 = $(this).find('input#obs').val();
        var origemAbastecimento = $(this).find('select#origemAbastecimento').val();
        var ultimo_km           = $(this).find('select#ultimo_km').val();


        /********************************************************************************************* */
        if(!data || !equipamento || !km || !horas || !litros || !combustivel || !origem ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'data'              : data
                ,'equipamento'      : equipamento
                ,'litros'           : litros
                ,'km'               : km
                ,'horas'            : horas
                ,'combustivel'      : combustivel
                ,'obs'              : obs
                ,'origemAbastecimento':origemAbastecimento
                ,'ultimo_km'        :ultimo_km
            }
            // console.log(dados,route,type,origem);
            grava(dados,route,type,origem);
        }
    })


    /**********************gravar transferencia **************************************************/
    $(document).on('submit', 'form#cadastro-transferencia', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var data                = $(this).find('input#data').val();
        var origem              = $(this).find('select#origem').val();
        var destino             = $(this).find('select#destino').val();
        var litros              = $(this).find('input#litros').val();
        var nr_doc              = $(this).find('input#nr_doc').val();
        var combustivel         = $(this).find('select#combustivel').val();
        var horimetro           = $(this).find('input#horimetro').val();
        var operador            = $(this).find('select#operador').val();
        var fazenda             = $(this).find('select#fazenda').val();
        var horimetro_inicial   = $(this).find('input#horimetro_inicial').val();
        var horimetro_final     = $(this).find('input#horimetro_final').val();
        var tanque              = $(this).find('select#tanque').val();
        var obs                 = $(this).find('input#obs').val();




        /********************************************************************************************* */
        if(!data || !combustivel || !origem || !destino || !litros ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'data'                  : data
                ,'origem'               : origem
                ,'destino'              : destino
                ,'litros'               : litros
                ,'nr_doc'               : nr_doc
                ,'combustivel'          : combustivel
                ,'horimetro'            : horimetro
                ,'operador'             : operador
                ,'fazenda'              : fazenda
                ,'horimetro_inicial'    : horimetro_inicial
                ,'horimetro_final'      : horimetro_final
                ,'tanque'               : tanque
                ,'obs'                  : obs

            }
        grava(dados,route,type,origem);
        }
    })

    /**********************gravar arrasto **************************************************/
    $(document).on('submit', 'form#cadastro-arrasto', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var arrasto             = $(this).find('input#arrasto').val();


        /********************************************************************************************* */
        if(!arrasto ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'arrasto'             : arrasto

            }
        grava(dados,route,type,origem);
        }
    })


    /**********************gravar baldeio **************************************************/
    $(document).on('submit', 'form#cadastro-baldeio', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var baldeio             = $(this).find('input#baldeio').val();


        /********************************************************************************************* */
        if(!baldeio ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'baldeio'             : baldeio

            }
        grava(dados,route,type,origem);
        }
    })


       /**********************gravar colaborador **************************************************/
    $(document).on('submit', 'form#cadastro-colaborador', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var colaborador         = $(this).find('input#colaborador').val();
        var uf                  = $(this).find('select#uf').val();
        var ativo               = $(this).find('select#ativo').val();
        var cod                 = $(this).find('input#cod').val();
        var empresa             = $(this).find('select#empresa').val();

        /********************************************************************************************* */
        if(!colaborador ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'colaborador'      : colaborador
                ,'uf'              : uf
                ,'ativo'           : ativo
                ,'cod'             : cod
                ,'empresa'         : empresa

            }
        grava(dados,route,type,origem);
        }
    })

      /**********************gravar comboio **************************************************/
    $(document).on('submit', 'form#cadastro-comboio', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var tanque             = $(this).find('input#tanque').val();
        var capacidade         = $(this).find('input#capacidade').val();
        var fazenda            = $(this).find('select#fazenda').val();
        var uf                 = $(this).find('select#uf').val();
        var obs                = $(this).find('input#obs').val();

        /********************************************************************************************* */
        if(!tanque ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'tanque'            : tanque
                ,'capacidade'       : capacidade
                ,'fazenda'          : fazenda
                ,'uf'               : uf
                ,'obs'              : obs

            }
        grava(dados,route,type,origem);
        }
    })

         /**********************gravar fazenda **************************************************/
    $(document).on('submit', 'form#cadastro-fazenda', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var fazenda             = $(this).find('input#fazenda').val();
        var uf                  = $(this).find('select#uf').val();
        var apontador           = $(this).find('select#apontador').val();
        var ativa               = $(this).find('select#ativa').val();

        /********************************************************************************************* */
        if(!fazenda ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'fazenda'             : fazenda
                ,'uf'                 : uf
                ,'apontador'          : apontador
                ,'ativa'              : ativa

            }
        grava(dados,route,type,origem);
        }
    })
/**********************gravar corte **************************************************/
    $(document).on('submit', 'form#cadastro-corte', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var corte             = $(this).find('input#corte').val();


/********************************************************************************************* */
        if(!corte ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'corte'             : corte
            }
        grava(dados,route,type,origem);
        }
    })

 /**********************gravar comprimento_madeira **************************************************/
    $(document).on('submit', 'form#cadastro-comprimento_madeira', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var comprimento             = $(this).find('input#comprimento').val();


/********************************************************************************************* */
        if(!comprimento ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'comprimento'             : comprimento
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar fornecedor **************************************************/
    $(document).on('submit', 'form#cadastro-fornecedor', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var fornecedor            = $(this).find('input#fornecedor').val();
        var cod_cargo             = $(this).find('input#cod_cargo').val();


/********************************************************************************************* */
        if(!fornecedor ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'fornecedor'             : fornecedor
                ,'cod_cargo'             : cod_cargo
            }
        grava(dados,route,type,origem);
        }
    })
/**********************gravar lubrificante **************************************************/
    $(document).on('submit', 'form#cadastro-lubrificante', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var lubrificante             = $(this).find('input#lubrificante').val();


/********************************************************************************************* */
        if(!lubrificante ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'lubrificante'             : lubrificante
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar peca **************************************************/
    $(document).on('submit', 'form#cadastro-peca', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var peca             = $(this).find('input#peca').val();
        var cod_cargo             = $(this).find('input#cod_cargo').val();


/********************************************************************************************* */
        if(!peca ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'peca'             : peca
                ,'cod_cargo'             : cod_cargo
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar situacao_manutencao **************************************************/
    $(document).on('submit', 'form#cadastro-situacao_manutencao', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var situacao             = $(this).find('input#situacao').val();


/********************************************************************************************* */
        if(!situacao){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'situacao'             : situacao
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar tipo_manutencao **************************************************/
    $(document).on('submit', 'form#cadastro-tipo_manutencao', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var tipo             = $(this).find('input#tipo').val();


/********************************************************************************************* */
        if(!tipo ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'tipo'             : tipo
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar operacao **************************************************/
    $(document).on('submit', 'form#cadastro-operacao', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var operacao             = $(this).find('input#operacao').val();


/********************************************************************************************* */
        if(!operacao ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'operacao'             : operacao
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar tipo **************************************************/
    $(document).on('submit', 'form#cadastro-tipo', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var tipo             = $(this).find('input#tipo').val();


/********************************************************************************************* */
        if(!tipo ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'tipo'             : tipo
            }
        grava(dados,route,type,origem);
        }
    })
/**********************gravar atividade **************************************************/
    $(document).on('submit', 'form#cadastro-atividade', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var atividade             = $(this).find('input#atividade').val();


/********************************************************************************************* */
        if(!atividade ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'atividade'             : atividade
            }
        grava(dados,route,type,origem);
        }
    })

    /**********************gravar clima **************************************************/
    $(document).on('submit', 'form#cadastro-clima', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var combustivel         = $(this).find('input#combustivel').val();
        var unidade             = $(this).find('input#unidade').val();


/********************************************************************************************* */
        if(!combustivel ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'combustivel'          : combustivel
                ,'unidade'             : unidade
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar clima **************************************************/
    $(document).on('submit', 'form#cadastro-clima', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var clima         = $(this).find('input#clima').val();

/********************************************************************************************* */
        if(!clima ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'clima'          : clima
            }
        grava(dados,route,type,origem);
        }
    })
/**********************gravar terreno **************************************************/
    $(document).on('submit', 'form#cadastro-terreno', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var terreno         = $(this).find('input#terreno').val();

/********************************************************************************************* */
        if(!terreno ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'terreno'          : terreno
            }
        grava(dados,route,type,origem);
        }
    })

/**********************gravar informacao de manutencao **************************************************/
    $(document).on('submit', 'form#cadastro-manutencao', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var data                = $(this).find('input#data').val();
        var ord_servico         = $(this).find('input#ord_servico').val();
        var fazenda             = $(this).find('select#fazenda').val();
        var maquina             = $(this).find('select#maquina').val();
        var maquina             = $(this).find('select#maquina').val();
        var hora_inicial        = $(this).find('input#hora_inicial').val();
        var hora_final          = $(this).find('input#hora_final').val();
        var horimetro           = $(this).find('input#horimetro').val();
        var tipo_manutencao     = $(this).find('select#tipo_manutencao').val();
        var custo               = $(this).find('input#custo').val();
        var manutencao_diaria   = $(this).find('input#manutencao_diaria').val();
        var situacao            = $(this).find('select#situacao').val();
        var obs                 = $(this).find('input#obs').val();


/********************************************************************************************* */
        if(!data ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'data'                  : data
                ,'ord_servico'          : ord_servico
                ,'fazenda'              : fazenda
                ,'maquina'              : maquina
                ,'operador'             : operador
                ,'hora_inicial'         : hora_inicial
                ,'hora_final'           : hora_final
                ,'horimetro'            : horimetro
                ,'tipo_manutencao'      : tipo_manutencao
                ,'custo'                : custo
                ,'manutencao_diaria'    : manutencao_diaria
                ,'situacao'             : situacao
                ,'obs'                  : obs

            }
        grava(dados,route,type,origem);
        }
    })
/**********************gravar informacao diária **************************************************/
    $(document).on('submit', 'form#cadastro-informacao', function(event){
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('#origem').val();

        var data                    = $(this).find('input#data').val();
        var fazenda                 = $(this).find('input#fazenda').val();
        var equipamento             = $(this).find('select#equipamento').val();
        var atividade               = $(this).find('select#atividade').val();
        var colaborador             = $(this).find('select#colaborador').val();
        var hora_inicial            = $(this).find('input#hora_inicial').val();
        var hora_final              = $(this).find('input#hora_final').val();
        var horimetro_inicial       = $(this).find('input#horimetro_inicial').val();
        var horimetro_final         = $(this).find('select#horimetro_final').val();
        var corte                   = $(this).find('input#corte').val();
        var fat_m                   = $(this).find('input#fat_m').val();
        var origem_abastecimento    = $(this).find('select#origem_abastecimento').val();
        var nr_nf                   = $(this).find('input#nr_nf').val();
        var qnt_diesel              = $(this).find('input#qnt_diesel').val();
        var horimetro_abastecimento = $(this).find('input#horimetro_abastecimento').val();
        var relogio_tanque_inicial  = $(this).find('input#relogio_tanque_inicial').val();
        var relogio_tanque_final    = $(this).find('input#relogio_tanque_final').val();
        var qnt_lubrificante        = $(this).find('input#qnt_lubrificante').val();
        var tipo_lubrificante       = $(this).find('input#tipo_lubrificante').val();
        var carregamento            = $(this).find('input#carregamento').val();
        var veiculo_carregado       = $(this).find('input#veiculo_carregado').val();
        var descarregamento         = $(this).find('input#descarregamento').val();
        var veiculo_descarregado    = $(this).find('input#veiculo_descarregado').val();
        var producao_terceiros      = $(this).find('input#producao_terceiros').val();
        var comprimento_madeira     = $(this).find('input#comprimento_madeira').val();
        var baldeio_curto           = $(this).find('input#baldeio_curto').val();
        var baldeio_medio           = $(this).find('input#baldeio_medio').val();
        var baldeio_longo           = $(this).find('input#baldeio_longo').val();
        var arrasto_curto           = $(this).find('input#arrasto_curto').val();
        var arrasto_medio           = $(this).find('input#arrasto_medio').val();
        var arrasto_longo           = $(this).find('input#arrasto_longo').val();
        var clima                   = $(this).find('input#clima').val();
        var terreno                 = $(this).find('input#terreno').val();
        var obs                     = $(this).find('input#obs').val();


/********************************************************************************************* */
        if(!data ){
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer:3000
            })
        }else{
            var dados= {
                'data'                      : data
                ,'fazenda'                  : fazenda
                ,'equipamento'              : equipamento
                ,'atividade'                : atividade
                ,'colaborador'              : colaborador
                ,'hora_inicial'             : hora_inicial
                ,'hora_final'               : hora_final
                ,'horimetro_inicial'        : horimetro_inicial
                ,'horimetro_final'          : horimetro_final
                ,'corte'                    : corte
                ,'fat_m'                    : fat_m
                ,'origem_abastecimento'     : origem_abastecimento
                ,'nr_nf'                    : nr_nf
                ,'qnt_diesel'               : qnt_diesel
                ,'horimetro_abastecimento'  : horimetro_abastecimento
                ,'relogio_tanque_inicial'   : relogio_tanque_inicial
                ,'relogio_tanque_final'     : relogio_tanque_final
                ,'qnt_lubrificante'         : qnt_lubrificante
                ,'tipo_lubrificante'        : tipo_lubrificante
                ,'carregamento'             : carregamento
                ,'veiculo_carregado'        : veiculo_carregado
                ,'descarregamento'          : descarregamento
                ,'veiculo_descarregado'     : veiculo_descarregado
                ,'producao_terceiros'       : producao_terceiros
                ,'comprimento_madeira'      : comprimento_madeira
                ,'baldeio_curto'            : baldeio_curto
                ,'baldeio_medio'            : baldeio_medio
                ,'baldeio_longo'            : baldeio_longo
                ,'arrasto_curto'            : arrasto_curto
                ,'arrasto_medio'            : arrasto_medio
                ,'arrasto_longo'            : arrasto_longo
                ,'clima'                    : clima
                ,'terreno'                  : terreno
                ,'obs'                      : obs

            }
        grava(dados,route,type,origem);
        }
    })

/************************checa km ***********************************************************/
    $(document).on('change','select#placa',function(event){
        var equipamento = $(this).val();
        checaKm(equipamento);
    })
    $(document).on('blur','#km',function(event){
        var kmAtual = $(this).val();
        var kmAnterior = $('#ultimoKm').val();
        if(kmAtual){
            if(kmAnterior>=kmAtual){
                Swal({
                    title: 'Km Atual menor que Km anterior!',
                    type: 'warning',
                    timer:1000
                })
            }
        }
    })
    /************************checa hora ***********************************************************/
    $(document).on('change','select#placa',function(event){
        var equipamento = $(this).val();
        checaHora(equipamento);
    })
    $(document).on('blur','#horas',function(event){
        var horaAtual = $(this).val();
        var horaAnterior = $('#ultimaHora').val();
        if(horaAtual){
            if(horaAnterior>=horaAtual){
                Swal({
                    title: 'Hora Atual menor que Hora anterior!',
                    type: 'warning',
                    timer:1000
                })
            }
        }
    })

})

