<?php
session_start();
/**
 * Configurações gerais da aplicação
 */

// Informações básicas da aplicação
define('APP_NAME', 'Sistema de Gestão de Chamados');
define('APP_VERSION', '2.0.0');
//define('APP_URL', 'http://localhost/sistema-gestao-chamados');
define('APP_URL', 'https://chamado.eagletelecom.com.br');
define('APP_PRODUCTION', false);

// Configurações de e-mail
define('EMAIL_FROM', 'lucas.santos@eagletelecom.com.br');
define('EMAIL_FROM_NAME', 'Sistema de Gestão de Chamados');
define('EMAIL_REPLY_TO', 'lucas.santos@eagletelecom.com.br');

// Configurações de SMTP
define('EMAIL_SMTP_ENABLED', true);
define('EMAIL_SMTP_HOST', 'smtp.gmail.com'); // Altere para o seu servidor SMTP
define('EMAIL_SMTP_AUTH', true);
define('EMAIL_SMTP_USERNAME', 'eagle.madreteresa@gmail.com'); // Altere para o seu e-mail
define('EMAIL_SMTP_PASSWORD', 'sssi yskm dznh lwco'); // Altere para sua senha ou senha de app
define('EMAIL_SMTP_SECURE', 'tls'); // tls ou ssl
define('EMAIL_SMTP_PORT', 587); // 587 (TLS) ou 465 (SSL)
define('EMAIL_SMTP_DEBUG', false); // true para debug, false para produção

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_PRODUCTION ? 1 : 0);

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
 * Verifica se o usuário está autenticado e se a sessão não expirou
 */
function is_authenticated()
{
    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Verifica se a sessão expirou (30 minutos de inatividade)
    $sessionTimeout = 30 * 60; // 30 minutos em segundos
    $currentTime = time();
    $lastActivity = $_SESSION['last_activity'] ?? 0;

    if ($currentTime - $lastActivity > $sessionTimeout) {
        // Sessão expirou, faz logout
        session_destroy();
        return false;
    }

    // Atualiza o timestamp de última atividade
    $_SESSION['last_activity'] = $currentTime;

    return true;
}

/**
 * Sanitiza entrada de dados
 * 
 * @param string $input Entrada a ser sanitizada
 * @return string Entrada sanitizada
 */
function sanitize_input($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
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
 * 
 * @return int ID do usuário
 */
function get_user_id()
{
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
}

/**
 * Obtém o ID da empresa do usuário logado
 */
function get_empresa_id()
{
    return $_SESSION['empresa_id'] ?? null;
}

/**
 * Verifica se o usuário é administrador master
 */
function is_admin_master()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true &&
        isset($_SESSION['admin_tipo']) && $_SESSION['admin_tipo'] === 'master';
}

/**
 * Verifica se o usuário é administrador regular
 */
function is_admin_regular()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true &&
        (!isset($_SESSION['admin_tipo']) || $_SESSION['admin_tipo'] === 'regular');
}

/**
 * Verifica se um registro pertence à empresa do usuário logado
 */
function check_empresa_access($record, $redirect = 'dashboard')
{
    if (!isset($record['empresa_id']) || $record['empresa_id'] != get_empresa_id()) {
        set_flash_message('error', 'Você não tem permissão para acessar este recurso.');
        redirect($redirect);
        exit;
    }
    return true;
}

/**
 * Verifica se o usuário é administrador global
 */
function is_admin_global()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true &&
        isset($_SESSION['admin_tipo']) && $_SESSION['admin_tipo'] === 'global';
}

/**
 * Verifica se um token CSRF é válido
 * 
 * @param string $token Token a ser verificado
 * @return bool True se o token for válido, false caso contrário
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

/**
 * Gera um token CSRF
 * 
 * @return string Token CSRF gerado
 */
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Formata o tempo em minutos para uma representação legível
 * 
 * @param int $minutos Tempo em minutos
 * @return string Tempo formatado
 */
function formatarTempo($minutos)
{
    if ($minutos < 60) {
        return $minutos . ' min';
    } elseif ($minutos < 1440) { // menos de 24 horas
        $horas = floor($minutos / 60);
        $min = $minutos % 60;
        return $horas . 'h ' . ($min > 0 ? $min . 'min' : '');
    } else {
        $dias = floor($minutos / 1440);
        $horas = floor(($minutos % 1440) / 60);
        return $dias . 'd ' . ($horas > 0 ? $horas . 'h' : '');
    }
}




/**
 * Retorna a cor CSS para um status de chamado
 * 
 * @param string $status Status do chamado
 * @return string Classe CSS de cor
 */
function getStatusColor($status)
{
    $colors = [
        'aberto' => 'danger',
        'em_atendimento' => 'warning',
        'em_andamento' => 'warning',
        'pausado' => 'info',
        'concluido' => 'success',
        'cancelado' => 'secondary'
    ];

    return $colors[$status] ?? 'primary';
}

/**
 * Formata um status para exibição
 * 
 * @param string $status Status do chamado
 * @return string Status formatado
 */
function formatarStatus($status)
{
    $formatado = [
        'aberto' => 'Aberto',
        'em_atendimento' => 'Em Atendimento',
        'em_andamento' => 'Em Andamento',
        'pausado' => 'Pausado',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado'
    ];

    return $formatado[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Formata a prioridade para exibição
 * 
 * @param string $prioridade Prioridade do chamado
 * @return string Prioridade formatada
 */
function formatarPrioridade($prioridade)
{
    $formatado = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'critica' => 'Crítica'
    ];

    return isset($formatado[$prioridade]) ? $formatado[$prioridade] : $prioridade;
}

/**
 * Formata uma data para exibição
 * 
 * @param string $data Data no formato MySQL
 * @return string Data formatada
 */
function formatarData($data)
{
    if (empty($data)) return '';

    $timestamp = strtotime($data);
    return date('d/m/Y H:i:s', $timestamp);
}

/**
 * Verifica se há uma mensagem flash do tipo especificado
 */
function has_flash_message($type)
{
    return isset($_SESSION['flash_messages'][$type]);
}
