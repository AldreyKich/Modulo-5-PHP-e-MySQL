<?php
/**
 * Script de processamento para atualizar os dados de um usuário existente.
 * Recebe os dados via POST do formulário login_editar.php.
 */
 
// Inclui arquivos essenciais
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';

// 1. Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionarComMensagem('login_listar.php', 'erro', 'Método de requisição inválido.');
}

// 2. Coleta e valida o ID do usuário (vindo do campo hidden do formulário POST)
$id_usuario = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT);
$url_retorno_erro = "login_editar.php?id={$id_usuario}"; // URL de retorno em caso de erro

if (!$id_usuario) {
    redirecionarComMensagem('login_listar.php', 'erro', 'ID de usuário inválido ou ausente para atualização.');
}

// 3. Coleta e sanitiza os dados do formulário
$nome = filter_var(trim($_POST['nome'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$perfil = filter_var(trim($_POST['perfil'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$ativo = isset($_POST['ativo']) ? 1 : 0;
$nova_senha = trim($_POST['senha'] ?? '');

// Lista de perfis válidos para validação
$perfis_validos = ['admin', 'bibliotecario', 'membro'];

// 4. Validação dos dados
if (empty($nome) || empty($email) || empty($perfil)) {
    redirecionarComMensagem($url_retorno_erro, 'erro', 'Todos os campos obrigatórios (Nome, E-mail, Perfil) devem ser preenchidos.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionarComMensagem($url_retorno_erro, 'erro', 'O endereço de e-mail é inválido.');
}
if (!in_array($perfil, $perfis_validos)) {
    redirecionarComMensagem($url_retorno_erro, 'erro', 'Perfil selecionado é inválido.');
}

// Validação da nova senha, se fornecida
if (!empty($nova_senha) && strlen($nova_senha) < 6) {
    redirecionarComMensagem($url_retorno_erro, 'erro', 'A nova senha deve ter no mínimo 6 caracteres.');
}

// 5. Conexão com o Banco de Dados
try {
    $db_instance = Database::getInstance();
    $conexao = $db_instance->getConnection();
} catch (Exception $e) {
    redirecionarComMensagem('login_listar.php', 'erro', 'Erro de conexão com o banco de dados.');
}

// 6. Checar unicidade do E-mail (exceto o próprio usuário)
try {
    $stmt_check = $conexao->prepare("SELECT id_usuario FROM usuario WHERE email = :email AND id_usuario != :id");
    $stmt_check->bindParam(':email', $email);
    $stmt_check->bindParam(':id', $id_usuario);
    $stmt_check->execute();
    
    if ($stmt_check->fetchColumn()) {
        redirecionarComMensagem($url_retorno_erro, 'erro', 'O e-mail já está em uso por outro usuário.');
    }
} catch (PDOException $e) {
    redirecionarComMensagem($url_retorno_erro, 'erro', 'Erro ao verificar unicidade do e-mail: ' . $e->getMessage());
}

// 7. Montagem da Query SQL e Execução
$parametros = [
    ':id' => $id_usuario,
    ':nome' => $nome,
    ':email' => $email,
    ':perfil' => $perfil,
    ':ativo' => $ativo,
];

// Define os campos a serem atualizados, incluindo a senha apenas se uma nova for fornecida
$campos_sql = "nome = :nome, email = :email, perfil = :perfil, ativo = :ativo";

if (!empty($nova_senha)) {
    $hash_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
    // Use o nome real da sua coluna de senha (ex: 'senha_hash' ou 'senha')
    $campos_sql .= ", senha_hash = :senha_hash"; 
    $parametros[':senha_hash'] = $hash_senha;
}

$sql = "UPDATE usuario SET {$campos_sql} WHERE id_usuario = :id";

try {
    $stmt = $conexao->prepare($sql);
    
    // Bind dinâmico dos parâmetros
    foreach ($parametros as $key => $value) {
        $tipo = PDO::PARAM_STR;
        if ($key === ':id' || $key === ':ativo') {
            $tipo = PDO::PARAM_INT;
        }
        $stmt->bindValue($key, $value, $tipo);
    }
    
    $stmt->execute();
    
    // 8. Redirecionamento de Sucesso
    $mensagem = $stmt->rowCount() > 0 ? 'Usuário atualizado com sucesso!' : 'Nenhuma alteração foi feita no usuário.';
    redirecionarComMensagem('login_listar.php', 'sucesso', $mensagem);
    
} catch (PDOException $e) {
    // 9. Redirecionamento de Erro
    redirecionarComMensagem($url_retorno_erro, 'erro', 'Erro ao atualizar usuário no banco de dados: ' . $e->getMessage());
}