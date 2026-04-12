<?php

namespace sistema\Modelo;

use sistema\Nucleo\Conexao;
use sistema\Nucleo\Modelo;

/**
 * Classe EnderecoModelo
 *
 * @author Fernando
 */
class EnderecoModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('enderecos');
    }

    /**
     * Busca o usuario pelo ID
     * @return UsuarioModelo|null
     */
    public function usuario(): ?UsuarioModelo
    {
        if ($this->usuario_id) {
            return (new UsuarioModelo())->buscaPorId($this->usuario_id);
        }
        return null;
    }
}
