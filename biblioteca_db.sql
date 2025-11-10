-- ========================================
-- SCRIPT DE CRIAÇÃO DO BANCO DE DADOS
-- Sistema de Biblioteca - Módulo 5
-- ========================================

-- Remove o banco se já existir (cuidado em produção!)
DROP DATABASE IF EXISTS biblioteca;

-- Cria o banco de dados com charset UTF8
CREATE DATABASE biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco para uso
USE biblioteca;

-- ========================================
-- TABELA: autores
-- Armazena informações dos autores dos livros
-- ========================================
CREATE TABLE autores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    nacionalidade VARCHAR(50),
    data_nascimento DATE,
    biografia TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: livros
-- Armazena o catálogo de livros da biblioteca
-- ========================================
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor_id INT NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    ano_publicacao YEAR,
    editora VARCHAR(100),
    numero_paginas INT,
    quantidade_total INT DEFAULT 1,
    quantidade_disponivel INT DEFAULT 1,
    categoria VARCHAR(50),
    localizacao VARCHAR(50), -- Ex: Estante A, Prateleira 3
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Chave estrangeira para autores
    FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE RESTRICT,
    
    -- Índices para melhorar performance em buscas
    INDEX idx_titulo (titulo),
    INDEX idx_autor (autor_id),
    INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: clientes
-- Cadastro dos usuários da biblioteca
-- ========================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    data_nascimento DATE,
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    status ENUM('Ativo', 'Inativo', 'Bloqueado') DEFAULT 'Ativo',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_email (email),
    INDEX idx_nome (nome),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: emprestimos
-- Registra todos os empréstimos realizados
-- ========================================
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_emprestimo DATE NOT NULL,
    data_devolucao_prevista DATE NOT NULL,
    data_devolucao_real DATE NULL,
    status ENUM('Ativo', 'Devolvido', 'Atrasado', 'Cancelado') DEFAULT 'Ativo',
    multa DECIMAL(10,2) DEFAULT 0.00,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Chaves estrangeiras
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE RESTRICT,
    
    -- Índices
    INDEX idx_cliente (cliente_id),
    INDEX idx_livro (livro_id),
    INDEX idx_status (status),
    INDEX idx_data_emprestimo (data_emprestimo),
    INDEX idx_data_devolucao (data_devolucao_prevista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INSERÇÃO DE DADOS DE EXEMPLO
-- ========================================

-- Inserir autores
INSERT INTO autores (nome, nacionalidade, data_nascimento) VALUES
('Machado de Assis', 'Brasileira', '1839-06-21'),
('Clarice Lispector', 'Brasileira', '1920-12-10'),
('Jorge Amado', 'Brasileira', '1912-08-10'),
('J.K. Rowling', 'Britânica', '1965-07-31'),
('George Orwell', 'Britânica', '1903-06-25'),
('Gabriel García Márquez', 'Colombiana', '1927-03-06'),
('Agatha Christie', 'Britânica', '1890-09-15'),
('Stephen King', 'Americana', '1947-09-21'),
('Paulo Coelho', 'Brasileira', '1947-08-24'),
('Monteiro Lobato', 'Brasileira', '1882-04-18');

-- Inserir livros
INSERT INTO livros (titulo, autor_id, isbn, ano_publicacao, editora, numero_paginas, quantidade_total, quantidade_disponivel, categoria, localizacao) VALUES
('Dom Casmurro', 1, '978-8535911664', 1899, 'Companhia das Letras', 256, 3, 3, 'Romance', 'Estante A1'),
('Memórias Póstumas de Brás Cubas', 1, '978-8535911671', 1881, 'Companhia das Letras', 368, 2, 2, 'Romance', 'Estante A1'),
('A Hora da Estrela', 2, '978-8520925683', 1977, 'Rocco', 88, 2, 1, 'Romance', 'Estante A2'),
('A Paixão Segundo G.H.', 2, '978-8532511010', 1964, 'Rocco', 176, 1, 1, 'Romance', 'Estante A2'),
('Capitães da Areia', 3, '978-8535914063', 1937, 'Companhia das Letras', 280, 4, 3, 'Romance', 'Estante A3'),
('Gabriela, Cravo e Canela', 3, '978-8535911046', 1958, 'Companhia das Letras', 424, 2, 2, 'Romance', 'Estante A3'),
('Harry Potter e a Pedra Filosofal', 4, '978-8532530787', 1997, 'Rocco', 264, 5, 4, 'Fantasia', 'Estante B1'),
('Harry Potter e a Câmara Secreta', 4, '978-8532530794', 1998, 'Rocco', 288, 3, 3, 'Fantasia', 'Estante B1'),
('1984', 5, '978-8535914849', 1949, 'Companhia das Letras', 416, 4, 3, 'Ficção', 'Estante B2'),
('A Revolução dos Bichos', 5, '978-8535909555', 1945, 'Companhia das Letras', 152, 3, 3, 'Ficção', 'Estante B2'),
('Cem Anos de Solidão', 6, '978-8501061294', 1967, 'Record', 424, 2, 2, 'Romance', 'Estante C1'),
('Assassinato no Expresso do Oriente', 7, '978-8595084841', 1934, 'HarperCollins', 256, 3, 2, 'Mistério', 'Estante C2'),
('O Iluminado', 8, '978-8581050584', 1977, 'Suma', 464, 2, 1, 'Terror', 'Estante C3'),
('O Alquimista', 9, '978-8522008865', 1988, 'Rocco', 224, 5, 5, 'Ficção', 'Estante D1'),
('O Sítio do Picapau Amarelo', 10, '978-8525406293', 1920, 'Globo', 288, 4, 4, 'Infantil', 'Estante D2');

-- Inserir clientes de exemplo
INSERT INTO clientes (nome, email, telefone, cpf, data_nascimento, endereco, cidade, estado, cep, status) VALUES
('Ana Silva Santos', 'ana.silva@email.com', '(11) 98765-4321', '123.456.789-00', '1995-03-15', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 'Ativo'),
('Carlos Eduardo Souza', 'carlos.souza@email.com', '(11) 97654-3210', '234.567.890-11', '1988-07-22', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 'Ativo'),
('Maria Oliveira Lima', 'maria.lima@email.com', '(21) 99876-5432', '345.678.901-22', '1992-11-08', 'Rua Copacabana, 456', 'Rio de Janeiro', 'RJ', '22070-000', 'Ativo'),
('João Pedro Costa', 'joao.costa@email.com', '(41) 98765-1234', '456.789.012-33', '2000-05-30', 'Rua XV de Novembro, 789', 'Curitiba', 'PR', '80020-310', 'Ativo'),
('Juliana Fernandes', 'juliana.fernandes@email.com', '(11) 96543-2109', '567.890.123-44', '1985-09-12', 'Rua Augusta, 2500', 'São Paulo', 'SP', '01412-100', 'Ativo');

-- Inserir alguns empréstimos de exemplo
INSERT INTO emprestimos (cliente_id, livro_id, data_emprestimo, data_devolucao_prevista, status) VALUES
(1, 7, '2025-11-01', '2025-11-08', 'Ativo'),
(2, 9, '2025-11-03', '2025-11-10', 'Ativo'),
(3, 3, '2025-10-28', '2025-11-04', 'Ativo'), -- Este está atrasado
(4, 5, '2025-10-25', '2025-11-01', 'Devolvido'),
(5, 12, '2025-11-02', '2025-11-09', 'Ativo');

-- Atualizar quantidade disponível dos livros emprestados ativos
UPDATE livros SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id IN (7, 9, 3, 12);

-- ========================================
-- VIEWS ÚTEIS PARA CONSULTAS
-- ========================================

-- View: Empréstimos com informações completas
CREATE VIEW vw_emprestimos_completos AS
SELECT 
    e.id,
    e.data_emprestimo,
    e.data_devolucao_prevista,
    e.data_devolucao_real,
    e.status,
    e.multa,
    c.id AS cliente_id,
    c.nome AS cliente_nome,
    c.email AS cliente_email,
    c.telefone AS cliente_telefone,
    l.id AS livro_id,
    l.titulo AS livro_titulo,
    l.isbn AS livro_isbn,
    a.nome AS autor_nome,
    DATEDIFF(CURDATE(), e.data_devolucao_prevista) AS dias_atraso,
    CASE 
        WHEN e.status = 'Ativo' AND CURDATE() > e.data_devolucao_prevista 
        THEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) * 2.50
        ELSE 0
    END AS multa_calculada
FROM emprestimos e
INNER JOIN clientes c ON e.cliente_id = c.id
INNER JOIN livros l ON e.livro_id = l.id
INNER JOIN autores a ON l.autor_id = a.id;

-- View: Estatísticas da biblioteca
CREATE VIEW vw_estatisticas_biblioteca AS
SELECT 
    (SELECT COUNT(*) FROM livros) AS total_livros,
    (SELECT SUM(quantidade_total) FROM livros) AS total_exemplares,
    (SELECT SUM(quantidade_disponivel) FROM livros) AS exemplares_disponiveis,
    (SELECT COUNT(*) FROM clientes WHERE status = 'Ativo') AS clientes_ativos,
    (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo') AS emprestimos_ativos,
    (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados;

-- ========================================
-- PROCEDURES ÚTEIS
-- ========================================

-- Procedure para calcular multa de um empréstimo
DELIMITER //
CREATE PROCEDURE sp_calcular_multa(IN emprestimo_id INT)
BEGIN
    DECLARE dias_atraso INT;
    DECLARE valor_multa DECIMAL(10,2);
    
    SELECT DATEDIFF(CURDATE(), data_devolucao_prevista)
    INTO dias_atraso
    FROM emprestimos
    WHERE id = emprestimo_id AND status = 'Ativo';
    
    IF dias_atraso > 0 THEN
        SET valor_multa = dias_atraso * 2.50;
        UPDATE emprestimos SET multa = valor_multa WHERE id = emprestimo_id;
    END IF;
END //
DELIMITER ;

-- ========================================
-- CONSULTAS ÚTEIS PARA TESTES
-- ========================================

-- Listar todos os livros disponíveis
-- SELECT l.*, a.nome AS autor FROM livros l 
-- INNER JOIN autores a ON l.autor_id = a.id 
-- WHERE quantidade_disponivel > 0;

-- Listar empréstimos ativos
-- SELECT * FROM vw_emprestimos_completos WHERE status = 'Ativo';

-- Verificar estatísticas
-- SELECT * FROM vw_estatisticas_biblioteca;

-- ========================================
-- FIM DO SCRIPT
-- ========================================
