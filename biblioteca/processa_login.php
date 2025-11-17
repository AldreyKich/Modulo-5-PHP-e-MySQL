<?php
/**
 * Processa a Autenticação do Usuário.
 * * 1. Recebe e-mail e senha do formulário de login.
 * 2. Busca o usuário no banco de dados.
 * 3. Verifica a senha usando password_verify().
 * 4. Inicia a sessão se as credenciais estiverem corretas e o perfil for permitido.
 * 5. Redireciona o usuário.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0 (Lógica de Autenticação)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Inicia a sessão para gerenciamento de login e mensagens
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Constantes de perfis permitidos (Apenas admin e bibliotecário podem acessar a área de gestão)
$perfis_permitidos = ['admin', 'bibliotecario'];
$pagina_login = 'login.php';
$pagina_principal = 'index.php';

// ========================================
// 1. VERIFICAÇÃO DO MÉTODO E DADOS
// ========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$pagina_login}");
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    redirecionarComMensagem(
        $pagina_login,
        MSG_ERRO,
        'E-mail e senha são obrigatórios.'
    );
}

// ========================================
// 2. BUSCA DO USUÁRIO NO BANCO
// ========================================
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $sql = "SELECT id_usuario, nome, email, senha_hash, perfil, ativo FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Em caso de erro do banco de dados (ex: tabela inexistente)
    redirecionarComMensagem(
        $pagina_login,
        MSG_ERRO,
        'Erro ao tentar autenticar. Tente novamente mais tarde.'
    );
}

// ========================================
// 3. VERIFICAÇÃO DE CREDENCIAIS E STATUS
// ========================================

// 3.1. Usuário não encontrado
if (!$usuario) {
    redirecionarComMensagem(
        $pagina_login,
        MSG_ERRO,
        'Credenciais inválidas. Verifique seu e-mail e senha.'
    );
}

// 3.2. Verificação da Senha
// Usamos password_verify para comparar a senha fornecida com a hash salva
if (!password_verify($senha, $usuario['senha_hash'])) {
    redirecionarComMensagem(
        $pagina_login,
        MSG_ERRO,
        'Credenciais inválidas. Verifique seu e-mail e senha.'
    );
}

// 3.3. Verificação de Status
if ($usuario['ativo'] != 1) {
    redirecionarComMensagem(
        $pagina_login,
        MSG_ERRO,
        'Sua conta está inativa. Contate o administrador do sistema.'
    );
}

// 3.4. Verificação de Perfil (Apenas 'admin' ou 'bibliotecario' podem logar)
if (!in_array($usuario['perfil'], $perfis_permitidos)) {
    redirecionarComMensagem(
        $pagina_login,
        MSG_ERRO,
        'Seu perfil não tem permissão para acessar esta área.'
    );
}

// ========================================
// 4. SUCESSO E INÍCIO DA SESSÃO
// ========================================

// Define variáveis de sessão essenciais
$_SESSION['usuario_logado'] = true;
$_SESSION['user_id'] = $usuario['id_usuario'];
$_SESSION['user_nome'] = $usuario['nome'];
$_SESSION['user_perfil'] = $usuario['perfil'];

// 5. REDIRECIONAMENTO FINAL
redirecionarComMensagem(
    $pagina_principal,
    MSG_SUCESSO,
    "Bem-vindo(a), {$usuario['nome']}! Seu perfil é '{$usuario['perfil']}'."
);

?>