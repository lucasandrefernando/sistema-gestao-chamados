-- schema.sql
-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS eagle_chamados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eagle_chamados;

-- Tabela de empresas
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de licenças
CREATE TABLE licencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(50),
    admin BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_acesso DATETIME,
    token_recuperacao VARCHAR(100),
    token_expiracao DATETIME,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de setores
CREATE TABLE setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserção dos setores padrão
INSERT INTO setores (nome, descricao) VALUES 
('Serviços de Manutenção', 'Manutenção predial e de equipamentos'),
('Itens de Rouparia', 'Gestão de roupas e tecidos hospitalares'),
('Serviço de Higienização', 'Limpeza e higienização de ambientes'),
('Serviço de Nutrição', 'Alimentação de pacientes e funcionários'),
('Serviços Religiosos', 'Apoio espiritual e religioso'),
('Serviços de Tecnologia da Informação', 'Suporte técnico e sistemas');

-- Tabela de status de chamados
CREATE TABLE status_chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cor VARCHAR(7) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserção dos status padrão
INSERT INTO status_chamados (nome, cor) VALUES 
('Aberto', '#FF0000'),
('Em Atendimento', '#FFA500'),
('Pausado', '#FFFF00'),
('Concluído', '#00FF00'),
('Cancelado', '#808080');

-- Tabela de chamados
CREATE TABLE chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    setor_id INT NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    solicitante VARCHAR(100) NOT NULL,
    paciente VARCHAR(100),
    quarto_leito VARCHAR(20),
    descricao TEXT NOT NULL,
    tipo_servico VARCHAR(100),
    email_origem VARCHAR(100),
    data_solicitacao DATETIME NOT NULL,
    data_conclusao DATETIME,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE RESTRICT,
    FOREIGN KEY (status_id) REFERENCES status_chamados(id) ON DELETE RESTRICT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de histórico de chamados (para transferências entre setores)
CREATE TABLE historico_chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT NOT NULL,
    setor_id_anterior INT NOT NULL,
    setor_id_novo INT NOT NULL,
    status_id_anterior INT NOT NULL,
    status_id_novo INT NOT NULL,
    usuario_id INT,
    observacao TEXT,
    FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    FOREIGN KEY (setor_id_anterior) REFERENCES setores(id) ON DELETE RESTRICT,
    FOREIGN KEY (setor_id_novo) REFERENCES setores(id) ON DELETE RESTRICT,
    FOREIGN KEY (status_id_anterior) REFERENCES status_chamados(id) ON DELETE RESTRICT,
    FOREIGN KEY (status_id_novo) REFERENCES status_chamados(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de logs
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    chamado_id INT,
    acao VARCHAR(50) NOT NULL,
    descricao TEXT NOT NULL,
    dados_anteriores TEXT,
    dados_novos TEXT,
    ip VARCHAR(45),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Inserção da empresa inicial (Hospital Madre Teresa)
INSERT INTO empresas (nome, cnpj, email, telefone) 
VALUES ('Hospital Madre Teresa', '00.000.000/0001-00', 'contato@madreteresa.org.br', '(31) 0000-0000');

-- Inserção de licenças iniciais
INSERT INTO licencas (empresa_id, quantidade, data_inicio, data_fim) 
VALUES (1, 5, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR));

-- Criação do usuário administrador inicial (senha: admin123)
INSERT INTO usuarios (empresa_id, nome, email, senha, admin, ativo) 
VALUES (1, 'Administrador', 'admin@madreteresa.org.br', '$2y$10$KlRITtzgXnm7.zJQPC3Cg.wbgYm9RzFY.VgCeaIYIxUTFXcXUFhJq', TRUE, TRUE);