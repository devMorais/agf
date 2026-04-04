<?php

use sistema\Nucleo\Helpers;

date_default_timezone_set('America/Sao_Paulo');

define('SITE_NOME', 'Associação Grande Família');
define('SITE_DESCRICAO', 'Realizando sonhos, construindo um futuro melhor para todos.');
define('WHATSAPP_DEV_NUMERO', '5561983411859');
// define('WHATSAPP_DEV_URL', 'https://wa.me/' . WHATSAPP_DEV_NUMERO . '?text=Olá, vi o sistema de votação e gostaria de conversar.');

// define('URL_PRODUCAO', 'https://hom-votar.devmorais.com.br/');
define('URL_PRODUCAO', 'https://associacaograndefamilia.devmorais.com.br/');


define('URL_DESENVOLVIMENTO', 'https://agf.test/');
define('SERVIDORES_LOCAIS', ['localhost', '127.0.0.1', 'agf.test']);

define('URL_INICIAL', 'agf');
define('STATUS_ATIVO', 1);
define('STATUS_INATIVO', 0);

if (Helpers::localhost()) {
    define('DB_HOST', 'localhost');
    define('DB_PORTA', '3306');
    define('DB_NOME', 'agf');
    define('DB_USUARIO', 'root');
    define('DB_SENHA', 'root');

    define('URL_SITE', '/');
    define('URL_ADMIN', '/admin/');

    // Asaas
    // define('ASAAS_KEY', '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmFkNjFiMWNhLTgwMmEtNDdiNy04Y2VjLTlkZTBhMjAxMzc0NDo6JGFhY2hfMTQ0ODU1MzAtNzAxOS00ZTBkLWIzYTAtOWVmZWQ4ZGJhZGJj');
    // define('ASAAS_URL', 'https://sandbox.asaas.com/api/v3');
    // define('ASAAS_SSL', false);

    // InfinitePay
    define('INFINITEPAY_HANDLE', 'fe_guiar92');
    define('INFINITEPAY_URL', 'https://api.infinitepay.io');
    define('INFINITEPAY_WEBHOOK_URL', 'https://agf.test/webhook/infinitepay');
    define('INFINITEPAY_REDIRECT_URL', null);
    define('INFINITEPAY_SSL', false);
} else {
    define('DB_HOST', 'localhost');
    define('DB_PORTA', '3306');
    define('DB_NOME', 'agf');
    define('DB_USUARIO', 'root');
    define('DB_SENHA', 'root');

    define('URL_SITE', '/');
    define('URL_ADMIN', '/admin/');

    // Asaas - HOMOLOGAÇÃO
    // define('ASAAS_KEY', '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmFkNjFiMWNhLTgwMmEtNDdiNy04Y2VjLTlkZTBhMjAxMzc0NDo6JGFhY2hfMTQ0ODU1MzAtNzAxOS00ZTBkLWIzYTAtOWVmZWQ4ZGJhZGJj');
    // define('ASAAS_URL', 'https://sandbox.asaas.com/api/v3');
    // define('ASAAS_SSL', true);

    // Asaas - PRODUÇÃO (descomentado quando for usar)
    // define('ASAAS_KEY', '$aact_YzU2ZDE3OGE2N2YxNzNjYTBlZmZlY2IzMzM2MWY3MGE6OjEwMDo6JGFhY2hfYjQxNGRmMWUtMGQzMC00ZDEwLWI5YjMtYTI5YjA3MzJhYzBi');
    // define('ASAAS_URL', 'https://api.asaas.com/api/v3');
    // define('ASAAS_SSL', true);

    // InfinitePay - PRODUÇÃO
    define('INFINITEPAY_HANDLE', 'fe_guiar92');
    define('INFINITEPAY_URL', 'https://api.infinitepay.io');
    // define('INFINITEPAY_WEBHOOK_URL', 'https://hom-votar.devmorais.com.br/webhook/infinitepay');
    define('INFINITEPAY_WEBHOOK_URL', 'https://associacaograndefamilia.devmorais.com.br/webhook/infinitepay');
    define('INFINITEPAY_REDIRECT_URL', null);
    define('INFINITEPAY_SSL', true);
}

define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_PORTA', '465');
define('EMAIL_USUARIO', 'contato@votepelasuamiss.com.br');
define('EMAIL_SENHA', 'Caita92*/');
define('EMAIL_REMETENTE', ['email' => EMAIL_USUARIO, 'nome' => SITE_NOME]);
