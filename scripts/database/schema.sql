-- schema.sql
-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS eagle_chamados CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE eagle_chamados;

-- Tabela de empresas
CREATE TABLE
    empresas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        cnpj VARCHAR(20) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        telefone VARCHAR(20) DEFAULT NULL,
        endereco VARCHAR(255) DEFAULT NULL,
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Tabela de licenças
CREATE TABLE
    licencas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empresa_id INT NOT NULL,
        quantidade INT NOT NULL DEFAULT 1,
        data_inicio DATE NOT NULL,
        data_fim DATE NOT NULL,
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
    );

-- Tabela de usuários
CREATE TABLE
    usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empresa_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        senha VARCHAR(255) NOT NULL,
        cargo VARCHAR(50) DEFAULT NULL,
        admin BOOLEAN DEFAULT FALSE,
        admin_tipo ENUM ('regular', 'master', 'global') DEFAULT NULL,
        ativo BOOLEAN DEFAULT TRUE,
        ultimo_acesso DATETIME DEFAULT NULL,
        token_recuperacao VARCHAR(100) DEFAULT NULL,
        token_expiracao DATETIME DEFAULT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        removido BOOLEAN NOT NULL DEFAULT FALSE,
        data_remocao DATETIME DEFAULT NULL,
        tentativas_login INT DEFAULT 0,
        bloqueado_ate DATETIME DEFAULT NULL,
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE,
        UNIQUE KEY email_empresa_unique (email, empresa_id)
    );

-- Tabela de setores
CREATE TABLE
    setores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empresa_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT DEFAULT NULL,
        ativo BOOLEAN DEFAULT TRUE,
        removido BOOLEAN DEFAULT FALSE,
        criado_por INT DEFAULT NULL,
        criado_em DATETIME DEFAULT NULL,
        atualizado_por INT DEFAULT NULL,
        atualizado_em DATETIME DEFAULT NULL,
        removido_por INT DEFAULT NULL,
        data_remocao DATETIME DEFAULT NULL,
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
    );

-- Tabela de associação entre usuários e setores
CREATE TABLE
    usuarios_setores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        setor_id INT NOT NULL,
        principal BOOLEAN DEFAULT FALSE,
        criado_por INT DEFAULT NULL,
        criado_em DATETIME DEFAULT NULL,
        UNIQUE KEY uk_usuario_setor (usuario_id, setor_id)
    );

-- Tabela de status de chamados
CREATE TABLE
    status_chamados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL,
        cor VARCHAR(7) NOT NULL,
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Tabela de chamados
CREATE TABLE
    chamados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empresa_id INT NOT NULL,
        setor_id INT NOT NULL,
        status_id INT NOT NULL DEFAULT 1,
        solicitante VARCHAR(100) NOT NULL,
        paciente VARCHAR(100) DEFAULT NULL,
        quarto_leito VARCHAR(20) DEFAULT NULL,
        descricao TEXT NOT NULL,
        tipo_servico VARCHAR(100) DEFAULT NULL,
        email_origem VARCHAR(100) DEFAULT NULL,
        data_solicitacao DATETIME NOT NULL,
        data_conclusao DATETIME DEFAULT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE,
        FOREIGN KEY (setor_id) REFERENCES setores (id),
        FOREIGN KEY (status_id) REFERENCES status_chamados (id)
    );

-- Tabela de comentários de chamados
CREATE TABLE
    chamados_comentarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chamado_id INT NOT NULL,
        usuario_id INT DEFAULT NULL,
        comentario TEXT NOT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (chamado_id) REFERENCES chamados (id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
    );

-- Tabela de histórico de chamados
CREATE TABLE
    historico_chamados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chamado_id INT NOT NULL,
        setor_id_anterior INT NOT NULL,
        setor_id_novo INT NOT NULL,
        status_id_anterior INT NOT NULL,
        status_id_novo INT NOT NULL,
        usuario_id INT DEFAULT NULL,
        observacao TEXT DEFAULT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (chamado_id) REFERENCES chamados (id) ON DELETE CASCADE,
        FOREIGN KEY (setor_id_anterior) REFERENCES setores (id),
        FOREIGN KEY (setor_id_novo) REFERENCES setores (id),
        FOREIGN KEY (status_id_anterior) REFERENCES status_chamados (id),
        FOREIGN KEY (status_id_novo) REFERENCES status_chamados (id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
    );

-- Tabela de logs
CREATE TABLE
    logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT DEFAULT NULL,
        chamado_id INT DEFAULT NULL,
        acao VARCHAR(50) NOT NULL,
        descricao TEXT NOT NULL,
        dados_anteriores TEXT DEFAULT NULL,
        dados_novos TEXT DEFAULT NULL,
        ip VARCHAR(45) DEFAULT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
        FOREIGN KEY (chamado_id) REFERENCES chamados (id) ON DELETE CASCADE
    );

-- Inserção dos status padrão
INSERT INTO
    status_chamados (nome, cor)
VALUES
    ('Aberto', '#FF0000'),
    ('Em Atendimento', '#FFA500'),
    ('Pausado', '#FFFF00'),
    ('Concluído', '#00FF00'),
    ('Cancelado', '#808080');

-- Inserção da empresa inicial (Hospital Madre Teresa)
INSERT INTO
    empresas (nome, cnpj, email, telefone)
VALUES
    (
        'Hospital Madre Teresa',
        '00.000.000/0001-00',
        'contato@madreteresa.org.br',
        '(31) 0000-0000'
    );

-- Inserção da empresa Eagle Tele Informática
INSERT INTO
    empresas (nome, cnpj, email, telefone, endereco)
VALUES
    (
        'Eagle Tele Informática Ltda',
        '07.592.851/0001-10',
        'contato@eagletelecom.com.br',
        '(31) 4501-2600',
        'Rua Machado Nunes, 116 - Cj 301 - Caiçara Belo Horizonte - Minas Gerais CEP: 30.775-530'
    );

-- Inserção de licenças iniciais
INSERT INTO
    licencas (empresa_id, quantidade, data_inicio, data_fim)
VALUES
    (
        1,
        4,
        CURDATE (),
        DATE_ADD (CURDATE (), INTERVAL 1 YEAR)
    );

INSERT INTO
    licencas (
        empresa_id,
        quantidade,
        data_inicio,
        data_fim,
        ativo
    )
VALUES
    (
        1,
        1,
        CURDATE (),
        DATE_ADD (CURDATE (), INTERVAL 1 YEAR),
        FALSE
    );

INSERT INTO
    licencas (empresa_id, quantidade, data_inicio, data_fim)
VALUES
    (
        2,
        2,
        CURDATE (),
        DATE_ADD (CURDATE (), INTERVAL 1 YEAR)
    );

-- Criação do usuário administrador inicial (senha: admin123)
INSERT INTO
    usuarios (
        empresa_id,
        nome,
        email,
        senha,
        admin,
        admin_tipo,
        ativo
    )
VALUES
    (
        1,
        'Lucas André',
        'lucasandre.sanos@gmail.com',
        '$2y$10$KlRITtzgXnm7.zJQPC3Cg.wbgYm9RzFY.VgCeaIYIxUTFXcXUFhJq',
        TRUE,
        'master',
        TRUE
    );

-- Inserção dos setores para Hospital Madre Teresa
INSERT INTO
    setores (empresa_id, nome, descricao, criado_em)
VALUES
    (
        1,
        'Serviços de Manutenção',
        'Manutenção predial e de equipamentos',
        NOW ()
    ),
    (
        1,
        'Itens de Rouparia',
        'Gestão de roupas e tecidos hospitalares',
        NOW ()
    ),
    (
        1,
        'Serviço de Higienização',
        'Limpeza e higienização de ambientes',
        NOW ()
    ),
    (
        1,
        'Serviço de Nutrição',
        'Alimentação de pacientes e funcionários',
        NOW ()
    ),
    (
        1,
        'Serviços Religiosos',
        'Apoio espiritual e religioso',
        NOW ()
    ),
    (
        1,
        'Serviços de Tecnologia da Informação',
        'Suporte técnico e sistemas',
        NOW ()
    );

-- Inserção dos setores para Eagle Tele Informática
INSERT INTO
    setores (
        empresa_id,
        nome,
        descricao,
        criado_por,
        criado_em
    )
VALUES
    (
        2,
        'Recebimento de Mercadoria',
        'Central de Recebimento de Mercadoria',
        1,
        NOW ()
    ),
    (
        2,
        'Suporte Técnico',
        'Setor de Informática',
        1,
        NOW ()
    ),
    (2, 'Comercial', 'Central de Comércio', 1, NOW ()),
    (
        2,
        'Televendas',
        'Central de Vendas por Telemarketing',
        1,
        NOW ()
    );

-- Inserção de chamados de exemplo para o Hospital Madre Teresa
INSERT INTO
    chamados (
        empresa_id,
        setor_id,
        status_id,
        solicitante,
        paciente,
        quarto_leito,
        descricao,
        tipo_servico,
        email_origem,
        data_solicitacao
    )
VALUES
    (
        1,
        1,
        1,
        'João Silva',
        'Maria Oliveira',
        '101-A',
        'Manutenção no ar condicionado do quarto 101-A',
        'Manutenção',
        'joao.silva@exemplo.com',
        NOW () - INTERVAL 6 DAY
    ),
    (
        1,
        1,
        2,
        'Ana Santos',
        'Pedro Costa',
        '202-B',
        'Troca de lâmpada queimada no banheiro do quarto 202-B',
        'Manutenção',
        'ana.santos@exemplo.com',
        NOW () - INTERVAL 5 DAY
    ),
    (
        1,
        3,
        1,
        'Ricardo Oliveira',
        'Sandra Pereira',
        '105-A',
        'Limpeza urgente no quarto 105-A após procedimento',
        'Higienização',
        'ricardo.oliveira@exemplo.com',
        NOW () - INTERVAL 6 DAY
    ),
    (
        1,
        4,
        1,
        'Luciana Martins',
        'Eduardo Costa',
        '120-A',
        'Solicitação de dieta especial para paciente diabético no quarto 120-A',
        'Nutrição',
        'luciana.martins@exemplo.com',
        NOW () - INTERVAL 3 DAY
    ),
    (
        1,
        5,
        1,
        'Roberto Mendes',
        'Luiza Oliveira',
        '130-A',
        'Solicitação de visita pastoral para paciente em estado grave no quarto 130-A',
        'Religioso',
        'roberto.mendes@exemplo.com',
        NOW () - INTERVAL 6 DAY
    ),
    (
        1,
        6,
        1,
        'Marcos Almeida',
        NULL,
        'Recepção',
        'Computador da recepção não está ligando',
        'TI',
        'marcos.almeida@exemplo.com',
        NOW () - INTERVAL 3 DAY
    );

-- Inserção de chamados concluídos
INSERT INTO
    chamados (
        empresa_id,
        setor_id,
        status_id,
        solicitante,
        paciente,
        quarto_leito,
        descricao,
        tipo_servico,
        email_origem,
        data_solicitacao,
        data_conclusao
    )
VALUES
    (
        1,
        1,
        4,
        'Carlos Ferreira',
        'Lúcia Mendes',
        '305-C',
        'Conserto de vazamento na pia do quarto 305-C',
        'Hidráulica',
        'carlos.ferreira@exemplo.com',
        NOW () - INTERVAL 4 DAY,
        NOW () - INTERVAL 4 DAY + INTERVAL 4 HOUR
    ),
    (
        1,
        4,
        4,
        'Marcelo Lima',
        'Cristina Sousa',
        '225-B',
        'Alteração na dieta do paciente do quarto 225-B para sem lactose',
        'Nutrição',
        'marcelo.lima@exemplo.com',
        NOW () - INTERVAL 2 DAY,
        NOW () - INTERVAL 1 DAY
    ),
    (
        1,
        5,
        4,
        'Cristina Rodrigues',
        'Paulo Santos',
        '235-B',
        'Pedido de oração para paciente em pré-operatório no quarto 235-B',
        'Religioso',
        'cristina.rodrigues@exemplo.com',
        NOW () - INTERVAL 5 DAY,
        NOW () - INTERVAL 5 DAY + INTERVAL 45 MINUTE
    ),
    (
        1,
        6,
        4,
        'Felipe Costa',
        NULL,
        'Administração',
        'Instalação de novo software no departamento de Administração',
        'TI',
        'felipe.costa@exemplo.com',
        NOW () - INTERVAL 1 DAY,
        NOW () - INTERVAL 1 DAY + INTERVAL 2 HOUR
    );