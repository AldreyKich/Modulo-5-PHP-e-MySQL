<?php
// emprestimo_renovar.php
require_once 'config/database.php';
require_once 'config/config.php';

$emprestimo_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($emprestimo_id > 0) {

    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // Buscar dados do empréstimo
        $sql = "SELECT * FROM emprestimos WHERE id = :id AND status = 'Ativo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $emprestimo_id]);
        $emprestimo = $stmt->fetch();

        if (!$emprestimo) {
            throw new Exception("Empréstimo não encontrado ou já foi devolvido");
        }

        // Verificar se já está atrasado
        if ($emprestimo['data_devolucao_prevista'] < date('Y-m-d')) {
            throw new Exception("Não é possível renovar empréstimo em atraso. Realize a devolução primeiro.");
        }

        // Renovar empréstimo (adicionar mais dias)
        $nova_data = date('Y-m-d', strtotime($emprestimo['data_devolucao_prevista'] . ' +' . PRAZO_EMPRESTIMO_DIAS . ' days'));

        $sql = "UPDATE emprestimos SET data_devolucao_prevista = :nova_data WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nova_data' => $nova_data,
            'id'        => $emprestimo_id
        ]);

        $mensagem = "Empréstimo renovado! Nova data de devolução: " . date('d/m/Y', strtotime($nova_data));

        header("Location: emprestimos.php?msg=Emprestimo Renovado!&detalhes=" . urlencode($mensagem));
        exit;

    } catch (Exception $e) {
        header("Location: emprestimos.php?erro=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    header("Location: emprestimos.php");
    exit;
}
?>
