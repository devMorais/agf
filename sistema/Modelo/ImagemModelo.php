<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe PostModelo
 *
 * @author Ronaldo Aires
 */
class ImagemModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('imagens');
    }

    /**
     * Busca o usuário pelo ID
     * @return UsuarioModelo|null
     */
    public function usuario(): ?UsuarioModelo
    {
        if ($this->id_usuario) {
            return (new UsuarioModelo())->buscaPorId($this->id_usuario);
        }
        return null;
    }

    /**
     * Salva o post com slug
     * @return bool
     */
    public function salvar(): bool
    {
        $this->slug();
        return parent::salvar();
    }
}
