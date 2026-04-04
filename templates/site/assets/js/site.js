function selecionarValor(elemento, valor) {
    let botoes = document.querySelectorAll('.value-btn');
    botoes.forEach(function (btn) {
        btn.classList.remove('active');
    });

    elemento.classList.add('active');
    document.getElementById('valor').value = valor;
}

function limparSelecao() {
    let botoes = document.querySelectorAll('.value-btn');
    botoes.forEach(function (btn) {
        btn.classList.remove('active');
    });
}