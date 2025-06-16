<?php

/**
 * Middleware para verificar autenticação
 */
class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado
     */
    public static function requireAuth()
    {
        if (!is_authenticated()) {
            // Se não estiver autenticado, redireciona para o login
            set_flash_message('error', 'Você precisa fazer login para acessar esta página.');
            redirect('auth');
            exit;
        }
    }

    /**
     * Verifica se o usuário é administrador
     */
    public static function requireAdmin()
    {
        self::requireAuth();

        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            // Se não for admin, redireciona para o dashboard
            set_flash_message('error', 'Você não tem permissão para acessar esta página.');
            redirect('dashboard');
            exit;
        }
    }

    /**
     * Verifica se o usuário é administrador master
     */
    public static function requireAdminMaster()
    {
        self::requireAuth();

        if (
            !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true ||
            !isset($_SESSION['admin_tipo']) || $_SESSION['admin_tipo'] !== 'master'
        ) {
            // Se não for admin master, redireciona para o dashboard
            set_flash_message('error', 'Você não tem permissão para acessar esta página.');
            redirect('dashboard');
            exit;
        }
    }
}
