$(document).ready(function () {
    $("#busca").keyup(function () {
        var busca = $(this).val();
        if (busca.length > 10) {
            $.ajax({
                url: $('form').attr('data-url-busca'),
                method: 'POST',
                data: {
                    busca: busca
                },
                success: function (resultado) {
                    if (resultado) {
                        $('#usuario_id').val(resultado); // Define o resultado como o valor do campo de entrada do usuário
                        $('#resultadoBuscarCpf').text("Cód: " + resultado); // Preenche o resultado também no <span> com "Cód: "
                        $('#resultadoBuscarCpf').removeClass('text-danger'); // Remove a classe de erro, se existir
                    } else {
                        $('#usuario_id').val(""); // Define como vazio se nenhum resultado for encontrado
                        $('#resultadoBuscarCpf').text('Nenhum resultado encontrado');
                        $('#resultadoBuscarCpf').addClass('text-danger'); // Adiciona a classe de erro ao <span>
                    }
                }
            });
            $('#resultadoBuscarCpf').show();
        } else {
            $('#usuario_id').val(""); // Define como vazio se o comprimento da busca for menor que 10
            $('#resultadoBuscarCpf').text(""); // Limpa o texto do <span>
            $('#resultadoBuscarCpf').hide();
        }
    });
});


document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('pre').forEach((el) => {
        hljs.highlightElement(el);
    });
});


