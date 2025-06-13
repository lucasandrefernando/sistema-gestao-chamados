<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';

/**
 * Controlador para a página inicial
 */
class HomeController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        // Construtor vazio
    }

    /**
     * Página inicial
     */
    public function index()
    {
        // Se estiver autenticado, redireciona para o dashboard
        if (is_authenticated()) {
            redirect('dashboard');
        } else {
            // Se não estiver autenticado, redireciona para o login
            redirect('auth');
        }
    }
}
