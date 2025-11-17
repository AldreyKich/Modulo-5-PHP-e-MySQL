<?php
/**
 * Processamento de Cadastro de Novo Usuário
 * Recebe dados do formulário e insere um novo registro na tabela 'usuario'.
 */

// Inclui arquivos essenciais usando caminhos absolutos com __DIR__
require_once __DIR__ . '/config/config.php';
// Inclui o arquivo que contém a classe Database
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';

// Redirecionamento padrão em caso de erro
$url_retorno = 'login_cadastrar.php';

// =========================================================================
// CORREÇÃO ESSENCIAL: OBTENDO A CONEXÃO PDO A PARTIR DA CLASSE DATABASE
// Esta etapa é crucial para definir a variável $conexao.
// =========================================================================
try {
    // 1. Obtém a instância única da classe Database (padrão Singleton)
    $db_instance = Database::getInstance();
    // 2. Obtém o objeto PDO (conexão ativa)
    $conexao = $db_instance->getConnection();
} catch (Exception $e) {
    // Em caso de falha na conexão/inicialização
    redirecionarComMensagem($url_retorno, 'erro', 'Erro de conexão com o banco de dados: ' . $e->getMessage());
}
// =========================================================================


// 1. Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecionarComMensagem($url_retorno, 'erro', 'Método de requisição inválido.');
}

// 2. Sanitiza e coleta dados
$nome = limparInput($_POST['nome'] ?? '');
$email = limparInput($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? ''; // Senha não é sanitizada para hashing
$perfil = limparInput($_POST['perfil'] ?? '');
// O checkbox 'ativo' só existe no POST se for marcado. Se não vier, é 0.
$ativo = isset($_POST['ativo']) ? 1 : 0; 

// 3. Validação básica
if (empty($nome) || empty($email) || empty($senha) || empty($perfil)) {
    redirecionarComMensagem($url_retorno, 'erro', 'Todos os campos obrigatórios devem ser preenchidos.');
}

if (!validarEmail($email)) {
    redirecionarComMensagem($url_retorno, 'erro', 'O e-mail fornecido é inválido.');
}

if (strlen($senha) < 8) {
    redirecionarComMensagem($url_retorno, 'erro', 'A senha deve ter no mínimo 8 caracteres.');
}

// Lista de perfis válidos (garantir que não insere um perfil arbitrário)
$perfis_validos = ['admin', 'bibliotecario', 'membro'];
if (!in_array($perfil, $perfis_validos)) {
    redirecionarComMensagem($url_retorno, 'erro', 'Perfil de usuário inválido.');
}

// 4. Checar unicidade do E-mail
try {
    $stmt = $conexao->prepare("SELECT COUNT(*) FROM usuario WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        redirecionarComMensagem($url_retorno, 'erro', 'O e-mail já está cadastrado no sistema.');
    }
} catch (PDOException $e) {
    // Em caso de erro do banco de dados na checagem
    redirecionarComMensagem($url_retorno, 'erro', 'Erro ao verificar e-mail: ' . $e->getMessage());
}

// 5. Hashing da Senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// 6. Preparar e executar a inserção
$sql = "INSERT INTO usuario (nome, email, senha_hash, perfil, ativo, criado_em) 
        VALUES (:nome, :email, :senha_hash, :perfil, :ativo, NOW())";

try {
    $stmt = $conexao->prepare($sql);
    
    // Bind dos parâmetros
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha_hash', $senha_hash);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    
    $stmt->execute();
    
    // Sucesso: redireciona para a lista de usuários (ou para o index)
    redirecionarComMensagem('index.php', 'sucesso', "Usuário '{$nome}' cadastrado com sucesso!");

} catch (PDOException $e) {
    // Erro na inserção
    redirecionarComMensagem($url_retorno, 'erro', 'Erro ao salvar o usuário: ' . $e->getMessage());
}

?>