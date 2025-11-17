<?php
/**
 * Formulário de Login para o Sistema de Biblioteca.
 * * Permite ao usuário (admin ou bibliotecário) acessar o sistema.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0 (Login Inicial)
 */

// Inclui funções e configurações
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Inicia a sessão para gerenciamento de login
// Garante que a sessão está iniciada para usar $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário já está logado e, se sim, redireciona para a página principal
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header('Location: index.php'); // Redireciona para a home
    exit;
}

// Obtém mensagem de erro/sucesso do redirecionamento, se houver
$mensagem = obterMensagem();

// Não carregamos o header/footer padrão, pois esta é uma página de acesso inicial
// E deve ter um layout minimalista e centralizado.
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biblioteca</title>
    <!-- Inclui o CSS do Bootstrap (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Fundo cinza claro */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
        }
        .card-header {
            background-color: #007bff; /* Azul primário */
            color: white;
            font-weight: bold;
            text-align: center;
            border-radius: .375rem .375rem 0 0;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card shadow">
        <div class="card-header">
            <h3>Acesso ao Sistema</h3>
        </div>
        <div class="card-body">
            
            <?php 
            // Exibe a mensagem de retorno (erro, sucesso, etc.)
// Exibe a mensagem de retorno (erro, sucesso, etc.)
      if ($mensagem):
        $tipo_alerta = ($mensagem['tipo'] === 'sucesso') ? 'alert-success' : 'alert-danger';
      ?>
        <div class="alert <?= $tipo_alerta ?> alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($mensagem['mensagem']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
            <form action="processa_login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
            
            <p class="mt-3 text-center text-muted"><small>Apenas usuários com perfil 'admin' ou 'bibliotecario' podem acessar.</small></p>
        </div>
    </div>
</div>

<!-- Inclui o JS do Bootstrap (CDN) para fechar o alerta -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>