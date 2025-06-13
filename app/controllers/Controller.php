<?php

/**
 * Classe base para todos os controladores
 */
abstract class Controller
{
    /**
     * Renderiza uma view
     */
    protected function render($view, $data = [])
    {
        // Extrai os dados para variáveis
        extract($data);

        // Inclui o cabeçalho
        include ROOT_DIR . '/app/views/templates/header.php';

        // Inclui a view
        include ROOT_DIR . '/app/views/' . $view . '.php';

        // Inclui o rodapé
        include ROOT_DIR . '/app/views/templates/footer.php';
    }

    /**
     * Renderiza uma view sem o template
     */
    protected function renderPartial($view, $data = [])
    {
        // Extrai os dados para variáveis
        extract($data);

        // Inclui a view
        include ROOT_DIR . '/app/views/' . $view . '.php';
    }

    /**
     * Retorna uma resposta JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Verifica se o usuário tem permissão de administrador
     */
    protected function requireAdmin()
    {
        if (!is_admin()) {
            set_flash_message('error', 'Você não tem permissão para acessar esta página.');
            redirect('dashboard');
            exit;
        }
    }

    /**
     * Obtém dados do POST
     */
    protected function getPostData()
    {
        $data = [];

        foreach ($_POST as $key => $value) {
            $data[$key] = sanitize_input($value);
        }

        return $data;
    }

    /**
     * Obtém dados do GET
     */
    protected function getQueryData()
    {
        $data = [];

        foreach ($_GET as $key => $value) {
            $data[$key] = sanitize_input($value);
        }

        return $data;
    }

    /**
     * Valida campos obrigatórios
     */
    protected function validateRequired($data, $fields)
    {
        $errors = [];

        foreach ($fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "O campo {$label} é obrigatório.";
            }
        }

        return $errors;
    }
}
