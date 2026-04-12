<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Helpers;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\EnderecoModelo;
use sistema\Modelo\ImagemModelo;
use sistema\Suporte\XDebug;
use Verot\Upload\Upload;

/**
 * Classe AdminUsuarios
 *
 * @author Fernando Aguiar
 */
class AdminUsuarios extends AdminControlador
{
    /**
     * @var array
     * Armazena os nomes dos arquivos de imagem processados.
     */
    private array $imagens = [];

    /**
     * Lista usuários
     * @return void
     */
    public function listar(): void
    {
        $usuario = new UsuarioModelo();

        echo $this->template->renderizar('usuarios/listar.html', [
            'usuarios' => $usuario->busca()->ordem('level DESC, status ASC')->resultado(true),
            'total' => [
                'usuarios' => $usuario->busca('level != 3')->total(),
                'usuariosAtivo' => $usuario->busca('status = 1 AND level != 3')->total(),
                'usuariosInativo' => $usuario->busca('status = 0 AND level != 3')->total(),
                'admin' => $usuario->busca('level = 3')->total(),
                'adminAtivo' => $usuario->busca('status = 1 AND level = 3')->total(),
                'adminInativo' => $usuario->busca('status = 0 AND level = 3')->total()
            ]
        ]);
    }

    /**
     * Cadastra usuário
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            // Primeiro, valida os dados do formulário e tenta fazer o upload das imagens
            if ($this->validarDados($dados)) {
                if (empty($dados['senha'])) {
                    Helpers::json('erro', 'A senha não pode ser vazia!');
                } else {
                    $usuario = new UsuarioModelo();
                    $usuario->level = 1;
                    $usuario->nome = $dados['nome'];
                    $usuario->email = $dados['email'];
                    $usuario->url_video = $dados['url_video'];
                    $usuario->senha = Helpers::gerarSenha($dados['senha']);
                    $usuario->telefone = Helpers::limparNumero($dados['telefone']);
                    $usuario->cpf = Helpers::limparNumero($dados['cpf']);

                    $data_brasileira = $dados['nascimento'];
                    $data_americana = date('Y-m-d', strtotime(str_replace('/', '-', $data_brasileira)));

                    $usuario->nascimento = $data_americana;
                    $usuario->texto = $dados['texto'];
                    $usuario->status = 1;
                    $usuario->cadastrado_em = date('Y-m-d H:i:s');

                    if ($usuario->salvar()) {
                        // Se o usuário foi salvo com sucesso, processa as imagens
                        if (!empty($this->imagens)) {
                            foreach ($this->imagens as $imagem_nome) {
                                $imagemModelo = new ImagemModelo();
                                $imagemModelo->id_usuario = $usuario->id;
                                $imagemModelo->imagem = $imagem_nome;
                                $imagemModelo->slug = null;
                                $imagemModelo->salvar();
                            }
                        }

                        $this->mensagem->sucesso('Usuário e imagens cadastrados com sucesso!')->flash();
                        Helpers::json('redirecionar', Helpers::url('admin/usuarios/listar'));
                    } else {
                        Helpers::json('erro', strip_tags($usuario->mensagem()));
                    }
                }
            }
        }
        echo $this->template->renderizar('usuarios/formulario.html', [
            'usuario' => $dados ?? [],
        ]);
    }

    /**
     * Edita os dados do usuário por ID
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $usuario = (new UsuarioModelo())->buscaPorId($id);

        if (!$usuario) {
            $this->mensagem->alerta('O usuário que você está tentando editar não existe!')->flash();
            Helpers::redirecionar('admin/usuarios/listar');
        }

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            // Primeiro, valida os dados do formulário e tenta fazer o upload das imagens
            if ($this->validarDados($dados)) {
                // AQUI É ONDE O UPLOAD JÁ OCORREU E OS ARQUIVOS ESTÃO NO DIRETÓRIO TEMPORÁRIO
                // ANTES DE SALVAR O USUÁRIO NO BANCO

                $usuario->level = 1;
                $usuario->nome = $dados['nome'];
                $usuario->email = $dados['email'];

                if (empty($dados['senha'])) {
                    // Se a senha estiver vazia, não a atualiza.
                    $usuario->senha = $usuario->senha;
                } else {
                    $usuario->senha = Helpers::gerarSenha($dados['senha']);
                }

                $usuario->telefone = Helpers::limparNumero($dados['telefone']);
                $usuario->cpf = $dados['cpf'];
                $data_brasileira = $dados['nascimento'];
                $data_americana = date('Y-m-d', strtotime(str_replace('/', '-', $data_brasileira)));
                $usuario->nascimento = $data_americana;
                $usuario->texto = $dados['texto'];
                $usuario->status = $dados['status'];
                $usuario->url_video = $dados['url_video'];
                $usuario->atualizado_em = date('Y-m-d H:i:s');

                if ($usuario->salvar()) {
                    // Se o usuário foi salvo com sucesso, processa as imagens
                    if (!empty($this->imagens)) {
                        foreach ($this->imagens as $imagem_nome) {
                            $imagemModelo = new ImagemModelo();
                            $imagemModelo->id_usuario = $usuario->id;
                            $imagemModelo->imagem = $imagem_nome;
                            $imagemModelo->slug = null;
                            if (!$imagemModelo->salvar()) {
                                Helpers::json('erro', strip_tags($imagemModelo->mensagem()));
                                return;
                            }
                        }
                    }

                    $this->mensagem->sucesso('Usuário e imagens atualizados com sucesso!')->flash();
                    Helpers::json('redirecionar', Helpers::url('admin/usuarios/listar'));
                } else {
                    Helpers::json('erro', strip_tags($usuario->mensagem()));
                }
            }
        }

        echo $this->template->renderizar('usuarios/formulario.html', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * Checa os dados do formulário e processa os uploads
     * @param array $dados
     * @return bool
     */
    public function validarDados(array $dados): bool
    {
        if (empty($dados['texto'])) {
            Helpers::json('erro', 'Por favor, conte-nos um pouco sobre você! Essa informação é essencial para fornecermos a melhor assistência possível!');
            return false;
        }

        if (empty($dados['nome']) || strlen($dados['nome']) < 1) {
            Helpers::json('erro', 'Nome curto!');
            return false;
        }
        // A validação de senha só é necessária se o campo não estiver vazio
        if (!empty($dados['senha']) && !Helpers::validarSenha($dados['senha'])) {
            Helpers::json('erro', 'A senha precisa ter entre 6 ou 60 caracteres!');
            return false;
        }
        if (empty($dados['telefone']) || strlen($dados['telefone']) < 5) {
            Helpers::json('erro', 'O telefone precisa ter pelo menos 5 caracteres!');
            return false;
        }
        if (empty($dados['cpf'])) {
            Helpers::json('erro', 'Por favor, insira seu CPF.');
            return false;
        }
        $cpfLimpo = Helpers::limparNumero($dados['cpf']);

        if (!Helpers::validarCPF($cpfLimpo)) {
            Helpers::json('erro', 'O seu CPF precisa ser válido!');
            return false;
        }
        if (empty($dados['nascimento'])) {
            Helpers::json('erro', 'Por favor, informe a data de nascimento.');
            return false;
        }
        // Validação da URL do vídeo do YouTube.
        if (!empty($dados['url_video'])) {
            if (strpos($dados['url_video'], 'youtube.com') === false && strpos($dados['url_video'], 'youtu.be') === false) {
                Helpers::json('erro', 'Por favor, insira um URL do YouTube válido.');
                return false;
            }
        }

        // Lógica para lidar com múltiplos uploads usando Verot/Upload
        if (!empty($_FILES['anexos'])) {
            $anexos = $_FILES['anexos'];
            foreach ($anexos['name'] as $key => $name) {
                if ($anexos['error'][$key] === UPLOAD_ERR_OK) {
                    $file_info = [
                        'name' => $name,
                        'type' => $anexos['type'][$key],
                        'tmp_name' => $anexos['tmp_name'][$key],
                        'error' => $anexos['error'][$key],
                        'size' => $anexos['size'][$key]
                    ];

                    $upload = new Upload($file_info, 'pt_BR');
                    if ($upload->uploaded) {
                        $upload->file_new_name_body = Helpers::slug($name);
                        $upload->jpeg_quality = 90;
                        $upload->image_convert = 'jpg';
                        $upload->process('uploads/imagens/usuarios/');

                        if ($upload->processed) {
                            $this->imagens[] = 'uploads/imagens/usuarios/' . $upload->file_dst_name;
                            $upload->clean();
                        } else {
                            $this->mensagem->alerta($upload->error)->flash();
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Deletar um usuário por ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $usuario = (new UsuarioModelo())->buscaPorId($id);
            if (!$usuario) {
                $this->mensagem->alerta('O usuário que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            } else {
                if ($usuario->deletar()) {
                    $this->mensagem->sucesso('Usuário deletado com sucesso!')->flash();
                    Helpers::redirecionar('admin/usuarios/listar');
                } else {
                    $this->mensagem->erro($usuario->erro())->flash();
                    Helpers::redirecionar('admin/usuarios/listar');
                }
            }
        }
    }

    public function visualizar(int $id): void
    {
        // 1. Busca o usuário
        $usuario = (new UsuarioModelo())->buscaPorId($id);

        if (!$usuario) {
            $this->mensagem->alerta('Usuário não encontrado!')->flash();
            Helpers::redirecionar('admin/usuarios/listar');
        }

        // 2. Busca o endereço (Modo simplificado, direto no ID para o banco não se confundir)
        $endereco = (new EnderecoModelo())->busca("usuario_id = {$id}")->resultado();

        // 3. Busca as fotos (Modo simplificado)
        $arquivos = (new ImagemModelo())->busca("id_usuario = {$id}")->resultado(true);

        // 4. Renderiza o template enviando os dados
        echo $this->template->renderizar('usuarios/visualizar.html', [
            'usuarios'  => $usuario,
            'enderecos' => $endereco,
            'arquivos'  => $arquivos
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
            1 => 'nome',
            2 => 'telefone',
            3 => 'texto',
        ];

        $colunaOrdenacao = $datatable['order'][0]['column'];
        $ordem = " " . ($colunas[$colunaOrdenacao] ?? 'id') . " ";
        $ordem .= " " . $datatable['order'][0]['dir'] . " ";

        $usuarios = new UsuarioModelo();

        if (empty($busca)) {
            $usuarios->busca()->ordem($ordem)->limite($limite)->offset($offset);
            $total = (new UsuarioModelo())->busca(null, 'COUNT(id)', 'id')->total();
        } else {
            $usuarios->busca("id LIKE '%{$busca}%' OR nome LIKE '%{$busca}%' OR telefone LIKE '%{$busca}%' OR texto LIKE '%{$busca}%' ")->limite($limite)->offset($offset);
            $total = $usuarios->total();
        }

        $dados = [];

        if ($usuarios->resultado(true)) {
            foreach ($usuarios->resultado(true) as $usuario) {

                // --- AÇÃO ---
                if ($usuario->level == 3) {
                    $acao = '<i class="fa-solid fa-lock m-1 text-danger"></i>';
                } else {
                    $acao = '<a href="' . Helpers::url('admin/usuarios/editar/' . $usuario->id) . '" tooltip="tooltip" title="Editar"><i class="fa-solid fa-pen m-1 text-primary"></i></a>';

                    // AQUI ESTAVA 'inscricoes/visualizar/', MUDAMOS PARA 'usuarios/visualizar/'
                    $acao .= '<a href="' . Helpers::url('admin/usuarios/visualizar/' . $usuario->id) . '" tooltip="tooltip" title="Visualizar detalhes"><i class="fa-solid fa-eye m-1 text-dark"></i></a>';
                }

                // --- CORREÇÃO DO ERRO DE 100 REGISTROS ---
                // 1. Remove tags HTML que podem quebrar o JSON
                $textoLimpo = strip_tags($usuario->texto);
                // 2. FORÇA a codificação UTF-8 (Isso conserta o erro de travamento)
                $textoLimpo = mb_convert_encoding($textoLimpo, 'UTF-8', 'UTF-8');
                // 3. Corta para não ficar gigante no JSON
                $textoResumido = mb_strimwidth($textoLimpo, 0, 100, "...");

                $dados[] = [
                    $usuario->id,
                    $usuario->nome,
                    $usuario->telefone,
                    $textoResumido,
                    $acao
                ];
            }
        }

        $retorno = [
            "draw" => $datatable['draw'],
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $dados
        ];

        // Adicionei flag para ignorar caracteres inválidos silenciosamente em vez de quebrar
        echo json_encode($retorno, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }
}
