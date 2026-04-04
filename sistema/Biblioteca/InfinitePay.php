<?php

namespace sistema\Biblioteca;

use sistema\Nucleo\Helpers;
use sistema\Modelo\LogPagamentoInfinitepayModelo;
use sistema\Suporte\XDebug;

class InfinitePay
{
    private string $handle;
    private string $url;
    private LogPagamentoInfinitepayModelo $log;
    private ?int $doacaoId;
    private ?string $webhookUrl;
    private ?string $redirectUrl;

    public function __construct(?int $doacaoId = null)
    {
        $this->handle      = INFINITEPAY_HANDLE;
        $this->url         = INFINITEPAY_URL;
        $this->webhookUrl  = INFINITEPAY_WEBHOOK_URL;
        $this->redirectUrl = INFINITEPAY_REDIRECT_URL;
        $this->log         = new LogPagamentoInfinitepayModelo();
        $this->doacaoId    = $doacaoId;
    }

    public function verificarPagamento(string $orderNsu, ?string $transactionNsu = null, ?string $slug = null): array
    {
        $payload = [
            'handle' => $this->handle,
            'order_nsu' => $orderNsu
        ];

        if ($transactionNsu) $payload['transaction_nsu'] = $transactionNsu;
        if ($slug) $payload['slug'] = $slug;

        $resposta = $this->request('/invoices/public/checkout/payment_check', $payload, 'verificar_pagamento');

        if (!isset($resposta->success)) {
            return ['erro' => true, 'paid' => false, 'mensagem' => $resposta->message ?? 'Erro na checagem'];
        }

        return [
            'erro' => false,
            'paid' => $resposta->paid ?? false,
            'capture_method' => $resposta->capture_method ?? 'card',
            'dados' => $resposta
        ];
    }

    public function gerarLinkPagamento(array $itens, ?string $orderNsu = null, ?array $dadosCliente = null, ?string $redirectUrlCustom = null): array
    {
        $redirectUrl = $redirectUrlCustom ?? $this->redirectUrl;

        $payload = [
            'handle' => $this->handle,
            'items' => $this->formatarItens($itens),
            'order_nsu' => $orderNsu,
            'webhook_url' => $this->webhookUrl,
            'redirect_url' => $redirectUrl,
            'customer' => $dadosCliente ? $this->formatarDadosCliente($dadosCliente) : null
        ];


        $resposta = $this->request('/invoices/public/checkout/links', array_filter($payload, fn($v) => $v !== null), 'criar_link');


        if (!isset($resposta->url)) {
            return ['erro' => true, 'mensagem' => $resposta->message ?? 'Erro ao gerar link'];
        }

        return [
            'erro' => false,
            'link' => $resposta->url,
            'order_nsu' => $orderNsu,
            'slug' => $resposta->slug ?? null
        ];
    }

    public function request(string $endpoint, array $dados, string $etapa): object
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url . $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($dados),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => INFINITEPAY_SSL ?? true
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        $respostaObj = (json_last_error() === JSON_ERROR_NONE) ? json_decode($res) : null;
        $statusLog = ($httpCode >= 200 && $httpCode < 300 && !$curlError) ? 'SUCESSO' : 'ERRO';

        // Salva o log perfeitamente formatado para o seu banco
        $this->log->registrar(
            $this->doacaoId,
            $etapa,
            $statusLog,
            $curlError ?: null,
            $dados,
            $res,
            null,
            null,
            null,
            $dados['transaction_nsu'] ?? null,
            $dados['order_nsu'] ?? null,
            $this->url . $endpoint,
            $httpCode
        );

        if ($curlError) return (object)['error' => 'curl_error', 'message' => $curlError];
        if ($httpCode < 200 || $httpCode >= 300) return (object)['error' => 'http_error', 'http_code' => $httpCode, 'response' => $respostaObj];

        return $respostaObj ?? (object)['error' => 'json_error', 'message' => 'Resposta inválida'];
    }

    private function formatarItens(array $itens): array
    {
        return array_map(fn($i) => [
            'quantity' => $i['quantidade'] ?? 1,
            'price' => (int)round(($i['valor'] ?? 0) * 100),
            'description' => $i['descricao'] ?? 'Doação - Associação Grande Família'
        ], $itens);
    }

    private function formatarDadosCliente(array $d): array
    {
        return [
            'name' => $d['nome'] ?? '',
            'cpf' => isset($d['cpf']) ? Helpers::limparNumero($d['cpf']) : null,
            'email' => $d['email'] ?? null,
            'phone_number' => Helpers::limparNumero($d['telefone'] ?? '')
        ];
    }
}
