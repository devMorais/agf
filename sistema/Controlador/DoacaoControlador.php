<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Nucleo\Helpers;
use sistema\Modelo\DoacaoModelo;
use sistema\Controlador\Pagamento\PagamentoInfinitepayControlador;
use sistema\Nucleo\Mensagem;
use sistema\Suporte\XDebug;

class DoacaoControlador extends Controlador
{
    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    public function index(): void
    {
        echo $this->template->renderizar('doacao.html', []);
    }

    public function processar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        $valor = isset($dados['valor']) ? (float) $dados['valor'] : 0;

        if ($valor < 1 || $valor > 100000) {
            $this->mensagem->erro('O valor da doação deve estar entre R$ 1,00 e R$ 100.000,00.')->flash();
            Helpers::redirecionar('doar');
            return;
        }

        if (empty($dados['nome']) || mb_strlen(trim($dados['nome'])) < 3) {
            $this->mensagem->erro('Informe seu nome completo.')->flash();
            Helpers::redirecionar('doar');
            return;
        }

        if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $this->mensagem->erro('Informe um e-mail válido.')->flash();
            Helpers::redirecionar('doar');
            return;
        }

        $doacao = new DoacaoModelo();
        $doacao->usuario_id = null;
        $doacao->valor = $valor;
        $doacao->status = 'aguardando';

        // --- CAPTURA OS NOVOS DADOS ---
        $doacao->doador_nome = $dados['nome'] ?? null;
        $doacao->doador_email = $dados['email'] ?? null;
        $doacao->doador_telefone = $dados['telefone'] ?? null;

        // Se a checkbox 'anonimo' foi marcada, salva 1. Se não, salva 0.
        $doacao->doador_anonimo = isset($dados['anonimo']) ? 1 : 0;
        // ------------------------------

        $isRecorrente = isset($dados['recorrente']) ? true : false;

        if (!$doacao->salvar()) {
            $this->mensagem->erro('Erro ao gerar doação no banco. Tente novamente.')->flash();
            Helpers::redirecionar('doar');
            return;
        }

        $idDoacao = $doacao->id;

        $controladorIP = new PagamentoInfinitepayControlador();
        $resultado = $controladorIP->processar($doacao, $isRecorrente);

        if ($resultado['erro']) {
            $doacaoFalha = (new DoacaoModelo())->buscaPorId($idDoacao);
            $doacaoFalha->status = 'cancelada';
            $doacaoFalha->salvar();

            $this->mensagem->erro($resultado['mensagem'])->flash();
            Helpers::redirecionar('doar');
            return;
        }

        $doacaoAtualizar = (new DoacaoModelo())->buscaPorId($idDoacao);
        $doacaoAtualizar->infinitepay_link = $resultado['link'];
        $doacaoAtualizar->infinitepay_order_nsu = $resultado['order_nsu'];
        $doacaoAtualizar->infinitepay_slug = $resultado['slug'] ?? null;

        if (!$doacaoAtualizar->salvar()) {
            $erroBancodados = $doacaoAtualizar->erro();
            $textoErro = is_object($erroBancodados) ? $erroBancodados->getMessage() : (string)$erroBancodados;

            $this->mensagem->erro("Falha no banco de dados: " . $textoErro)->flash();
            Helpers::redirecionar('doar');
            return;
        }

        // Sucesso total! Redireciona para o pagamento
        Helpers::redirecionar('doacao/pagamento/' . $idDoacao);
    }
}
