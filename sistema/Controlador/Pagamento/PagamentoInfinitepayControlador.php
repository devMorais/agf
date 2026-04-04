<?php

namespace sistema\Controlador\Pagamento;

use sistema\Nucleo\Controlador;
use sistema\Nucleo\Helpers;
use sistema\Modelo\DoacaoModelo;
use sistema\Biblioteca\InfinitePay;

class PagamentoInfinitepayControlador extends Controlador
{
    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    public function processar(DoacaoModelo $doacao, bool $isRecorrente = false): array
    {
        $infinitePay = new InfinitePay($doacao->id);
        $urlRetorno = Helpers::url('doacao/pagamento/' . $doacao->id);

        $resultado = $infinitePay->gerarLinkPagamento(
            [['quantidade' => 1, 'valor' => (float) $doacao->valor, 'descricao' => 'Doação - Associação Grande Família']],
            'doacao-' . $doacao->id,
            null,
            $urlRetorno
        );

        if ($resultado['erro']) {
            return $resultado;
        }

        $doacao->infinitepay_link = $resultado['link'];
        $doacao->infinitepay_order_nsu = $resultado['order_nsu'];
        $doacao->infinitepay_slug = $resultado['slug'] ?? null;

        return ['erro' => false];
    }

    public function exibirTelaPagamento(int $id): void
    {
        $doacao = (new DoacaoModelo())->buscaPorId($id);

        if (!$doacao) {
            Helpers::redirecionar('doar');
            return;
        }

        $infinitePay = new InfinitePay($doacao->id);
        $voltouDoGateway = isset($_GET['transaction_nsu']) || isset($_GET['slug']);

        if ($doacao->status === 'aguardando') {
            if ($voltouDoGateway) {
                $transactionNsu = $_GET['transaction_nsu'] ?? null;
                $slug = $_GET['slug'] ?? $doacao->infinitepay_slug;

                if ($transactionNsu) $doacao->infinitepay_transaction_nsu = $transactionNsu;
                if ($slug) $doacao->infinitepay_slug = $slug;
                $doacao->salvar();

                // Verifica pagamento na API
                $res = $infinitePay->verificarPagamento($doacao->infinitepay_order_nsu, $transactionNsu, $slug);

                if (!$res['erro'] && $res['paid']) {
                    $doacao->metodo = ($res['capture_method'] ?? '') === 'pix' ? 'PIX' : 'CARTAO';
                    $doacao->status = 'confirmada';

                    // === A MÁGICA ACONTECE AQUI ===
                    // Se a InfinitePay retornar os dados do cliente, nós salvamos!
                    if (isset($res['dados']->customer)) {
                        $cliente = $res['dados']->customer;
                        $doacao->doador_nome = $cliente->name ?? null;
                        $doacao->doador_email = $cliente->email ?? null;
                        $doacao->doador_cpf = $cliente->document_number ?? $cliente->cpf ?? null;
                        $doacao->doador_telefone = $cliente->phone_number ?? null;
                    }
                    // ===============================

                    $doacao->salvar();
                }
            } else {
                if (!empty($doacao->infinitepay_link)) {
                    echo "<div style='display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;background:#f0f2f5;'>";
                    echo "<div style='text-align:center;padding:40px;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.1);'>";
                    echo "<h2 style='color:#084298;'>Redirecionando para pagamento seguro...</h2>";
                    echo "<p>Você está sendo levado para o ambiente da InfinitePay.</p>";
                    echo "<a href='{$doacao->infinitepay_link}' style='display:inline-block;margin-top:20px;padding:10px 20px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:5px;'>Clique aqui se não for redirecionado</a>";
                    echo "</div></div>";
                    echo "<script>window.location.href = '{$doacao->infinitepay_link}';</script>";
                    exit;
                }
            }
        }

        echo $this->template->renderizar('pagamento/concluido.html', [
            'titulo' => $doacao->status == 'confirmada' ? 'Doação Confirmada' : 'Aguardando Pagamento',
            'doacao' => $doacao
        ]);
    }

    public function webhook(): void
    {
        $json = file_get_contents('php://input');
        $dados = json_decode($json, true);

        if (!$dados || !isset($dados['order_nsu'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'order_nsu não fornecido']);
            exit;
        }

        $doacao = (new DoacaoModelo())->busca("infinitepay_order_nsu = :nsu", "nsu={$dados['order_nsu']}")->resultado();

        if (!$doacao) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Doação não encontrada']);
            exit;
        }

        if ($doacao->status !== 'confirmada') {
            if (isset($dados['transaction_nsu'])) $doacao->infinitepay_transaction_nsu = $dados['transaction_nsu'];
            if (isset($dados['invoice_slug'])) $doacao->infinitepay_slug = $dados['invoice_slug'];

            $doacao->metodo = ($dados['capture_method'] ?? '') === 'pix' ? 'PIX' : 'CARTAO';
            $doacao->status = 'confirmada';

            // === MESMA COISA PARA O WEBHOOK ===
            if (isset($dados['customer'])) {
                $cliente = (object) $dados['customer'];
                $doacao->doador_nome = $cliente->name ?? null;
                $doacao->doador_email = $cliente->email ?? null;
                $doacao->doador_cpf = $cliente->document_number ?? $cliente->cpf ?? null;
                $doacao->doador_telefone = $cliente->phone_number ?? null;
            }
            // ==================================

            $doacao->salvar();

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } else {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Já processado']);
        }
    }
}
