<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe DoacaoModelo
 *
 * Responsável por gerenciar os dados da tabela de doações
 * e seus relacionamentos.
 *
 * @author Fernando Aguiar
 */
class DoacaoModelo extends Modelo
{
    public function __construct()
    {
        // Define a tabela que este modelo gerencia
        parent::__construct('doacoes');
    }

    /**
     * Busca o usuário que realizou a doação pelo ID
     * * @return UsuarioModelo|null
     */
    public function usuario(): ?UsuarioModelo
    {
        if ($this->usuario_id) {
            return (new UsuarioModelo())->buscaPorId($this->usuario_id);
        }
        return null;
    }

    /**
     * Confirma o pagamento da doação alterando seu status
     * * @return bool
     */
    public function confirmarPagamento(): bool
    {
        $this->status = 'confirmada';
        return $this->salvar();
    }

    /**
     * Cancela a doação alterando seu status
     * * @return bool
     */
    public function cancelar(): bool
    {
        $this->status = 'cancelada';
        return $this->salvar();
    }
}
