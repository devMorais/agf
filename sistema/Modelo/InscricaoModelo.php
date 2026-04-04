<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe InscricaoModelo
 *
 * @author Fernando Aguiar
 */
class InscricaoModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('inscricoes');
    }

    /**
     * Busca o post ID
     * @return CategoriaModelo|null
     */
    public function postInscricao(): ?PostModelo
    {
        if ($this->id_post) {
            return (new PostModelo())->buscaPorId($this->id_post);
        }
        return null;
    }
}
