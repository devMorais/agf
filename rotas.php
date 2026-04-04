<?php

use Pecee\SimpleRouter\SimpleRouter;
use sistema\Nucleo\Helpers;

try {
    //namespace dos controladores
    SimpleRouter::setDefaultNamespace('sistema\Controlador');

    //SITE
    SimpleRouter::get(URL_SITE, 'SiteControlador@index');
    SimpleRouter::get(URL_SITE . 'index.php', 'SiteControlador@index');
    SimpleRouter::get(URL_SITE . 'sobre-nos', 'SiteControlador@sobre');
    SimpleRouter::get(URL_SITE . 'privacidade', 'SiteControlador@privacidade');
    SimpleRouter::get(URL_SITE . 'termos', 'SiteControlador@termos');
    SimpleRouter::get(URL_SITE . 'transparencia', 'SiteControlador@transparencia');
    SimpleRouter::get(URL_SITE . 'post/{categoria}/{slug}', 'SiteControlador@post');
    SimpleRouter::get(URL_SITE . 'categoria/{slug}/{pagina?}', 'SiteControlador@categoria');
    SimpleRouter::post(URL_SITE . 'buscar', 'SiteControlador@buscar');
    SimpleRouter::get(URL_SITE . '404', 'SiteControlador@erro404');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'contato', 'SiteControlador@contato');

    // DOACOES
    SimpleRouter::get(URL_SITE . 'doar', 'DoacaoControlador@index');
    SimpleRouter::post(URL_SITE . 'doar/processar', 'DoacaoControlador@processar');
    SimpleRouter::post(URL_SITE . 'doacao/verificar', 'DoacaoControlador@verificar');

    // ROTA DA TELA DE PAGAMENTO (Corrigida com a subpasta Pagamento\)
    SimpleRouter::get(URL_SITE . 'doacao/pagamento/{id}', 'Pagamento\PagamentoInfinitepayControlador@exibirTelaPagamento');

    // ROTA DO WEBHOOK (Corrigida com a subpasta Pagamento\)
    SimpleRouter::post(URL_SITE . 'webhook/infinitepay', 'Pagamento\PagamentoInfinitepayControlador@webhook');

    //USUARIO
    SimpleRouter::match(['get', 'post'], URL_SITE . 'cadastro', 'UsuarioControlador@cadastro');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'editar', 'UsuarioControlador@editar');
    SimpleRouter::post(URL_SITE . 'login', 'UsuarioControlador@login');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'usuario/confirmar/email/{token}', 'UsuarioControlador@confirmarEmail');

    //SAAS
    SimpleRouter::get(URL_SITE . 'saas', 'SaasControlador@index');
    SimpleRouter::get(URL_SITE . 'saas/sair', 'SaasControlador@sair');
    SimpleRouter::get(URL_SITE . 'inscricoes/{id}', 'SaasControlador@inscricoes');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'completar/cadastro/{id}', 'SaasControlador@cadastro');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'alterar/senha/{id}', 'SaasControlador@alterarSenha');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'saas/enderecos/editar/{id}', 'SaasControlador@editarEndereco');
    SimpleRouter::post(URL_SITE . 'saas/datatable', 'SaasControlador@datatable');

    //ROTAS ADMIN
    SimpleRouter::group(['namespace' => 'Admin'], function () {

        //ADMIN LOGIN
        SimpleRouter::get(URL_ADMIN, 'AdminLogin@index');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'login', 'AdminLogin@login');

        //DASHBOARD
        SimpleRouter::get(URL_ADMIN . 'dashboard', 'AdminDashboard@dashboard');
        SimpleRouter::get(URL_ADMIN . 'sair', 'AdminDashboard@sair');

        //ADMIN USUARIOS
        SimpleRouter::get(URL_ADMIN . 'usuarios/listar', 'AdminUsuarios@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'usuarios/cadastrar', 'AdminUsuarios@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'usuarios/editar/{id}', 'AdminUsuarios@editar');
        SimpleRouter::get(URL_ADMIN . 'usuarios/deletar/{id}', 'AdminUsuarios@deletar');
        SimpleRouter::post(URL_ADMIN . 'usuarios/datatable', 'AdminUsuarios@datatable');

        //ADMIN ENDERECOS
        SimpleRouter::get(URL_ADMIN . 'enderecos/listar/', 'AdminEnderecos@listar');
        SimpleRouter::post(URL_ADMIN . 'buscar/cpf', 'AdminEnderecos@buscar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'enderecos/cadastrar/{id?}', 'AdminEnderecos@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'enderecos/editar/{id}', 'AdminEnderecos@editar');
        SimpleRouter::post(URL_ADMIN . 'enderecos/datatable', 'AdminEnderecos@datatable');

        //ADMIN INSCRIÇÕES
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'inscricoes/listar/{id?}', 'AdminInscricao@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'inscricoes/cadastrar/{id?}', 'AdminInscricao@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'inscricoes/visualizar/{id?}', 'AdminInscricao@visualizar');
        SimpleRouter::post(URL_ADMIN . 'inscricoes/datatable', 'AdminInscricao@datatable');

        //ADMIN PROJETOS
        SimpleRouter::get(URL_ADMIN . 'posts/listar', 'AdminPosts@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'posts/cadastrar', 'AdminPosts@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'posts/editar/{id}', 'AdminPosts@editar');
        SimpleRouter::get(URL_ADMIN . 'posts/deletar/{id}', 'AdminPosts@deletar');
        SimpleRouter::post(URL_ADMIN . 'posts/datatable', 'AdminPosts@datatable');
    });

    SimpleRouter::start();
} catch (Pecee\SimpleRouter\Exceptions\NotFoundHttpException $ex) {
    // Agora, mesmo em localhost, se errar a rota, ele manda para a nossa tela 404 premium!
    Helpers::redirecionar('404');
}
// } catch (Pecee\SimpleRouter\Exceptions\NotFoundHttpException $ex) {
//     if (Helpers::localhost()) {
//         echo $ex->getMessage();
//     } else {
//         Helpers::redirecionar('404');
//     }
// }
