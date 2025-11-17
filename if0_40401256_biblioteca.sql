-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql201.infinityfree.com
-- Tempo de geração: 17/11/2025 às 18:14
-- Versão do servidor: 11.4.7-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_40401256_biblioteca`
--
CREATE DATABASE IF NOT EXISTS `if0_40401256_biblioteca` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `if0_40401256_biblioteca`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `autores`
--

CREATE TABLE `autores` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nacionalidade` varchar(50) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `autores`
--

INSERT INTO `autores` (`id`, `nome`, `nacionalidade`, `data_nascimento`, `biografia`, `created_at`, `updated_at`) VALUES
(1, 'Machado de Assis', 'Brasileira', '1839-06-21', 'Breve bibliografia', '2025-11-06 13:35:07', '2025-11-10 22:59:30'),
(2, 'Clarice Lispector', 'Brasileira', '1920-12-10', '', '2025-11-06 13:35:07', '2025-11-10 22:53:27'),
(3, 'Jorge Amado', 'Brasileira', '1912-08-10', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(4, 'J.K. Rowling', 'Britânica', '1965-07-31', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(5, 'George Orwell', 'Britânica', '1903-06-25', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(6, 'Gabriel García Márquez', 'Colombiana', '1927-06-06', '', '2025-11-06 13:35:07', '2025-11-10 22:53:42'),
(7, 'Agatha Christie', 'Britânica', '1890-09-15', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(8, 'Stephen King', 'Americana', '1947-09-21', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(9, 'Paulo Coelho', 'Brasileira', '1947-08-24', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(10, 'Monteiro Lobato', 'Brasileira', '1882-04-18', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(12, 'Aldrey Kich', 'Brasileira', '1977-06-30', 'teaaerasr', '2025-11-10 22:55:26', '2025-11-10 22:55:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `status` enum('Ativo','Inativo','Bloqueado') DEFAULT 'Ativo',
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `cpf`, `data_nascimento`, `endereco`, `cidade`, `estado`, `cep`, `status`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 'Ana Silva Santos', 'ana.silva@email.com', '(11) 98765-4321', '123.456.789-00', '1995-03-15', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 'Ativo', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(2, 'Carlos Eduardo Souza', 'carlos.souza@email.com', '(11) 97654-3210', '234.567.890-11', '1988-07-22', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 'Ativo', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(3, 'Maria Oliveira Lima', 'maria.lima@email.com', '(21) 99876-5432', '345.678.901-22', '1992-11-08', 'Rua Copacabana, 456', 'Rio de Janeiro', 'RJ', '22070-000', 'Ativo', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(4, 'João Pedro Costa', 'joao.costa@email.com', '(41) 98765-1234', '456.789.012-33', '2000-05-30', 'Rua XV de Novembro, 789', 'Curitiba', 'PR', '80020-310', 'Ativo', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(5, 'Juliana Fernandes', 'juliana.fernandes@email.com', '(11) 96543-2109', '567.890.123-44', '1985-09-12', 'Rua Augusta, 2500', 'São Paulo', 'SP', '01412-100', 'Ativo', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(6, 'Lucas', 'lucas.dressler.barros@escola.pr.gov.br', '45999111568', NULL, NULL, NULL, NULL, NULL, NULL, 'Ativo', NULL, '2025-11-06 13:36:06', '2025-11-06 13:36:06'),
(7, 'Marcio', 'marcio@escola.pr.gov.br', '45999111123', NULL, NULL, NULL, NULL, NULL, NULL, 'Ativo', NULL, '2025-11-06 14:24:08', '2025-11-06 14:24:08'),
(8, 'Junior', 'Juninho@play.com', '4599882244', NULL, NULL, NULL, NULL, NULL, NULL, 'Ativo', NULL, '2025-11-06 14:27:06', '2025-11-06 14:27:06'),
(9, 'Debora', 'deboramaria@gmail.com', '45999221313', NULL, NULL, NULL, NULL, NULL, NULL, 'Ativo', NULL, '2025-11-06 19:30:52', '2025-11-06 19:30:52'),
(13, 'Aldrey Kich', 'aldrey.kich@gmail.com', '45999449138', NULL, NULL, NULL, NULL, NULL, NULL, 'Ativo', NULL, '2025-11-10 22:56:25', '2025-11-10 22:56:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimos`
--

CREATE TABLE `emprestimos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `livro_id` int(11) NOT NULL,
  `data_emprestimo` date NOT NULL,
  `data_devolucao_prevista` date NOT NULL,
  `data_devolucao_real` date DEFAULT NULL,
  `status` enum('Ativo','Devolvido','Atrasado','Cancelado') DEFAULT 'Ativo',
  `multa` decimal(10,2) DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `emprestimos`
--

INSERT INTO `emprestimos` (`id`, `cliente_id`, `livro_id`, `data_emprestimo`, `data_devolucao_prevista`, `data_devolucao_real`, `status`, `multa`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 1, 7, '2025-11-01', '2025-11-22', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 13:35:07', '2025-11-06 20:20:44'),
(2, 2, 9, '2025-11-03', '2025-11-10', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 13:35:07', '2025-11-06 20:20:39'),
(3, 3, 3, '2025-10-28', '2025-11-04', '2025-11-06', 'Devolvido', '5.00', NULL, '2025-11-06 13:35:07', '2025-11-06 20:16:19'),
(4, 4, 5, '2025-10-25', '2025-11-01', NULL, 'Devolvido', '0.00', NULL, '2025-11-06 13:35:07', '2025-11-06 13:35:07'),
(5, 5, 12, '2025-11-02', '2025-11-09', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 13:35:07', '2025-11-06 20:20:41'),
(6, 6, 4, '2025-11-06', '2025-11-20', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 13:36:21', '2025-11-06 20:20:30'),
(7, 7, 9, '2025-11-06', '2025-11-13', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 14:24:15', '2025-11-06 20:20:33'),
(8, 8, 9, '2025-11-06', '2025-11-13', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 14:27:17', '2025-11-06 20:20:34'),
(9, 2, 10, '2025-11-06', '2025-11-13', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 19:24:40', '2025-11-06 20:20:36'),
(10, 9, 14, '2025-11-06', '2025-11-13', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 19:30:59', '2025-11-06 20:20:37'),
(11, 9, 13, '2025-11-06', '2025-11-13', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 20:20:07', '2025-11-06 20:20:38'),
(12, 1, 13, '2025-11-06', '2025-11-13', '2025-11-06', 'Devolvido', '0.00', NULL, '2025-11-06 20:25:20', '2025-11-06 20:30:45'),
(13, 2, 14, '2025-11-06', '2025-11-13', '2025-11-08', 'Devolvido', '0.00', NULL, '2025-11-06 20:26:50', '2025-11-07 23:27:44'),
(14, 2, 15, '2025-11-06', '2025-11-13', '2025-11-08', 'Devolvido', '0.00', NULL, '2025-11-06 20:27:56', '2025-11-07 23:29:03'),
(15, 6, 14, '2025-11-06', '2025-11-09', '2025-11-12', 'Devolvido', '7.50', NULL, '2025-11-06 22:52:01', '2025-11-11 23:51:37'),
(16, 6, 5, '2025-11-07', '2025-11-21', '2025-11-07', 'Devolvido', '0.00', NULL, '2025-11-07 21:58:51', '2025-11-07 22:01:03'),
(31, 6, 5, '2025-11-11', '2025-11-25', NULL, 'Ativo', '0.00', NULL, '2025-11-11 22:39:20', '2025-11-11 23:51:17'),
(32, 8, 5, '2025-11-11', '2025-11-25', NULL, 'Ativo', '0.00', NULL, '2025-11-11 22:42:49', '2025-11-11 23:47:18'),
(33, 7, 12, '2025-11-11', '2025-11-18', NULL, 'Ativo', '0.00', NULL, '2025-11-11 22:47:11', '2025-11-11 22:47:11'),
(34, 3, 6, '2025-11-11', '2025-11-18', NULL, 'Ativo', '0.00', NULL, '2025-11-11 22:48:58', '2025-11-11 22:48:58'),
(35, 9, 6, '2025-11-11', '2025-11-18', NULL, 'Ativo', '0.00', NULL, '2025-11-11 22:50:47', '2025-11-11 22:50:47'),
(36, 13, 2, '2025-11-11', '2025-12-02', '2025-11-12', 'Devolvido', '0.00', NULL, '2025-11-11 22:53:13', '2025-11-11 23:46:58'),
(37, 5, 4, '2025-11-11', '2025-11-18', '2025-11-12', 'Devolvido', '0.00', NULL, '2025-11-11 22:55:39', '2025-11-11 23:41:21'),
(38, 4, 3, '2025-11-11', '2025-11-18', '2025-11-12', 'Devolvido', '0.00', NULL, '2025-11-11 22:57:20', '2025-11-11 23:41:47'),
(40, 1, 10, '2025-11-12', '2025-12-03', NULL, 'Ativo', '0.00', NULL, '2025-11-11 23:01:54', '2025-11-12 17:24:39'),
(41, 1, 13, '2025-11-12', '2025-11-26', '2025-11-12', 'Devolvido', '0.00', NULL, '2025-11-11 23:08:37', '2025-11-11 23:37:48'),
(42, 5, 12, '2025-11-12', '2025-11-19', NULL, 'Ativo', '0.00', NULL, '2025-11-12 17:25:14', '2025-11-12 17:25:14'),
(43, 4, 5, '2025-11-17', '2025-11-24', NULL, 'Ativo', '0.00', NULL, '2025-11-17 22:41:52', '2025-11-17 22:41:52'),
(44, 2, 3, '2025-11-17', '2025-11-24', NULL, 'Ativo', '0.00', NULL, '2025-11-17 22:42:49', '2025-11-17 22:42:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `livros`
--

CREATE TABLE `livros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `autor_id` int(11) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `ano_publicacao` year(4) DEFAULT NULL,
  `editora` varchar(100) DEFAULT NULL,
  `numero_paginas` int(11) DEFAULT NULL,
  `quantidade_total` int(11) DEFAULT 1,
  `quantidade_disponivel` int(11) DEFAULT 1,
  `categoria` varchar(50) DEFAULT NULL,
  `capa_imagem` varchar(255) DEFAULT NULL,
  `localizacao` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `livros`
--

INSERT INTO `livros` (`id`, `titulo`, `autor_id`, `isbn`, `ano_publicacao`, `editora`, `numero_paginas`, `quantidade_total`, `quantidade_disponivel`, `categoria`, `capa_imagem`, `localizacao`, `created_at`, `updated_at`) VALUES
(2, 'Memórias Póstumas de Brás Cubas', 1, '978-8535911671', 2000, 'Companhia das Letras', 368, 2, 2, 'Romance', '49a5a1fdb296ededa5351b68ce74973d.jpg', 'Estante A1', '2025-11-06 13:35:07', '2025-11-11 23:46:58'),
(3, 'A Hora da Estrela', 2, '978-8520925683', 1977, 'Rocco', 88, 2, 0, 'Romance', 'dc0c3816e73d204872666a4a8960d740.jpg', 'Estante A2', '2025-11-06 13:35:07', '2025-11-17 22:42:49'),
(4, 'A Paixão Segundo G.H.', 2, '978-8532511010', 1964, 'Rocco', 176, 1, 1, 'Romance', '833818e12215e5852a88ba4c234b7896.jpg', 'Estante A2', '2025-11-06 13:35:07', '2025-11-11 23:41:22'),
(5, 'Capitães da Areia', 3, '978-8535914063', 1937, 'Companhia das Letras', 280, 4, 0, 'Romance', 'b9cfcbb0f554ba7fcc51814fa9a71a06.png', 'Estante A3', '2025-11-06 13:35:07', '2025-11-17 22:41:52'),
(6, 'Gabriela, Cravo e Canela', 3, '978-8535911046', 1958, 'Companhia das Letras', 424, 2, 0, 'Romance', '18a4b3a3f4b941f1c249871a1a12a057.jpg', 'Estante A3', '2025-11-06 13:35:07', '2025-11-11 22:50:47'),
(7, 'Harry Potter e a Pedra Filosofal', 4, '978-8532530787', 1997, 'Rocco', 264, 5, 4, 'Fantasia', 'e1a9feec7414d3db21795d9fd7624d94.jpg', 'Estante B1', '2025-11-06 13:35:07', '2025-11-11 22:28:35'),
(9, '1984', 5, '978-8535914849', 1949, 'Companhia das Letras', 416, 4, 3, 'Ficção', '1389a15edd2d2a401c703c86d5c20ee7.jpg', 'Estante B2', '2025-11-06 13:35:07', '2025-11-11 23:09:38'),
(10, 'A Revolução dos Bichos', 5, '978-8535909555', 1945, 'Companhia das Letras', 152, 3, 2, 'Ficção', '4ee8ef50ee292676a760b1215c492ae4.jpg', 'Estante B2', '2025-11-06 13:35:07', '2025-11-11 23:01:54'),
(11, 'Cem Anos de Solidão', 6, '978-8501061294', 1967, 'Record', 424, 2, 2, 'Romance', 'be64601abfdca667c717c6c4c0cd6de5.jpg', 'Estante C1', '2025-11-06 13:35:07', '2025-11-11 22:27:03'),
(12, 'Assassinato no Expresso do Oriente', 7, '978-8595084841', 1934, 'HarperCollins', 256, 3, 0, 'Mistério', '901aa5ddaf5f35b722b90ce2bafd49e7.jpg', 'Estante C2', '2025-11-06 13:35:07', '2025-11-12 17:25:14'),
(13, 'O Iluminado', 8, '978-8581050584', 1977, 'Suma', 464, 2, 1, 'Terror', 'af936f1660823aabe0be2724aa7433f7.jpg', 'Estante C3', '2025-11-06 13:35:07', '2025-11-11 23:37:48'),
(14, 'O Alquimista', 9, '978-8522008865', 1988, 'Rocco', 224, 5, 8, 'Ficção', 'ad784aaf69001850d55c1cdc79147486.jpg', 'Estante D1', '2025-11-06 13:35:07', '2025-11-11 23:51:37'),
(15, 'O Sítio do Picapau Amarelo', 10, '978-8525406293', 1920, 'Globo', 288, 4, 4, 'Infantil', '5a9ac21b9c3f1450f2554e27f25f703b.jpg', 'Estante D2', '2025-11-06 13:35:07', '2025-11-11 22:19:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` varchar(15) NOT NULL DEFAULT 'cliente',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `senha_hash`, `perfil`, `ativo`, `criado_em`) VALUES
(1, 'Administrador Inicial', 'admin@biblioteca.com', '$2y$10$nqGDtZ5kece7s5nuoFie.OA.ShH/2WNSEsyAMhqA7bmAx2gRbY7A2', 'admin', 1, '2025-11-11 23:56:18'),
(2, 'Aldrey Kich', 'aldrey.kich@gmail.com', '$2y$10$71lXcijr6whvVicmL7deROctJ3PYNs8RyVnYCBiXr86SlSj5hxHOu', 'admin', 1, '2025-11-12 13:44:43'),
(3, 'bibliotecario', 'bibliotecario@biblioteca.com.br', '$2y$10$ldbqL0CjitT67Zk4.DRWYePMASugZ1WxeXzdc8pxIgfW.k3yCZoDy', 'bibliotecario', 1, '2025-11-12 16:43:48');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_nome` (`nome`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_livro` (`livro_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_emprestimo` (`data_emprestimo`),
  ADD KEY `idx_data_devolucao` (`data_devolucao_prevista`);

--
-- Índices de tabela `livros`
--
ALTER TABLE `livros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `idx_titulo` (`titulo`),
  ADD KEY `idx_autor` (`autor_id`),
  ADD KEY `idx_isbn` (`isbn`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de tabela `livros`
--
ALTER TABLE `livros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD CONSTRAINT `emprestimos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `emprestimos_ibfk_2` FOREIGN KEY (`livro_id`) REFERENCES `livros` (`id`);

--
-- Restrições para tabelas `livros`
--
ALTER TABLE `livros`
  ADD CONSTRAINT `livros_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
