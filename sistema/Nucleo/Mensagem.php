<?php

namespace sistema\Nucleo;

/**
 * Classe Mensagem – responsável por exibir as mensagens do sistema.
 * @author Fernando <adm@sempreumbug.com.br>
 * @copyright Copyright (c) 2023, FernandoDev
 */
class Mensagem
{

    private $texto;
    private $css;
    private $icone;

    public function __toString()
    {
        return $this->renderizar();
    }

    /**
     * Método responsável pelas mensagens de sucesso
     * @param string $mensagem
     * @return Mensagem
     */
    public function sucesso(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-success alert-dismissible fade show';
        $this->icone = 'bi bi-check-circle bi-3x me-2'; // Ícone maior
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Método responsável pelas mensagens de erro
     * @param string $mensagem
     * @return Mensagem
     */
    public function erro(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-danger alert-dismissible fade show';
        $this->icone = 'bi bi-x-octagon bi-3x me-2'; // Ícone maior
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Método responsável pelas mensagens de alerta
     * @param string $mensagem
     * @return Mensagem
     */
    public function alerta(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-warning alert-dismissible fade show';
        $this->icone = 'bi bi-exclamation-triangle bi-3x me-2'; // Ícone maior
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Método responsável pelas mensagens de informações
     * @param string $mensagem
     * @return Mensagem
     */
    public function informa(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-primary alert-dismissible fade show';
        $this->icone = 'bi bi-info-circle bi-3x me-2'; // Ícone maior
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Método responsável pela renderização das mensagens
     * @return string
     */
    public function renderizar(): string
    {
        return "
            <div class='{$this->css}' role='alert'>
                <div class='d-flex align-items-center'>
                    <i class='{$this->icone}'></i>
                    <span>{$this->texto}</span>
                </div>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        ";
    }

    /**
     * Método responsável por filtrar as mensagens
     * @param string $mensagem
     * @return string
     */
    private function filtrar(string $mensagem): string
    {
        return filter_var($mensagem, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Método responsável por exibir as mensagens do sistema em flash, através de sessão
     * @return void
     */
    public function flash(): void
    {
        (new Sessao())->criar('flash', $this);
    }
}