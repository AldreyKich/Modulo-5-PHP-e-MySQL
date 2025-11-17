<?php
/**
 * Script de processamento para excluir um autor do banco de dados.
 * A exclusão é feita via ID passado na URL (GET).
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários, incluindo a classe Database
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// A exclusão deve ser feita através de um link, que é processado via GET.
// Verifica se o ID foi passado na URL
$autor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($autor_id <= 0) {
    // Redireciona se o ID for inválido ou ausente
    header("Location: erro.php?msg=ID de autor inválido para exclusão.");
    exit();
}

// -------------------------------------------------------------------------
// 1. OBTENÇÃO DA CONEXÃO
// -------------------------------------------------------------------------

try {
    // Obtém a instância da conexão PDO do padrão Singleton
    $conn = Database::getInstance()->getConnection();
} catch (Exception $e) {
    error_log("Erro de Conexão: " . $e->getMessage());
    header("Location: erro.php?msg=Falha ao conectar ao banco de dados.");
    exit();
}

// -------------------------------------------------------------------------
// 2. VERIFICAÇÃO DE DEPENDÊNCIAS (LIVROS)
// -------------------------------------------------------------------------
try {
    // Conta quantos livros estão associados a este autor
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM livros WHERE autor_id = :id");
    $stmt_check->bindParam(':id', $autor_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $livros_associados = $stmt_check->fetchColumn();

    if ($livros_associados > 0) {
        // Se houver livros, impede a exclusão e redireciona com erro
        $mensagem_erro = "❌ Não foi possível excluir o autor. Existem $livros_associados livro(s) associado(s). Remova ou reassocie-os primeiro.";
        header("Location: autores.php?msg=" . urlencode($mensagem_erro));
        exit();
    }

    // -------------------------------------------------------------------------
    // 3. EXECUÇÃO DA EXCLUSÃO (DELETE)
    // -------------------------------------------------------------------------

    $conn->beginTransaction();

    $sql = "DELETE FROM autores WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $autor_id, PDO::PARAM_INT);
    $stmt->execute();

    $conn->commit();

    // -------------------------------------------------------------------------
    // 4. REDIRECIONAMENTO DE SUCESSO
    // -------------------------------------------------------------------------
    
    $mensagem_sucesso = "🗑️ Autor excluído com sucesso!";
    header("Location: autores.php?msg=" . urlencode($mensagem_sucesso));
    exit();

} catch (PDOException $e) {
    // Em caso de erro, desfaz as alterações (se houver)
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Loga o erro detalhado
    error_log("Erro durante a exclusão do autor ID $autor_id: " . $e->getMessage());

    // Redireciona com mensagem de erro amigável
    $mensagem_erro = "❌ Erro ao tentar excluir o autor. Tente novamente.";
    header("Location: erro.php?msg=" . urlencode($mensagem_erro));
    exit();
}
?>