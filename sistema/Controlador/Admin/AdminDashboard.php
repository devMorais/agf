<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Sessao;
use sistema\Nucleo\Helpers;
use sistema\Modelo\PostModelo;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\CategoriaModelo;
use sistema\Modelo\DoacaoModelo; // Importante adicionar
use sistema\Suporte\XDebug;

class AdminDashboard extends AdminControlador
{
    public function dashboard(): void
    {
        $posts = new PostModelo();
        $usuarios = new UsuarioModelo();
        $categorias = new CategoriaModelo();
        $doacoes = new DoacaoModelo();

        // Para somar valores, buscamos o resultado da coluna SUM
        $soma = $doacoes->busca("status = :s", "s=confirmada", "SUM(valor) as total")->resultado();
        $totalArrecadado = $soma ? $soma->total : 0;

        echo $this->template->renderizar('dashboard.html', [
            'doacoes' => [
                'ultimas' => $doacoes->busca("status = :s", "s=confirmada")->ordem('id DESC')->limite(5)->resultado(true),
                'total_valor' => $totalArrecadado,
                'total_qtd' => $doacoes->busca("status = :s", "s=confirmada COUNT(id)", "id")->total(),
                'pendentes' => $doacoes->busca("status = :s", "s=aguardando COUNT(id)", "id")->total(),
            ],
            'posts' => [
                'posts' => $posts->busca()->ordem('id DESC')->limite(5)->resultado(true),
                'total' => $posts->busca(null, 'COUNT(id)', 'id')->total(),
                'ativo' => $posts->busca('status = :s', 's=1 COUNT(status)', 'status')->total(),
                'inativo' => $posts->busca('status = :s', 's=0 COUNT(status)', 'status')->total()
            ],
            'categorias' => [
                'total' => $categorias->busca()->total(),
            ],
            'usuarios' => [
                'logins' => $usuarios->busca()->ordem('ultimo_login DESC')->limite(5)->resultado(true),
                'total' => $usuarios->busca('level != 3')->total(),
                'admin' => $usuarios->busca('level = 3')->total(),
            ],
        ]);
    }
    public function sair(): void
    {
        $sessao = new Sessao();
        $sessao->limpar('usuarioId');
        $this->mensagem->informa('Você saiu do painel de controle!')->flash();
        Helpers::redirecionar('/');
    }
}
