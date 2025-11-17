<?php
/**
 * Processamento de Exclusão de Usuário
 * Exclui um registro da tabela 'usuario' com base no ID fornecido.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';

$url_retorno = 'login_listar.php'; // Retorna sempre para a listagem

// 1. Verifica autenticação e permissão
//if (!sessaoAtiva() || $_SESSION['perfil'] !== 'admin') {
//    redirecionarComMensagem('login.php', 'erro', 'Acesso negado. Apenas administradores podem excluir usuários.');
//}

// 2. Coleta o ID do usuário (pode vir via GET ou POST)
$id_usuario = filter_var($_REQUEST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id_usuario) {
    redirecionarComMensagem($url_retorno, 'erro', 'ID de usuário inválido ou ausente para exclusão.');
}

// 3. Impede que o próprio administrador logado se exclua
if ($_SESSION['id_usuario'] == $id_usuario) {
    redirecionarComMensagem($url_retorno, 'erro', 'Você não pode excluir sua própria conta enquanto estiver logado.');
}

// 4. Obtém a conexão PDO
try {
    $db_instance = Database::getInstance();
    $conexao = $db_instance->getConnection();
} catch (Exception $e) {
    redirecionarComMensagem($url_retorno, 'erro', 'Erro de conexão com o banco de dados.');
}

// 5. Executar a exclusão
try {
    $sql = "DELETE FROM usuario WHERE id_usuario = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        redirecionarComMensagem($url_retorno, 'sucesso', "Usuário ID {$id_usuario} excluído com sucesso.");
    } else {
        redirecionarComMensagem($url_retorno, 'aviso', "Usuário ID {$id_usuario} não foi encontrado para exclusão.");
    }

} catch (PDOException $e) {
    // Erro de integridade referencial (se o usuário tiver registros associados em outras tabelas)
    if ($e->getCode() == '23000') {
        redirecionarComMensagem($url_retorno, 'erro', 'Erro de exclusão: Este usuário possui registros vinculados em outras partes do sistema (ex: Empréstimos) e não pode ser excluído diretamente.');
    } else {
        redirecionarComMensagem($url_retorno, 'erro', 'Erro ao excluir o usuário: ' . $e->getMessage());
    }
}
?>