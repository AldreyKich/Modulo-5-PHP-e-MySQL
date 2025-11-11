<?php
/**
 * Exclusão de Livro
 * 
 * Permite excluir um livro do acervo da biblioteca, com confirmação
 * e tratamento de erros (como vínculos com empréstimos).
 * 
 * @author 
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    exibirMensagem('erro', '❌ ID do livro não informado.');
    exit;
}

$id = (int) $_GET['id'];

try {
    // Verificar se o livro existe
    $sql = "SELECT id, titulo FROM livros WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $livro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livro) {
        exibirMensagem('erro', '⚠️ Livro não encontrado.');
        echo '<a href="livros.php" class="btn btn-secondary">⬅️ Voltar</a>';
        exit;
    }

    // Verificar se há empréstimos vinculados a este livro (opcional)
    $sqlVerifica = "SELECT COUNT(*) FROM emprestimos WHERE livro_id = :id";
    $stmtVerifica = $pdo->prepare($sqlVerifica);
    $stmtVerifica->execute([':id' => $id]);
    $vinculos = $stmtVerifica->fetchColumn();

    if ($vinculos > 0) {
        exibirMensagem('aviso', '⚠️ Este livro não pode ser excluído pois possui empréstimos vinculados.');
        echo '<a href="livros.php" class="btn btn-secondary">⬅️ Voltar</a>';
        exit;
    }

    // Se confirmado via POST → realizar exclusão
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
        $sqlDel = "DELETE FROM livros WHERE id = :id";
        $stmtDel = $pdo->prepare($sqlDel);
        $stmtDel->execute([':id' => $id]);

        exibirMensagem('sucesso', '✅ Livro excluído com sucesso!');
        echo '<a href="livros.php" class="btn btn-success">📚 Voltar à Lista de Livros</a>';
        exit;
    }
?>

<h1>🗑️ Excluir Livro</h1>

<div class="alert alert-danger" style="margin-top: 20px;">
    <strong>Atenção!</strong> Esta ação é <u>irreversível</u>.<br><br>
    Deseja realmente excluir o livro:
    <br><br>
    <h3 style="color: #b71c1c;">"<?= htmlspecialchars($livro['titulo']) ?>"</h3>
</div>

<form method="POST" action="">
    <input type="hidden" name="confirmar" value="1">

    <button type="submit" class="btn btn-danger">🗑️ Sim, excluir definitivamente</button>
    <a href="livros.php" class="btn btn-secondary">❌ Cancelar</a>
</form>

<?php
} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao excluir livro: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>
