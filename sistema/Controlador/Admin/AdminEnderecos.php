<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Helpers;
use sistema\Modelo\EnderecoModelo;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\InscricaoModelo;

/**
 * Classe AdminEnderecos
 *
 * @author Fernando Aguiar
 */
class AdminEnderecos extends AdminControlador
{

    /**
     * Lista enderecos
     * @return void
     */
    public function listar(): void
    {
        $endereco = new EnderecoModelo();

        echo $this->template->renderizar('enderecos/listar.html', [
            'enderecos' => $endereco->busca()->ordem('id DESC')->resultado(true),
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
            $enderecos = (new UsuarioModelo())->busca("cpf LIKE '%{$busca}%'")->limite(20)->resultado(true);
            if ($enderecos) {
                foreach ($enderecos as $endereco) {
                    echo $endereco->id;
                    return;
                }
            } else {
                echo "";
            }
        } else {
            echo "";
        }
    }

    /**
     * Cadastra endereço
     * @return void
     */
    public function cadastrar(int $id = null): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $idUsuario = $id;

        if (isset($dados)) {
            // Verifica se os dados são válidos
            if ($this->validarDadosEndereco($dados)) {
                $endereco = new EnderecoModelo();
                // Define o ID do usuário
                $endereco->usuario_id = is_int($id) ? $id : Helpers::formatarNumero($dados['usuario_id']);
                // Preenche os dados do endereço
                $endereco->cep = Helpers::limparNumero($dados['cep']);
                $endereco->endereco = Helpers::slug($dados['endereco']);
                $endereco->bairro = $dados['bairro'];
                $endereco->cidade = $dados['cidade'];
                $endereco->estado = $dados['estado'];

                // Tenta salvar o endereço
                if ($endereco->salvar()) {
                    // Exibe mensagem de sucesso e redireciona
                    $mensagem_sucesso = 'Endereço cadastrado com sucesso!';
                    $this->mensagem->sucesso($mensagem_sucesso)->flash();
                    Helpers::json('redirecionar', Helpers::url('admin/enderecos/listar'));
                } else {
                    // Exibe mensagem de erro em caso de falha
                    $mensagem_erro = 'Erro ao cadastrar endereço: ' . strip_tags($endereco->mensagem());
                    Helpers::json('erro', $mensagem_erro);
                }
            } else {
                // Dados inválidos, exibe mensagem de erro
                $mensagem_erro = 'Dados de endereço inválidos!';
                Helpers::json('erro', $mensagem_erro);
            }
        }
        echo $this->template->renderizar('enderecos/formulario.html', [
            'endereco' => $dados,
            'id' => $idUsuario
        ]);
    }

    /**
     * Validar campos do Endereco
     * @param array $dados
     * @return bool
     */
    public function validarDadosEndereco(array $dados): bool
    {
        if (empty($dados['usuario_id'])) {
            Helpers::json('erro', 'Informe  o código do usuário que você deseja cadastrar um endereço!');
            return false;
        }
        if (empty($dados['cep'])) {
            Helpers::json('erro', 'Informe um CEP!');
            return false;
        } elseif (strlen($dados['cep']) < 8) {
            Helpers::json('erro', 'O CEP informado deve ter pelo menos 8 dígitos!');
            return false;
        }
        if (empty($dados['endereco'])) {
            Helpers::json('erro', 'Informe um endereco!');
            return false;
        }
        if (empty($dados['bairro'])) {
            Helpers::json('erro', 'Informe um bairro!');
            return false;
        }

        if (empty($dados['cidade'])) {
            Helpers::json('erro', 'Informe uma Cidade!');
            return false;
        }
        if (empty($dados['estado'])) {
            Helpers::json('erro', 'Informe um Estado!');
            return false;
        }
        return true;
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
            0 => 'usuario_id',
            1 => 'cep',
            2 => 'endereco',
            3 => 'bairro',
            4 => 'cidade',
            5 => 'estados'
        ];

        $ordem = " " . $colunas[$datatable['order'][0]['column']] . " ";
        $ordem .= " " . $datatable['order'][0]['dir'] . " ";

        $enderecos = new EnderecoModelo;

        if (empty($busca)) {
            $enderecos->busca()->ordem($ordem)->limite($limite)->offset($offset);
            $total = (new EnderecoModelo)->busca(null, 'COUNT(id)', 'id')->total();
        } else {
            $enderecos->busca("usuario_id LIKE '%{$busca}%' OR cep LIKE '%{$busca}%' ")->limite($limite)->offset($offset);
            $total = $enderecos->total();
        }

        $dados = [];
        if ($enderecos->resultado(true)) {
            foreach ($enderecos->resultado(true) as $endereco) {
                $inscricoes = (new InscricaoModelo())->busca("id_usuario = {$endereco->usuario_id}")->resultado();
                $levelUsuario = (new UsuarioModelo())->busca("id = {$endereco->usuario_id} ")->resultado();

                if (!$inscricoes && $levelUsuario->level != 3) {
                    $iconePessoa = '<a href="' . helpers::url('admin/inscricoes/cadastrar/' . $endereco->usuario_id) . '"><i class="fa fa-user"></i>' . $levelUsuario->nome . '</a>';
                } else {
                    $iconePessoa = '<i class="fa fa-user text-success"></i>' . $levelUsuario->nome . '';
                }
                $dados[] = [
                    $iconePessoa,
                    $endereco->cep,
                    $endereco->endereco,
                    $endereco->bairro,
                    $endereco->cidade,
                    $endereco->estado
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
