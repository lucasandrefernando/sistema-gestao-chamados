<?php

/**
 * Script para configurar o banco de dados
 */

// Carrega as configurações
require_once __DIR__ . '/../config/database.php';

try {
    // Conectar ao MySQL sem selecionar um banco de dados
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar o banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    echo "Banco de dados '" . DB_NAME . "' criado ou já existente.\n";

    // Selecionar o banco de dados
    $pdo->exec("USE `" . DB_NAME . "`");

    // Ler e executar o script SQL
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');

    // Dividir o script em comandos individuais
    $commands = array_filter(array_map('trim', explode(';', $sql)));

    // Executar cada comando
    foreach ($commands as $command) {
        if (!empty($command)) {
            $pdo->exec($command);
        }
    }

    echo "Script SQL executado com sucesso!\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
