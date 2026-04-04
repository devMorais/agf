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

        if (empty($dados['valor']) || (float)$dados['valor'] < 1) {
            $this->mensagem->erro('O valor da doação deve ser de pelo menos R$ 1,00.')->flash();
            Helpers::redirecionar('doar');
            return;
        }

        $doacao = new DoacaoModelo();
        $doacao->usuario_id = null;
        $doacao->valor = (float) $dados['valor'];
        $doacao->status = 'aguardando';
        $doacao->doador_anonimo = isset($dados['anonimo']) ? 1 : 0;

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

        $doacaoAtualizar->infinitepay_link = $doacao->infinitepay_link;
        $doacaoAtualizar->infinitepay_order_nsu = $doacao->infinitepay_order_nsu;
        $doacaoAtualizar->infinitepay_slug = $doacao->infinitepay_slug;

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
