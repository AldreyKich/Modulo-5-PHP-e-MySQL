<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Biblioteca</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        nav {
            background: #333;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background: #4CAF50;
            color: white;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .btn {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }

        .btn-danger {
            background: #f44336;
        }

        .btn-warning {
            background: #ff9800;
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Início</a>
        <a href="livros.php">Livros</a>
        <a href="clientes.php">Clientes</a>
        <a href="emprestimos.php">Empréstimos</a>
    </nav>

    <div class="container">
