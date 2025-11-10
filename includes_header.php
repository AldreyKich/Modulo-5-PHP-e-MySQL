<?php
/**
 * Header - Cabeçalho padrão do sistema
 * 
 * Este arquivo é incluído no topo de todas as páginas do sistema.
 * Contém o HTML inicial, CSS, menu de navegação e verificação de mensagens.
 * 
 * Para usar em uma página:
 * require_once 'includes/header.php';
 */

// Carrega as configurações e funções (se ainda não foram carregadas)
if (!defined('PRAZO_EMPRESTIMO_DIAS')) {
    require_once __DIR__ . '/../config/config.php';
}

if (!function_exists('verificarExibirMensagens')) {
    require_once __DIR__ . '/funcoes.php';
}

// Inicia sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gerenciamento de biblioteca">
    <meta name="author" content="Módulo 5 - Banco de Dados II">
    <title><?= NOME_BIBLIOTECA ?></title>
    
    <style>
        /* ========================================
           RESET E CONFIGURAÇÕES GLOBAIS
           ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            padding-bottom: 60px; /* Espaço para o footer fixo */
        }

        /* ========================================
           CONTAINER PRINCIPAL
           ======================================== */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            min-height: calc(100vh - 140px);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* ========================================
           NAVEGAÇÃO (MENU)
           ======================================== */
        nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 18px 25px;
            display: block;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        nav ul li a:hover {
            background-color: rgba(255,255,255,0.1);
            border-bottom-color: white;
        }

        nav ul li a.active {
            background-color: rgba(255,255,255,0.2);
            border-bottom-color: white;
        }

        /* ========================================
           TÍTULOS E TIPOGRAFIA
           ======================================== */
        h1 {
            color: #667eea;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            font-size: 28px;
        }

        h2 {
            color: #764ba2;
            margin: 25px 0 15px 0;
            font-size: 22px;
        }

        h3 {
            color: #555;
            margin: 20px 0 10px 0;
            font-size: 18px;
        }

        p {
            margin-bottom: 15px;
        }

        /* ========================================
           TABELAS
           ======================================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        table tbody tr:hover {
            background-color: #f8f9fa;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ========================================
           FORMULÁRIOS
           ======================================== */
        form {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group {
            margin-bottom: 20px;
        }

        /* ========================================
           BOTÕES
           ======================================== */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f44336 0%, #da190b 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #2196F3 0%, #0c7cd5 100%);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 12px;
        }

        /* ========================================
           BADGES (ETIQUETAS DE STATUS)
           ======================================== */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* ========================================
           ALERTAS E MENSAGENS
           ======================================== */
        .alert {
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        /* ========================================
           CARDS (CAIXAS DE INFORMAÇÃO)
           ======================================== */
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px 0;
        }

        .card-header {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        /* ========================================
           GRID SYSTEM
           ======================================== */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .col {
            flex: 1;
            padding: 0 10px;
            min-width: 250px;
        }

        /* ========================================
           UTILITÁRIOS
           ======================================== */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
            }

            nav ul li a {
                border-bottom: 1px solid rgba(255,255,255,0.1);
                border-left: 3px solid transparent;
            }

            nav ul li a:hover {
                border-left-color: white;
                border-bottom-color: rgba(255,255,255,0.1);
            }

            .container {
                padding: 10px;
            }

            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Menu de Navegação -->
    <nav>
        <ul>
            <li><a href="index.php">🏠 Início</a></li>
            <li><a href="livros.php">📚 Livros</a></li>
            <li><a href="clientes.php">👥 Clientes</a></li>
            <li><a href="emprestimos.php">📋 Empréstimos</a></li>
            <li><a href="autores.php">✍️ Autores</a></li>
            <li><a href="relatorios.php">📊 Relatórios</a></li>
        </ul>
    </nav>

    <!-- Container Principal -->
    <div class="container">
        <?php 
        // Verifica e exibe mensagens vindas de redirecionamentos
        verificarExibirMensagens(); 
        ?>
