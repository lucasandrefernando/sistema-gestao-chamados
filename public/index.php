<?php

/**
 * Ponto de entrada da aplicação
 */

// Define o diretório raiz da aplicação
define('ROOT_DIR', dirname(__DIR__));

// Carrega as configurações
require_once ROOT_DIR . '/config/app.php';
require_once ROOT_DIR . '/config/database.php';

// Carrega os helpers
$helpersDir = ROOT_DIR . '/app/helpers/';
if (is_dir($helpersDir)) {
    $helperFiles = glob($helpersDir . '*.php');
    foreach ($helperFiles as $helperFile) {
        require_once $helperFile;
    }
}

// Obtém a URL requisitada
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Define o controlador, método e parâmetros
$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$methodName = $url[1] ?? 'index';
$params = array_slice($url, 2);

// Caminho para o arquivo do controlador
$controllerFile = ROOT_DIR . '/app/controllers/' . $controllerName . '.php';

// Verifica se o controlador existe
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // Instancia o controlador
    $controller = new $controllerName();

    // Verifica se o método existe
    if (method_exists($controller, $methodName)) {
        // Chama o método com os parâmetros
        call_user_func_array([$controller, $methodName], $params);
    } else {
        // Método não encontrado
        header("HTTP/1.0 404 Not Found");
        echo "Método não encontrado!";
    }
} else {
    // Controlador não encontrado
    header("HTTP/1.0 404 Not Found");
    echo "Página não encontrada!";
}
