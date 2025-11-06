<?php
// emprestimo_devolver.php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

$emprestimo_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($emprestimo_id > 0) {

    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // Iniciar transação
        $pdo->beginTransaction();

        // Buscar dados do empréstimo
        $sql = "SELECT e.*, l.titulo, c.nome as cliente
                FROM emprestimos e
                INNER JOIN livros l ON e.livro_id = l.id
                INNER JOIN clientes c ON e.cliente_id = c.id
                WHERE e.id = :id AND e.status = 'Ativo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $emprestimo_id]);
        $emprestimo = $stmt->fetch();

        if (!$emprestimo) {
            throw new Exception("Empréstimo não encontrado ou já foi devolvido");
        }

        // Calcular multa se houver atraso
        $data_atual = date('Y-m-d');
        $dias_atraso = 0;
        $multa = 0;

        if ($data_atual > $emprestimo['data_devolucao_prevista']) {
            $dias_atraso = floor((strtotime($data_atual) - strtotime($emprestimo['data_devolucao_prevista'])) / (60 * 60 * 24));
            $multa = $dias_atraso * VALOR_MULTA_DIA;
        }

        // Atualizar empréstimo
        $sql = "UPDATE emprestimos
                SET status = 'Devolvido',
                    data_devolucao_real = :data_dev,
                    multa = :multa
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'data_dev' => $data_atual,
            'multa'    => $multa,
            'id'       => $emprestimo_id
        ]);

        // Atualizar estoque do livro
        $sql = "UPDATE livros SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = :livro_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['livro_id' => $emprestimo['livro_id']]);

        // Confirmar transação
        $pdo->commit();

        // Mensagem de retorno
        $mensagem = "Devolução registrada com sucesso!";
        if ($dias_atraso > 0) {
            $mensagem .= " Multa: " . formatarMoeda($multa) . " ($dias_atraso dias de atraso)";
        } else {
            $mensagem .= " Sem multa (devolução no prazo)";
        }

        header("Location: emprestimos.php?msg=devolvido&detalhes=" . urlencode($mensagem));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: emprestimos.php?erro=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    header("Location: emprestimos.php");
    exit;
}
?>
