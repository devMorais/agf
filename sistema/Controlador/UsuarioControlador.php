<?php

namespace sistema\Controlador;

use sistema\Modelo\EnderecoModelo;
use sistema\Nucleo\Controlador;
use sistema\Nucleo\Helpers;
use sistema\Nucleo\Sessao;
use sistema\Modelo\UsuarioModelo;
use sistema\Suporte\Email;

class UsuarioControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/usuario/views');
    }

    /**
     * Busca usuário pela sessão
     * @return UsuarioModelo|null
     */
    public static function usuario(): ?UsuarioModelo
    {
        $sessao = new Sessao();
        if (!$sessao->checar('usuarioId')) {
            return null;
        }

        return (new UsuarioModelo())->buscaPorId($sessao->usuarioId);
    }

    /**
     * Cadastro de usuários
     * @return void
     */
    public function cadastro(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            if ($this->validarDadosCadastroInicial($dados)) {
                $usuario = new UsuarioModelo();
                $usuario->level = 1;
                $usuario->nome = $dados['nome'];

                // Gera um e-mail falso provisório
                $numeroAleatorio = random_int(10000, 999999);
                $usuario->email = "fixo{$numeroAleatorio}@gmail.com";

                // Gera uma senha provisória
                $usuario->senha = Helpers::gerarSenha('123456');

                $usuario->telefone = $dados['telefone'];
                $usuario->url_video = null;
                $usuario->cpf = null;
                $usuario->texto = $dados['texto'];
                $usuario->status = 1;
                $usuario->token = null;

                // Tenta salvar o usuário primeiro
                if ($usuario->salvar()) {

                    // Se o usuário salvou, pegamos o ID dele e salvamos o endereço
                    $endereco = new EnderecoModelo();
                    $endereco->usuario_id = $usuario->id;
                    $endereco->logradouro = $dados['endereco'];
                    $endereco->salvar();

                    $mensagemSucesso = "Cadastro recebido com sucesso!";
                    Helpers::json('successo', $mensagemSucesso);
                } else {
                    Helpers::json('erro', 'Erro: ' . $usuario->mensagem());
                }
            }
        }

        echo $this->template->renderizar('cadastro.html', [
            'titulo' => 'Cadastre-se'
        ]);
    }

    /**
     * Confirmar e-mail e ativar conta
     * @param string $token
     * @return void
     */
    public function confirmarEmail(string $token): void
    {
        $usuario = (new UsuarioModelo())->buscaPorToken($token);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (!$usuario) {
            $this->mensagem->erro('Erro: Token inválido!')->flash();
            Helpers::redirecionar();
        } else {
            if (isset($dados)) {
                if ($this->validarDadosCadastro($dados)) {
                    $usuario->senha = Helpers::gerarSenha($dados['senha']);
                    $usuario->status = 1;
                    $usuario->token = null;
                    $usuario->cadastrado_em = date('Y-m-d H:i:s');

                    if ($usuario->salvar()) {
                        $this->mensagem->sucesso('Seu cadastro foi confirmado com sucesso! Faça login agora para completar o registro de suas informações. Bem-vindo de volta!')->flash();
                        Helpers::json('redirecionar', Helpers::url());
                    } else {
                        Helpers::json('erro', 'Erro: ' . $usuario->mensagem());
                    }
                }
            }
        }

        echo $this->template->renderizar('ativar.html', [
            'usuario' => $usuario
        ]);
    }

    /**
     * Validar campos do cadastro
     * @param array $dados
     * @return bool
     */
    public function validarDadosCadastro(array $dados): bool
    {
        if (empty($dados['senha'])) {
            Helpers::json('erro', 'Informe uma senha!');
            return false;
        }
        if ($dados['senha'] != $dados['senha2']) {
            Helpers::json('erro', 'As senhas estão diferentes!');
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
     * Validar campos do cadastro inicial
     * @param array $dados
     * @return bool
     */
    public function validarDadosCadastroInicial(array $dados): bool
    {
        if (empty($dados['nome'])) {
            Helpers::json('erro', 'Informe seu nome!');
            return false;
        }

        if (empty($dados['telefone'])) {
            Helpers::json('erro', 'Informe seu telefone!');
            return false;
        }

        if (empty($dados['endereco'])) {
            Helpers::json('erro', 'Informe seu endereço');
            return false;
        }

        if (empty($dados['texto'])) {
            Helpers::json('erro', 'Informe sua história');
            return false;
        }

        return true;
    }

    /**
     * Login
     * @return void
     */
    public function login(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            if (in_array('', $dados)) {
                Helpers::json('erro', 'Informe seu login e senha!');
            } else {
                $usuario = new UsuarioModelo();

                if ($usuario->login($dados, 1)) {
                    $this->mensagem->sucesso("Bem-vindo(a) ao seu painel de controle, {$usuario->nome}! Estamos aqui para ajudá-lo(a) a gerenciar suas informações com facilidade e eficiência.")->flash();
                    Helpers::json('redirecionar', Helpers::url('saas'));
                } else {
                    Helpers::json('erro', strip_tags($usuario->mensagem()));
                }
            }
        }
    }
}
