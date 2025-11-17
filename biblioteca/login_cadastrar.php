<?php
/**
 * Formulário de Cadastro de Novo Usuário (Acesso Administrativo)
 * * Este formulário é usado por um administrador ou bibliotecário para criar
 * uma nova conta de usuário/funcionário.
 */

require_once 'config/config.php';
require_once 'includes/funcoes.php';
// Adicionar autenticação: Checar se o usuário está logado e tem perfil de Admin/Bibliotecário
// if (!checkAuthAdmin()) { redirecionarComMensagem('login.php', 'erro', 'Acesso negado.'); }

// Obtém e exibe mensagens de feedback (sucesso/erro) da URL
verificarExibirMensagens();

// Lista de perfis válidos para o dropdown
$perfis_validos = ['admin', 'bibliotecario', 'membro'];

$titulo_pagina = "Cadastrar Novo Usuário";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> | Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Simulação de Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Biblioteca</a>
            <span class="navbar-text">
                <?= $titulo_pagina ?>
            </span>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><?= $titulo_pagina ?></h4>
            </div>
            <div class="card-body">
                <!-- O formulário envia os dados para o script de salvar -->
                <form action="login_salvar.php" method="POST">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha (Mín. 8 caracteres)</label>
                        <input type="password" class="form-control" id="senha" name="senha" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil de Acesso</label>
                        <select class="form-select" id="perfil" name="perfil" required>
                            <?php foreach ($perfis_validos as $perfil): ?>
                                <option value="<?= htmlspecialchars($perfil) ?>"><?= ucfirst($perfil) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" checked>
                        <label class="form-check-label" for="ativo">Ativo (Permitir login)</label>
                    </div>

                    <button type="submit" class="btn btn-success me-2">Salvar Usuário</button>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Inclui o JS do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>