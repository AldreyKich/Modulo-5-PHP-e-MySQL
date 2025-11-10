<?php
/**
 * Exclui um empréstimo
 * - Verifica existência
 * - Se empréstimo estiver "Ativo" (livro não devolvido), devolve ao estoque (quantidade_disponivel + 1)
 * - Exclui o registro na tabela emprestimos
 * - Redireciona com mensagem formatada
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// ID via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirecionarComMensagem('emprestimos.php', MSG_ERRO, 'ID do empréstimo não informado.');
}

$emprestimo_id = (int) $_GET['id'];
if ($emprestimo_id <= 0) {
    redirecionarComMensagem('emprestimos.php', MSG_ERRO, 'ID do empréstimo inválido.');
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Inicia transação
    $pdo->beginTransaction();

    // Buscar empréstimo (cliente + livro + status)
    $sql = "
        SELECT e.id, e.status, e.livro_id, e.cliente_id, e.data_emprestimo, e.data_devolucao_prevista, e.data_devolucao_real,
               c.nome AS cliente_nome, l.titulo AS livro_titulo
        FROM emprestimos e
        LEFT JOIN clientes c ON e.cliente_id = c.id
        LEFT JOIN livros l ON e.livro_id = l.id
        WHERE e.id = :id
        FOR UPDATE
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $emprestimo_id]);
    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emprestimo) {
        // não encontrado
        $pdo->rollBack();
        redirecionarComMensagem('emprestimos.php', MSG_ERRO, 'Empréstimo não encontrado.');
    }

    // Se o empréstimo estiver Ativo (ou seja, livro ainda não devolvido),
    // devolve o exemplar ao estoque (quantidade_disponivel + 1)
    if (strtolower($emprestimo['status']) === 'ativo' || $emprestimo['status'] === 'Ativo') {
        $sqlUp = "UPDATE livros SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = :livro_id";
        $stmtUp = $pdo->prepare($sqlUp);
        $stmtUp->execute(['livro_id' => $emprestimo['livro_id']]);
    }

    // Excluir empréstimo
    $sqlDel = "DELETE FROM emprestimos WHERE id = :id";
    $stmtDel = $pdo->prepare($sqlDel);
    $stmtDel->execute(['id' => $emprestimo_id]);

    $pdo->commit();

    // Montar mensagem formatada (HTML) para exibição
    $mensagem = sprintf(
        "✅ Empréstimo excluído com sucesso!<br><br>
         <strong>Empréstimo:</strong> #%d<br>
         <strong>Cliente:</strong> %s<br>
         <strong>Livro:</strong> %s",
        $emprestimo['id'],
        htmlspecialchars($emprestimo['cliente_nome'] ?? '—'),
        htmlspecialchars($emprestimo['livro_titulo'] ?? '—')
    );

    redirecionarComMensagem('emprestimos.php', MSG_SUCESSO, $mensagem);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $mensagemErro = "Erro ao excluir empréstimo.";
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $mensagemErro .= " Detalhes: " . $e->getMessage();
    }
    redirecionarComMensagem('emprestimos.php', MSG_ERRO, $mensagemErro);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirecionarComMensagem('emprestimos.php', MSG_ERRO, $e->getMessage());
}
