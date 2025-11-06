<?php
// emprestimo_registrar.php
require_once 'config/database.php';
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $cliente_id = (int) $_POST['cliente_id'];
    $livro_id = (int) $_POST['livro_id'];

    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // Iniciar transação
        $pdo->beginTransaction();

        // Verificar disponibilidade do livro
        $sql = "SELECT quantidade_disponivel, titulo FROM livros WHERE id = :livro_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['livro_id' => $livro_id]);
        $livro = $stmt->fetch();

        if (!$livro) {
            throw new Exception("Livro não encontrado");
        }

        if ($livro['quantidade_disponivel'] <= 0) {
            throw new Exception("Livro indisponível no momento");
        }

        // Verificar limite de empréstimos do cliente
        $sql = "SELECT COUNT(*) FROM emprestimos WHERE cliente_id = :cliente_id AND status = 'Ativo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cliente_id' => $cliente_id]);
        $emprestimos_ativos = $stmt->fetchColumn();

        if ($emprestimos_ativos >= 3) {
            throw new Exception("Cliente atingiu o limite de 3 empréstimos simultâneos");
        }

        // Verificar se cliente tem empréstimos em atraso
        $sql = "SELECT COUNT(*) FROM emprestimos
                WHERE cliente_id = :cliente_id
                AND status = 'Ativo'
                AND data_devolucao_prevista < CURDATE()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cliente_id' => $cliente_id]);
        $emprestimos_atrasados = $stmt->fetchColumn();

        if ($emprestimos_atrasados > 0) {
            throw new Exception("Cliente possui empréstimos em atraso e está bloqueado");
        }

        // Registrar empréstimo
        $data_emprestimo = date('Y-m-d');
        $data_devolucao = date('Y-m-d', strtotime('+' . PRAZO_EMPRESTIMO_DIAS . ' days'));

        $sql = "INSERT INTO emprestimos (cliente_id, livro_id, data_emprestimo, data_devolucao_prevista, status)
                VALUES (:cliente_id, :livro_id, :data_emp, :data_dev, 'Ativo')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'cliente_id' => $cliente_id,
            'livro_id'   => $livro_id,
            'data_emp'   => $data_emprestimo,
            'data_dev'   => $data_devolucao
        ]);

        $emprestimo_id = $pdo->lastInsertId();

        // Atualizar estoque do livro
        $sql = "UPDATE livros SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = :livro_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['livro_id' => $livro_id]);

        // Confirmar transação
        $pdo->commit();

        $mensagem = "Empréstimo #$emprestimo_id registrado com sucesso!";
        header("Location: emprestimos.php?msg=sucesso&detalhes=" . urlencode($mensagem));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: emprestimo_novo.php?erro=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    header("Location: emprestimo_novo.php");
    exit;
}
?>
