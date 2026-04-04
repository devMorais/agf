<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;
use sistema\Nucleo\Conexao;

class LogPagamentoInfinitepayModelo extends Modelo
{
    public function __construct()
    {
        parent::__construct('logs_pagamento_infinitepay');
    }

    public function registrar(
        ?int $doacaoId,
        string $etapa,
        string $status,
        ?string $mensagem = null,
        ?array $requestData = null,
        $responseData = null,
        ?string $codigoErro = null,
        ?string $infinitepaySlug = null,
        ?string $infinitepayLink = null,
        ?string $transactionNsu = null,
        ?string $orderNsu = null,
        ?string $endpoint = null,
        ?int $httpCode = null
    ): bool {
        try {
            $requestSanitizado = $this->sanitizarDados($requestData);

            $responseJson = null;
            $responseObj = null;

            if ($responseData !== null) {
                if (is_string($responseData)) {
                    $responseJson = $responseData;
                    $responseObj = json_decode($responseData);
                } elseif (is_object($responseData) || is_array($responseData)) {
                    $responseJson = json_encode($responseData);
                    $responseObj = is_array($responseData) ? (object)$responseData : $responseData;
                }
            }

            if ($responseObj) {
                if (!$codigoErro) $codigoErro = $responseObj->error ?? $responseObj->code ?? null;
                if (!$mensagem) $mensagem = $responseObj->message ?? null;
                if (!$infinitepaySlug) $infinitepaySlug = $responseObj->slug ?? $responseObj->invoice_slug ?? null;
                if (!$infinitepayLink) $infinitepayLink = $responseObj->url ?? null;
            }

            // Inserção DIRETA via PDO para evitar o crash da classe Modelo base
            $db = Conexao::getInstancia();

            // AQUI ESTÁ A CORREÇÃO: Usando "doacao_id" perfeitamente alinhado com o seu banco!
            $sql = "INSERT INTO logs_pagamento_infinitepay 
                    (doacao_id, etapa, status, mensagem, codigo_erro, request_data, response_data, 
                     infinitepay_slug, infinitepay_link, transaction_nsu, order_nsu, endpoint, http_code, ip_usuario, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                $doacaoId,
                $etapa,
                $status,
                $mensagem,
                $codigoErro,
                $requestSanitizado ? json_encode($requestSanitizado) : null,
                $responseJson,
                $infinitepaySlug,
                $infinitepayLink,
                $transactionNsu,
                $orderNsu,
                $endpoint,
                $httpCode,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            return true;
        } catch (\Throwable $e) {
            // Se falhar o log, imprimimos o erro internamente para você saber, mas não travamos o cliente
            error_log("Falha ao salvar log InfinitePay: " . $e->getMessage());
            return false;
        }
    }

    private function sanitizarDados(?array $dados): ?array
    {
        if (!$dados) return null;
        $sanitizado = $dados;

        if (isset($sanitizado['customer']['creditCard']['number'])) {
            $numero = $sanitizado['customer']['creditCard']['number'];
            $sanitizado['customer']['creditCard']['number'] = substr($numero, 0, 4) . '****' . substr($numero, -4);
        }
        if (isset($sanitizado['customer']['creditCard']['cvv'])) $sanitizado['customer']['creditCard']['cvv'] = '***';
        if (isset($sanitizado['customer']['creditCard']['ccv'])) $sanitizado['customer']['creditCard']['ccv'] = '***';

        if (isset($sanitizado['customer']['email'])) {
            $email = $sanitizado['customer']['email'];
            $partes = explode('@', $email);
            if (count($partes) === 2) {
                $sanitizado['customer']['email'] = substr($partes[0], 0, 3) . '***@' . $partes[1];
            }
        }

        if (isset($sanitizado['customer']['phone_number'])) {
            $telefone = $sanitizado['customer']['phone_number'];
            $sanitizado['customer']['phone_number'] = substr($telefone, 0, 5) . '****' . substr($telefone, -2);
        }

        return $sanitizado;
    }
}
