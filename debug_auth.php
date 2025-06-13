<?php
// Define o diretório raiz da aplicação
define('ROOT_DIR', __DIR__);

// Carrega as configurações
require_once ROOT_DIR . '/config/app.php';
require_once ROOT_DIR . '/config/database.php';

// Carrega o modelo de usuário
require_once ROOT_DIR . '/app/models/Model.php';
require_once ROOT_DIR . '/app/models/Usuario.php';

// Email e senha para testar
$email = 'lucasandre.sanos@gmail.com';
$senha = 'admin123';

// Inicializa o modelo de usuário
$usuarioModel = new Usuario();

// Busca o usuário pelo email
$usuario = $usuarioModel->findOne('email = :email AND ativo = 1', ['email' => $email]);

echo "<h2>Depuração de Autenticação</h2>";

if ($usuario) {
    echo "<p>Usuário encontrado no banco de dados:</p>";
    echo "<pre>";
    print_r($usuario);
    echo "</pre>";

    // Verifica a senha
    $senhaCorreta = password_verify($senha, $usuario['senha']);

    echo "<p>Verificação de senha: " . ($senhaCorreta ? "CORRETA" : "INCORRETA") . "</p>";

    if (!$senhaCorreta) {
        echo "<p>Hash da senha armazenada: " . $usuario['senha'] . "</p>";
        echo "<p>Hash da senha fornecida (para comparação): " . password_hash($senha, PASSWORD_DEFAULT) . "</p>";
    }
} else {
    echo "<p>Usuário não encontrado no banco de dados com o email: $email</p>";

    // Listar todos os usuários para verificação
    echo "<h3>Lista de todos os usuários no banco:</h3>";
    $todosUsuarios = $usuarioModel->findAll();

    echo "<pre>";
    print_r($todosUsuarios);
    echo "</pre>";
}

// Verificar a conexão com o banco
echo "<h3>Informações da conexão com o banco:</h3>";
try {
    $db = Database::getInstance()->getConnection();
    echo "<p>Conexão com o banco de dados estabelecida com sucesso.</p>";
} catch (Exception $e) {
    echo "<p>Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}
