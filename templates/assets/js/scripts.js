//################# BOOTSTRAP #####################
$(document).ready(function () {

    buscarEndereco();
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[tooltip="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    $('.formularioAjax').submit(function (event) {
        event.preventDefault();
        var carregando = $('.ajaxLoading');
        var botao = $(':input[type="submit"]');
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                carregando.show().fadeIn(200);
                botao.prop('disabled', false).addClass('disabled');
            },
            success: function (retorno) {
                if (retorno.erro) {
                    alerta(retorno.erro, 'yellow');
                }
                if (retorno.successo) {
                    $('.formularioAjax')[0].reset();
                    $('#contatoModal').modal('hide');
                    alerta(retorno.successo, 'green');
                }
                if (retorno.redirecionar) {
                    window.location.href = retorno.redirecionar;
                }
            },
            complete: function () {
                carregando.hide().fadeOut(200);
                botao.removeClass('disabled');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                alerta("Erro ao enviar o formulário.", "red");
            }
        });
    });
});

function buscarEndereco() {
    console.log("Função buscarEndereco() foi chamada");
    $('#cep').on('input', function () {
        var cep = $(this).val().replace(/\D/g, '');
        var validacep = /^[0-9]{8}$/;
        if (validacep.test(cep)) {
            $.getJSON("https://viacep.com.br/ws/" + cep + "/json/", function (dados) {
                if (!("erro" in dados)) {
                    $("#endereco").val(dados.logradouro);
                    $("#bairro").val(dados.bairro);
                    $("#cidade").val(dados.localidade);
                    $("#estado").val(dados.uf);
                } else {
                    alerta("CEP não encontrado.", "red");
                }
            }).fail(function () {
                alerta("Erro ao buscar informações do CEP.", "red");
            });
        }
    });
}
function alerta(mensagem, cor) {
    new jBox('Notice', {
        content: mensagem,
        color: cor,
        animation: 'tada',
        showCountdown: true,
        autoClose: 5000,
        zIndex: 9999
    });
}

