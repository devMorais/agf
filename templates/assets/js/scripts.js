$(document).ready(function () {

    // === BOOTSTRAP TOOLTIPS ===
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[tooltip="tooltip"]'));
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // === BUSCA VIA CEP ===
    buscarEndereco();

    // === FORMULÁRIOS AJAX ===
    $(document).on('submit', '.formularioAjax', function (event) {
        event.preventDefault();
        var form = $(this);
        var carregando = $('.ajaxLoading');
        var botao = form.find(':input[type="submit"], button[type="submit"]');

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: new FormData(form[0]),
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                carregando.fadeIn(200);
                botao.prop('disabled', true);
            },
            success: function (retorno) {
                if (retorno.erro) {
                    alerta(retorno.erro, 'yellow');
                }
                if (retorno.successo) {
                    form[0].reset();
                    // Fecha qualquer modal aberto
                    var modal = form.closest('.modal');
                    if (modal.length) {
                        var bsModal = bootstrap.Modal.getInstance(modal[0]);
                        if (bsModal) bsModal.hide();
                    }
                    alerta(retorno.successo, 'green');
                }
                if (retorno.redirecionar) {
                    window.location.href = retorno.redirecionar;
                }
            },
            complete: function () {
                carregando.fadeOut(200);
                botao.prop('disabled', false);
            },
            error: function () {
                alerta('Erro ao enviar o formulário. Tente novamente.', 'red');
            }
        });
    });

    // === BACK TO TOP ===
    var backToTop = $('#backToTop');
    if (backToTop.length) {
        $(window).on('scroll.backToTop', function () {
            if ($(this).scrollTop() > 400) {
                backToTop.addClass('show');
            } else {
                backToTop.removeClass('show');
            }
        });

        backToTop.on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 400);
        });
    }

    // === BUSCA LIVE (autocomplete) ===
    var buscaInput  = $('#busca');
    var buscaBox    = $('#buscaResultado');
    var urlBusca    = buscaInput.closest('form').data('url-busca') || $('[data-url-busca]').first().data('url-busca');
    var buscaTimer  = null;

    buscaInput.on('input', function () {
        clearTimeout(buscaTimer);
        var q = $.trim($(this).val());

        if (q.length < 2) {
            buscaBox.hide().empty();
            return;
        }

        buscaTimer = setTimeout(function () {
            $.ajax({
                type: 'POST',
                url: urlBusca || $('[data-url-busca]').data('url-busca'),
                data: { busca: q },
                dataType: 'html',
                success: function (html) {
                    if (html && html.trim() !== '') {
                        buscaBox.html(html).fadeIn(150);
                    } else {
                        buscaBox.hide().empty();
                    }
                },
                error: function () {
                    buscaBox.hide().empty();
                }
            });
        }, 300);
    });

    // Fecha ao clicar fora
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#busca, #buscaResultado').length) {
            buscaBox.fadeOut(150);
        }
    });

});

// === BUSCA CEP ===
function buscarEndereco() {
    $('#cep').on('input', function () {
        var cep = $(this).val().replace(/\D/g, '');
        if (/^[0-9]{8}$/.test(cep)) {
            $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function (dados) {
                if (!('erro' in dados)) {
                    $('#endereco').val(dados.logradouro);
                    $('#bairro').val(dados.bairro);
                    $('#cidade').val(dados.localidade);
                    $('#estado').val(dados.uf);
                } else {
                    alerta('CEP não encontrado.', 'red');
                }
            }).fail(function () {
                alerta('Erro ao buscar informações do CEP.', 'red');
            });
        }
    });
}

// === ALERTA (jBox Notice) ===
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
