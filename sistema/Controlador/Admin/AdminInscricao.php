<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Helpers;
use sistema\Modelo\EnderecoModelo;
use sistema\Modelo\ImagemModelo;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\InscricaoModelo;
use sistema\Modelo\PostModelo;

/**
 * Classe AdminInscricoes
 *
 * @author Fernando Aguiar
 */
class AdminInscricao extends AdminControlador
{

    /**
     * Lista as inscrições
     * @return void
     */
    public function listar(): void
    {
        $inscricao = new InscricaoModelo();

        echo $this->template->renderizar('inscricoes/listar.html', [
            'inscricoes' => $inscricao->busca()->ordem('id DESC')->resultado(true),
        ]);
    }

    /**
     * Lista de informções completas dos usuarios
     * @return void
     */
    public function visualizar(?int $id = null): void
    {
        $infoUsuarios = (new UsuarioModelo)->buscaPorId($id);
        $infoenderecos = (new EnderecoModelo())->busca("usuario_id  = {$id}")->resultado();
        $infoArquivos = (new ImagemModelo())->busca("id_usuario = {$id}")->resultado(true);

        echo $this->template->renderizar('inscricoes/visualizar.html', [
            'usuarios' => $infoUsuarios,
            'enderecos' => $infoenderecos,
            'arquivos' => $infoArquivos
        ]);
    }

    /**
     * Busca ID do usuário 
     * @return void
     */
    public function buscar(): void
    {
        $busca = filter_input(INPUT_POST, 'busca', FILTER_DEFAULT);

        if (isset($busca)) {
            $inscricoes = (new UsuarioModelo())->busca("cpf LIKE '%{$busca}%'")->limite(20)->resultado(true);
            if ($inscricoes) {
                foreach ($inscricoes as $inscricao) {
                    echo $inscricao->id; // Apenas imprime o ID do usuário
                    return;
                }
            } else {
                echo ""; // Retorna vazio se nenhum resultado for encontrado
            }
        } else {
            echo ""; // Retorna vazio se não houver CPF enviado
        }
    }

    /**
     * Cadastra usuário
     * @return void
     */
    public function cadastrar(?int $id = null): void
    {
        $idUsuario = null; // Inicialize a variável $idUsuario aqui

        if ($id) {
            $idUsuario = $id;
        }

        $projetos = (new PostModelo())->busca("categoria_id = 1")->resultado(true);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            if (empty($dados['id_usuario'])) {
                Helpers::json('erro', 'Informe um usuário');
            } elseif (empty($dados['id_post']) || !is_numeric($dados['id_post'])) {
                Helpers::json('erro', 'Escolha um projeto!');
            } else {
                // Verifica se o usuário já está inscrito neste projeto
                $idPost = $dados['id_post'];
                $idUsuario = $dados['id_usuario'];

                $inscricaoExistente = (new InscricaoModelo())->busca("id_post = {$idPost} AND id_usuario = {$idUsuario}")->resultado();

                if ($inscricaoExistente) {
                    Helpers::json('erro', 'Usuário já está inscrito neste projeto!');
                } else {
                    $inscricao = new InscricaoModelo();

                    $inscricao->id_post = $idPost;
                    $inscricao->id_usuario = $idUsuario;

                    if ($inscricao->salvar()) {
                        $this->mensagem->sucesso("Número de inscrição: {$inscricao->id}")->flash();
                        Helpers::json('redirecionar', Helpers::url('admin/inscricoes/listar'));
                    } else {
                        Helpers::json('erro', 'Erro: ' . $inscricao->mensagem());
                    }
                }
            }
        }
        echo $this->template->renderizar('inscricoes/formulario.html', [
            'projetos' => $projetos,
            'id' => $idUsuario
        ]);
    }

    /**
     * Cadastra usuário
     * @return void
     */
    public function editar(int $id): void
    {
        $inscricao = (new EnderecoModelo())->buscaPorId($id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            //checa os dados 

            $inscricao = (new EnderecoModelo())->buscaPorId($id);

            $inscricao->usuario_id = Helpers::formatarNumero($dados['usuario_id']);
            $inscricao->cep = Helpers::limparNumero($dados['cep']);
            $inscricao->endereco = Helpers::slug($dados['endereco']);
            $inscricao->bairro = $dados['bairro'];
            $inscricao->cidade = $dados['cidade'];
            $inscricao->estado = $dados['estado'];

            if ($inscricao->salvar()) {
                $this->mensagem->sucesso('Endereço atualizado com sucesso!')->flash();
                Helpers::json('redirecionar', Helpers::url('admin/enderecos/listar'));
            } else {
                Helpers::json('erro', strip_tags($inscricao->mensagem()));
            }
        }
        echo $this->template->renderizar('enderecos/formulario.html', [
            'endereco' => $inscricao,
        ]);
    }

    /**
     * Método responsável por exibir os dados tabulados utilizando o plugin datatables
     * @return void
     */
    public function datatable(): void
    {
        $datatable = $_REQUEST;
        $datatable = filter_var_array($datatable, FILTER_SANITIZE_SPECIAL_CHARS);

        $limite = $datatable['length'];
        $offset = $datatable['start'];
        $busca = $datatable['search']['value'];

        $colunas = [
            0 => 'id',
            1 => 'id_post',
            2 => 'id_usuario'
        ];

        $ordem = " " . $colunas[$datatable['order'][0]['column']] . " ";
        $ordem .= " " . $datatable['order'][0]['dir'] . " ";

        $inscricoes = new InscricaoModelo();

        if (empty($busca)) {
            $inscricoes->busca()->ordem($ordem)->limite($limite)->offset($offset);
            $total = (new InscricaoModelo())->busca(null, 'COUNT(id)', 'id')->total();
        } else {
            $inscricoes->busca("id LIKE '%{$busca}%' OR id_post LIKE '%{$busca}%' OR id_usuario LIKE '%{$busca}%' ")->limite($limite)->offset($offset);
            $total = $inscricoes->total();
        }

        $dados = [];

        if ($inscricoes->resultado(true)) {
            foreach ($inscricoes->resultado(true) as $inscricao) {
                $infoPost = (new PostModelo())->buscaPorId($inscricao->id_post);
                $infoUsuario = (new UsuarioModelo())->buscaPorId($inscricao->id_usuario);

                $tituloPost = $infoPost ? $infoPost->titulo : 'Post não encontrado';
                $nomeUsuario = $infoUsuario ? $infoUsuario->nome : 'Usuário não encontrado';
                $linkUsuario = $infoUsuario ? '<a href="' . Helpers::url('admin/inscricoes/visualizar/' . $infoUsuario->id) . '" tooltip="tooltip" title="Visualizar informações"><i class="fa-solid fa-eye m-1"></i> ' . $nomeUsuario . '</a>' : $nomeUsuario;

                $dados[] = [
                    $inscricao->id,
                    $tituloPost,
                    $linkUsuario
                ];
            }
        }

        $retorno = [
            "draw" => $datatable['draw'],
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $dados
        ];

        header('Content-Type: application/json');
        echo json_encode($retorno);
        exit();
    }
}
