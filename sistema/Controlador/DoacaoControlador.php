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

    /**
     * Página /doar2: formulário antigo (nome + e-mail), que recebe na conta antiga da InfinitePay
     */
    public function contaAntiga(): void
    {
        echo $this->template->renderizar('doacao_conta_antiga.html', []);
    }

    public function processar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];
        $telefoneDigitos = preg_replace('/\D/', '', $dados['telefone'] ?? '');

        $erro = $this->validarValorENome($dados);
        if (!$erro && strlen($telefoneDigitos) < 10) {
            $erro = 'Informe um WhatsApp válido com DDD.';
        }

        if ($erro) {
            $this->mensagem->erro($erro)->flash();
            Helpers::redirecionar('doar');
            return;
        }

        $this->registrarDoacao($dados, 'doar', false);
    }

    public function processarContaAntiga(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];
        $telefoneDigitos = preg_replace('/\D/', '', $dados['telefone'] ?? '');

        $erro = $this->validarValorENome($dados);
        if (!$erro && (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL))) {
            $erro = 'Informe um e-mail válido.';
        }
        // WhatsApp é opcional neste formulário, mas se vier precisa ter DDD
        if (!$erro && $telefoneDigitos !== '' && strlen($telefoneDigitos) < 10) {
            $erro = 'Informe um WhatsApp válido com DDD.';
        }
        if (!$erro && !defined('INFINITEPAY_HANDLE_ANTIGO')) {
            $erro = 'Esta forma de doação está indisponível no momento.';
        }

        if ($erro) {
            $this->mensagem->erro($erro)->flash();
            Helpers::redirecionar('doar2');
            return;
        }

        $this->registrarDoacao($dados, 'doar2', true);
    }

    private function validarValorENome(array $dados): ?string
    {
        $valor = (float) ($dados['valor'] ?? 0);

        if ($valor < 1 || $valor > 100000) {
            return 'O valor da doação deve estar entre R$ 1,00 e R$ 100.000,00.';
        }

        if (empty($dados['nome']) || mb_strlen(trim($dados['nome'])) < 3) {
            return 'Informe seu nome completo.';
        }

        return null;
    }

    /**
     * Salva a doação, gera o link na InfinitePay e redireciona para o pagamento.
     * Em caso de erro volta para $rotaFormulario. Com $contaAntiga, recebe no INFINITEPAY_HANDLE_ANTIGO.
     */
    private function registrarDoacao(array $dados, string $rotaFormulario, bool $contaAntiga): void
    {
        $doacao = new DoacaoModelo();
        $doacao->usuario_id = null;
        $doacao->valor = (float) $dados['valor'];
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
            Helpers::redirecionar($rotaFormulario);
            return;
        }

        $idDoacao = $doacao->id;

        $controladorIP = new PagamentoInfinitepayControlador();
        $resultado = $controladorIP->processar($doacao, $isRecorrente, $contaAntiga);

        if ($resultado['erro']) {
            $doacaoFalha = (new DoacaoModelo())->buscaPorId($idDoacao);
            $doacaoFalha->status = 'cancelada';
            $doacaoFalha->salvar();

            $this->mensagem->erro($resultado['mensagem'])->flash();
            Helpers::redirecionar($rotaFormulario);
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
            Helpers::redirecionar($rotaFormulario);
            return;
        }

        // Sucesso total! Redireciona para o pagamento
        Helpers::redirecionar('doacao/pagamento/' . $idDoacao);
    }
}
