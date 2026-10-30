<?php
// Router para o servidor embutido do PHP (php -S) em desenvolvimento.
// Se o arquivo pedido existir fisicamente (css, js, imagens, uploads),
// deixa o servidor embutido servir ele direto. Senão, cai no index.php da aplicação.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false; // deixa o servidor embutido servir o arquivo estático
}

require __DIR__ . '/index.php';