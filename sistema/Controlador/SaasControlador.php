<?php

namespace sistema\Controlador;

use sistema\Controlador\UsuarioControlador;
use sistema\Nucleo\Controlador;
use sistema\Nucleo\Sessao;
use sistema\Nucleo\Helpers;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\EnderecoModelo;
use sistema\Modelo\PostModelo;
use sistema\Modelo\InscricaoModelo;

/**
 * Classe SaasControlador
 *
 * @author Fernando Aguiar
 */
class SaasControlador extends Controlador
{

    private $usuario;

    /**
     * Construtor responsável por definir o diretório das páginas HTML e controla se existe usuario
     */
    public function __construct()
    {
        parent::__construct('templates/saas/views');
        $this->usuario = UsuarioControlador::usuario();
        if (!$this->usuario OR $this->usuario->level < 1) {
            $this->mensagem->alerta('Faça login para acessar o painel do usuário!')->flash();
            $sessao = new Sessao();
            $sessao->limpar('usuarioId');
            Helpers::redirecionar();
        }
    }

    /**
     * Renderiza a página inicial do saas e transporta informações necessárias do usuario logado
     * 
     * @return void
     */
    public function index(): void
    {
        $endereco = (new EnderecoModelo())->busca("usuario_id = {$this->usuario->id}")->resultado(true);
        $objEndereco = (new EnderecoModelo())->busca("usuario_id = {$this->usuario->id}")->resultado();
        $inscricaoExistente = (new InscricaoModelo())->busca("{$this->usuario->id} = id_usuario  ")->ordem("id desc")->limite(1)->resultado(true);
        echo $this->template->renderizar('conta.html', [
            'usuario' => $this->usuario,
            'enderecos' => $endereco,
            'end' => $objEndereco,
            'inscricoes' => $inscricaoExistente
        ]);
    }

    /**
     * Faz logout do usuário
     * @return void
     */
    public function sair(): void
    {
        $sessao = new Sessao();
        $sessao->limpar('usuarioId');
        $this->mensagem->informa($this->usuario->nome . ', você saiu do painel de controle!')->flash();
        Helpers::redirecionar();
    }

    /**
     * Completa o cadastro do usuario, quando logado
     * @param int $id
     * @return void
     */
    public function cadastro(int $id): void
    {
        $cadastro = (new UsuarioModelo())->buscaPorId($id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDadosCadastro($dados)) {
                $cadastro->telefone = Helpers::limparNumero($dados['telefone']);
                // Convertendo a data de nascimento para o formato americano
                $data_brasileira = $dados['nascimento'];
                $data_americana = date('Y-m-d', strtotime(str_replace('/', '-', $data_brasileira)));
                $cadastro->nascimento = $data_americana;
                $cadastro->texto = $dados['texto'];
                //$usuario->cadastrado_em = date('Y-m-d H:i:s');
            }
            if ($this->validarDadosEndereco($dados)) {
                $endereco = new EnderecoModelo();
                $endereco->usuario_id = $id;
                $endereco->cep = Helpers::limparNumero($dados['cep']);
                $endereco->endereco = Helpers::slug($dados['endereco']);
                $endereco->bairro = Helpers::slug($dados['bairro']);
                $endereco->cidade = $dados['cidade'];
                $endereco->estado = $dados['estado'];
            }
            if ($cadastro->salvar() && $endereco->salvar()) {
                $this->mensagem->sucesso('Seu cadastro está completo! Você está pronto para participar dos nossos projetos. Obrigado!')->flash();
                Helpers::json('redirecionar', Helpers::url('saas'));
            } else {
                Helpers::json('erro', 'Alerta: ' . $endereco->mensagem() .
                        $cadastro->mensagem());
            }
        }
    }

    /**
     * Altera a senha do usuario, quando logado
     * @param int $id
     * @return void
     */
    public function alterarSenha(int $id): void
    {
        $usuario = (new UsuarioModelo())->buscaPorId($id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDadosSenha($dados)) {
                $usuario->senha = Helpers::gerarSenha($dados['senha']);
                if ($usuario->salvar()) {
                    $this->mensagem->sucesso('A senha foi atualizada com êxito.')->flash();
                    Helpers::json('redirecionar', Helpers::url('saas'));
                } else {
                    Helpers::json('erro', 'Alerta: ' . $usuario->mensagem());
                }
            }
        }
    }

    /**
     * Altera endereço quando logado
     * @param int $id
     * @return void
     */
    public function editarEndereco(int $id): void
    {
        $endereco = (new EnderecoModelo())->buscaPorId($id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDadosEndereco($dados)) {
                $endereco->cep = Helpers::limparNumero($dados['cep']);
                $endereco->endereco = Helpers::slug($dados['endereco']);
                $endereco->bairro = Helpers::slug($dados['bairro']);
                $endereco->cidade = $dados['cidade'];
                $endereco->estado = $dados['estado'];
                if ($endereco->salvar()) {
                    $this->mensagem->sucesso('O endereço foi atualizado com êxito.')->flash();
                    Helpers::json('redirecionar', Helpers::url('saas'));
                } else {
                    $this->mensagem->erro($endereco->erro())->flash();
                    Helpers::json('erro', 'Alerta: ' . $endereco->mensagem());
                }
            }
        }
    }

    /**
     * Realiza uma inscrição em algum projeto, quando logado
     * @param int $id
     * @return void
     */
    public function inscricoes(int $id): void
    {
        $idPost = (is_int($id)) ? $id : Helpers::redirecionar('saas');
        $post = (new PostModelo())->buscaPorId($id);
        if ($post) {
            if ($this->usuario->id) {
                // Verifica se o usuário já está inscrito neste projeto
                $inscricaoExistente = (new InscricaoModelo())->busca("id_post = {$idPost} AND id_usuario = {$this->usuario->id}")->resultado(true);
                if ($inscricaoExistente) {
                    $this->mensagem->alerta("Você já está inscrito no projeto '{$post->titulo}'. Verifique 'Minhas inscrições' para mais detalhes.")->flash();
                    Helpers::redirecionar('saas');
                } else {
                    // Cria uma nova inscrição
                    $inscricao = new InscricaoModelo();
                    $inscricao->id_post = $idPost;
                    $inscricao->id_usuario = $this->usuario->id;
                    if ($inscricao->salvar()) {
                        $this->mensagem->sucesso("Inscrição concluída com sucesso no projeto '{$post->titulo}'. Número de inscrição: {$inscricao->ultimoId()}. Bem-vindo à nossa comunidade!")->flash();
                        Helpers::redirecionar('saas');
                    } else {
                        $this->mensagem->erro($inscricao->erro())->flash();
                        Helpers::redirecionar('saas');
                    }
                }
            }
        }
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
            1 => 'titulo',
        ];
        $ordem = " " . $colunas[$datatable['order'][0]['column']] . " ";
        $ordem .= " " . $datatable['order'][0]['dir'] . " ";
        $posts = new PostModelo();
        if (empty($busca)) {
            $posts->busca("categoria_id = 1 AND status = 1")->ordem($ordem)->limite($limite)->offset($offset);
            $total = (new PostModelo())->busca(null, 'COUNT(id)', 'id')->total();
        } else {
            $posts->busca("id LIKE '%{$busca}%' OR titulo LIKE '%{$busca}%'")->limite($limite)->offset($offset);
            $total = $posts->total();
        }
        $dados = [];
        if ($posts->resultado(true)) {
            foreach ($posts->resultado(true) as $post) {
                $inscricaoExistente = (new InscricaoModelo())->busca("id_post = {$post->id} AND id_usuario = {$this->usuario->id}")->resultado(true);

                if (!$inscricaoExistente) {
                    $botao = '<a href="' . Helpers::url('inscricoes/' . $post->id) . '" class="btn btn-info btn-sm"><i class="fas fa-user-plus"></i> Inscreva-se</a>';
                } else {
                    $botao = '<button class="btn btn-success btn-sm" disabled><i class="fas fa-check"></i> Inscrito</button>';
                }
                $dados[] = [
                    $post->id,
                    '<a href="' . Helpers::url('post/projetos/' . $post->slug) . '" class="text-primary">' . $post->titulo . '</a>',
                    $botao
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

    /**
     * Validar campos do cadastro
     * @param array $dados
     * @return bool
     */
    public function validarDadosCadastro(array $dados): bool
    {
        if (empty($dados['texto'])) {
            Helpers::json('erro', 'Por favor, conte-nos um pouco sobre você! Essa informação é essencial para fornecermos a melhor assistência possível!');
            return false;
        }
        if (empty($dados['telefone']) || strlen($dados['telefone']) < 5) {
            Helpers::json('erro', 'O telefone precisa ter pelo menos 5 caracteres!');
            return false;
        }
        if (empty($dados['nascimento'])) {
            Helpers::json('erro', 'Por favor, informe a data de nascimento.');
            return false;
        }
        return true;
    }

    /**
     * Validar campos da senha
     * @param array $dados
     * @return bool
     */
    public function validarDadosSenha(array $dados): bool
    {
        if (empty($dados['senha'])) {
            Helpers::json('erro', 'Informe uma senha!');
            return false;
        }
        if (!empty($dados['senha'])) {
            if (!Helpers::validarSenha($dados['senha'])) {
                Helpers::json('erro', 'A senha deve ter entre 6 e 50 caracteres!');
                return false;
            }
        }
        return true;
    }

    /**
     * Validar campos do Endereco
     * @param array $dados
     * @return bool
     */
    public function validarDadosEndereco(array $dados): bool
    {
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
}
