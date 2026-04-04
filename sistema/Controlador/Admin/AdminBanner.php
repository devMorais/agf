<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\BannerModelo;
use sistema\Nucleo\Helpers;
use Verot\Upload\Upload;

/**
 * Classe AdminBanner
 *
 * @author Fernando Aguiar
 */
class AdminBanner extends AdminControlador
{

    private string $img;

    /**
     * Lista banner
     * @return void
     */
    public function listar(): void
    {
        $banner = (new BannerModelo())->busca()->resultado(true);

        echo $this->template->renderizar('banner/listar.html', [
            'banners' => $banner
        ]);
    }

    /**
     * Cadastro banners
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {
                $banner = new BannerModelo();

                $banner->titulo = Helpers::slug($dados['titulo']);
                $banner->img = $this->img ?? null;

                if ($banner->salvar()) {
                    $this->mensagem->sucesso('Banner cadastrado com sucesso')->flash();
                    Helpers::redirecionar('admin/banner/listar');
                } else {
                    $this->mensagem->erro($banner->erro())->flash();
                    Helpers::redirecionar('admin/banner/listar');
                }
            }
        }
        echo $this->template->renderizar('banner/formulario.html', [
            'banner' => $banner,
        ]);
    }

    /**
     * Atualiza o banner inicial
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $banner = (new BannerModelo())->buscaPorId($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            if ($this->validarDados($dados)) {
                $banner = (new BannerModelo())->buscaPorId($id);

                $banner->titulo = $dados['titulo'];
                //atualizar a img no DB e no servidor, se um novo arquivo de imagem for enviado
                if (!empty($_FILES['img']["name"])) {
                    if ($banner->img && file_exists("uploads/imagens/{$banner->img}")) {
                        unlink("uploads/imagens/{$banner->img}");
                        unlink("uploads/imagens/thumbs/{$banner->img}");
                    }
                    $banner->img = $this->img ?? null;
                }
                if ($banner->salvar()) {
                    $this->mensagem->sucesso('Banner atualizado com sucesso')->flash();
                    Helpers::redirecionar('admin/banner/listar');
                } else {
                    $this->mensagem->erro($banner->erro())->flash();
                    Helpers::redirecionar('admin/banner/listar');
                }
            }
        }
        echo $this->template->renderizar('banner/formulario.html', [
            'banner' => $banner,
        ]);
    }

    /**
     * Valida os dados do formulário
     * @param array $dados
     * @return bool
     */
    public function validarDados(array $dados): bool
    {

        if (empty($dados['titulo'])) {
            $this->mensagem->alerta('Escreva um título para o Post!')->flash();
            return false;
        }
        if (!empty($_FILES['img'])) {
            $upload = new Upload($_FILES['img'], 'pt_BR');
            if ($upload->uploaded) {
                $titulo = $upload->file_new_name_body = Helpers::slug($dados['titulo']);
                $upload->jpeg_quality = 90;
                $upload->image_convert = 'jpg';
                $upload->process('uploads/imagens/');

                if ($upload->processed) {
                    $this->img = $upload->file_dst_name;
                    $upload->file_new_name_body = $titulo;
                    $upload->image_resize = true;
                    $upload->image_x = 540;
                    $upload->image_y = 304;
                    $upload->jpeg_quality = 70;
                    $upload->image_convert = 'jpg';
                    $upload->process('uploads/imagens/thumbs/');
                    $upload->clean();
                } else {
                    $this->mensagem->alerta($upload->error)->flash();
                    return false;
                }
            }
        }
        return true;
    }
}
