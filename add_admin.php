<?php
// Carrega as configurações do banco de dados
require_once 'config/database.php';

try {
    // Obtém a conexão
    $db = Database::getInstance()->getConnection();

    // Prepara e executa a query
    $sql = "INSERT INTO usuarios (empresa_id, nome, email, senha, admin, ativo) 
            VALUES (1, 'Lucas André', 'lucasandre.sanos@gmail.com', '$2y$10$KlRITtzgXnm7.zJQPC3Cg.wbgYm9RzFY.VgCeaIYIxUTFXcXUFhJq', TRUE, TRUE)";

    $result = $db->exec($sql);

    if ($result) {
        echo "Usuário administrador criado com sucesso!";
    } else {
        echo "Erro ao criar usuário administrador.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
