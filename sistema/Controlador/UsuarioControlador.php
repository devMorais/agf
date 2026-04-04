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
                $numeroAleatorio = random_int(10000, 999999);
                $usuario->email = "fixo{$numeroAleatorio}@gmail.com";
                $usuario->senha = Helpers::gerarSenha('123456');
                $usuario->telefone = $dados['telefone'];
                $usuario->endereco = $dados['endereco'];
                $usuario->url_video = null;
                $usuario->cpf = null;
                $usuario->nascimento = $dados['nascimento'];
                $usuario->texto = $dados['texto'];
                $usuario->status = 1;
                $usuario->token = null;

                if ($usuario->salvar()) {
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
     * Validar campos do cadastro
     * @param array $dados
     * @return bool
     */
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

        // Validação da data de nascimento
        if (empty($dados['nascimento'])) {
            Helpers::json('erro', 'Informe sua data de nascimento');
            return false;
        } else {
            // Tenta criar um objeto de data a partir do formato brasileiro (dd/mm/aaaa)
            $data = \DateTime::createFromFormat('d/m/Y', $dados['nascimento']);

            // Se a data for inválida (formato incorreto) OU se não for uma data real (ex: 30/02/2025)
            if (!$data || $data->format('d/m/Y') !== $dados['nascimento']) {
                // Tenta criar a partir do formato do input type="date" (aaaa-mm-dd)
                $data = \DateTime::createFromFormat('Y-m-d', $dados['nascimento']);
                if (!$data || $data->format('Y-m-d') !== $dados['nascimento']) {
                    Helpers::json('erro', 'A data de nascimento informada é inválida. Use o formato dd/mm/aaaa.');
                    return false;
                }
            }
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
