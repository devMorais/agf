// A função $(document).ready() garante que o código só será executado 
// depois que toda a página HTML for carregada.
$(document).ready(function () {

    // Inicializa a função de busca de endereço por CEP
    buscarEndereco();

    // Inicializa os tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[tooltip="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Aplica a máscara de data no campo de nascimento
    // Colocamos aqui dentro para garantir que o campo #nascimento já existe na página
    $('#nascimento').mask('00/00/0000');

    // Intercepta o envio de qualquer formulário com a classe .formularioAjax
    $('.formularioAjax').submit(function (event) {
        // Impede o recarregamento da página
        event.preventDefault();

        var carregando = $('.ajaxLoading'); // Elemento de loading (precisa existir no seu HTML)
        var botao = $(this).find(':input[type="submit"]');

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                carregando.show();
                botao.prop('disabled', true).addClass('disabled');
            },
            success: function (retorno) {
                // Se houver erro, exiba o alerta e pare
                if (retorno.erro) {
                    alerta(retorno.erro, 'yellow');
                    return;
                }

                // Se houver sucesso
                if (retorno.successo) {
                    // Limpa o formulário
                    $('.formularioAjax')[0].reset();

                    // Exibe a mensagem de sucesso
                    alerta(retorno.successo, 'green');

                    // Se houver uma URL para redirecionar, aguarde 3 segundos e redirecione
                    if (retorno.redirecionar) {
                        setTimeout(function () {
                            window.location.href = retorno.redirecionar;
                        }, 3000); // 3 segundos
                    }
                }
            },
            complete: function () {
                carregando.hide();
                botao.prop('disabled', false).removeClass('disabled');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                alerta("Erro de comunicação ao enviar o formulário. Verifique sua conexão.", "red");
            }
        });
    });
});

/**
 * Função para buscar endereço via CEP usando a API ViaCEP.
 */
function buscarEndereco() {
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

/**
 * Função para exibir alertas usando a biblioteca jBox.
 * @param {string} mensagem A mensagem a ser exibida.
 * @param {string} cor A cor do alerta (ex: 'green', 'red', 'yellow').
 */
function alerta(mensagem, cor) {
    new jBox('Notice', {
        content: mensagem,
        color: cor,
        animation: 'tada',
        showCountdown: true,
        autoClose: 5000, // <-- ADICIONE ESTA LINHA (5000 milissegundos = 5 segundos)
        zIndex: 9999
    });
}