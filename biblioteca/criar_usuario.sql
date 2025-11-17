-- 1. APAGAR a tabela (se existir)
DROP TABLE IF EXISTS usuario;

-- 2. CRIAÇÃO da Tabela Usuario com chaves e constraints
CREATE TABLE usuario
(
    -- ID do usuário (Chave Primária e Auto-Incremento)
    id_usuario  INT AUTO_INCREMENT NOT NULL,
    
    -- Dados básicos
    nome        VARCHAR (100) NOT NULL,
    email       VARCHAR (100) NOT NULL,
    senha_hash  VARCHAR (255) NOT NULL,
    
    -- Perfil de acesso (Ex: 'admin', 'bibliotecario', 'cliente')
    perfil      VARCHAR (15) NOT NULL DEFAULT 'cliente',
    
    -- Status do usuário (1 para ativo, 0 para inativo)
    ativo       TINYINT(1) NOT NULL DEFAULT 1,
    
    -- Data de criação do registro
    criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Definição das chaves
    PRIMARY KEY (id_usuario),
    UNIQUE KEY (email) -- Garante que cada e-mail seja único
);

-- 3. Inserir um usuário administrador inicial
-- Senha de exemplo usada para esta hash: 'admin123'
-- Você deve gerar hashes seguras com password_hash() no PHP
INSERT INTO usuario (nome, email, senha_hash, perfil) VALUES 
('Administrador Inicial', 'admin@biblioteca.com', '$2y$10$3p/lFpU2iW7B0y/T0jBv.uBw8k.3i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6', 'admin');