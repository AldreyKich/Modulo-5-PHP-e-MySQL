<?php
/**
 * Script de Logout e Encerramento de Sessão
 * * Encerra a sessão do usuário e o redireciona para a página de login.
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// 1. Incluir as funções e a configuração (necessário para usar redirecionarComMensagem)
// O funcoes.php irá iniciar a sessão internamente se necessário
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// **********************************************
// 2. Encerrar a Sessão
// **********************************************

// Destrói todas as variáveis registradas na sessão
$_SESSION = array();

// Se for preciso destruir o cookie de sessão, deve ser feito aqui
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

// **********************************************
// 3. Redirecionar
// **********************************************

// Define a mensagem e redireciona para a tela de login
redirecionarComMensagem('login.php', MSG_INFO, 'Sessão encerrada com sucesso. Faça login novamente para acessar.');