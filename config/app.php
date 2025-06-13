<?php

/**
 * Configurações gerais da aplicação
 */

// Informações básicas da aplicação
define('APP_NAME', 'Sistema de Gestão de Chamados');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://10.0.1.66/sistema-gestao-chamados');
define('APP_PRODUCTION', false);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_PRODUCTION ? 1 : 0);
session_start();

// Configurações de exibição de erros
if (APP_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Funções auxiliares globais
/**
 * Redireciona para uma URL específica
 */
function redirect($path)
{
    header('Location: ' . APP_URL . '/' . $path);
    exit;
}

/**
 * Retorna a URL base da aplicação
 */
function base_url($path = '')
{
    return APP_URL . '/' . $path;
}

/**
 * Exibe uma mensagem flash
 */
function set_flash_message($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtém e limpa a mensagem flash
 */
function get_flash_message()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitiza entrada de dados
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Verifica se o usuário está autenticado
 */
function is_authenticated()
{
    return isset($_SESSION['user_id']);
}

/**
 * Verifica se o usuário é administrador
 */
function is_admin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Obtém o ID do usuário logado
 */
function get_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtém o ID da empresa do usuário logado
 */
function get_empresa_id()
{
    return $_SESSION['empresa_id'] ?? null;
}
