<?php
/**
 * Processa a Devolução de Empréstimo
 * 
 * Registra a devolução do livro:
 * 1. Calcula se há atraso e multa
 * 2. Atualiza o status do empréstimo
 * 3. Devolve o livro ao estoque
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// ========================================
// VERIFICAR ID DO EMPRÉSTIMO
// ========================================
$emprestimo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($emprestimo_id <= 0) {
    redirecionarComMensagem(
        'emprestimos.php',
        MSG_ERRO,
        'ID de empréstimo inválido.'
    );
}

// ========================================
// PROCESSAR DEVOLUÇÃO
// ========================================
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // ========================================
    // INICIAR TRANSAÇÃO
    // ========================================
    $pdo->beginTransaction();
    
    // ========================================
    // BUSCAR DADOS DO EMPRÉSTIMO
    // ========================================
    $sql = "
        SELECT 
            e.*,
            l.titulo AS livro_titulo,
            c.nome AS cliente_nome
        FROM emprestimos e
        INNER JOIN livros l ON e.livro_id = l.id
        INNER JOIN clientes c ON e.cliente_id = c.id
        WHERE e.id = :id AND e.status = 'Ativo'
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $emprestimo_id]);
    $emprestimo = $stmt->fetch();
    
    // Verificar se o empréstimo existe e está ativo
    if (!$emprestimo) {
        throw new Exception(
            "Empréstimo não encontrado ou já foi devolvido."
        );
    }
    
    // ========================================
    // CALCULAR MULTA SE HOUVER ATRASO
    // ========================================
    $data_atual = date('Y-m-d');
    $dias_atraso = calcularDiasAtraso($emprestimo['data_devolucao_prevista']);
    $multa = calcularMulta($dias_atraso);
    
    // ========================================
    // ATUALIZAR O EMPRÉSTIMO
    // Marca como devolvido e registra a multa
    // ========================================
    $sql = "
        UPDATE emprestimos SET
            status = 'Devolvido',
            data_devolucao_real = :data_devolucao,
            multa = :multa
        WHERE id = :id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'data_devolucao' => $data_atual,
        'multa' => $multa,
        'id' => $emprestimo_id
    ]);
    
    // ========================================
    // DEVOLVER O LIVRO AO ESTOQUE
    // Adiciona 1 unidade à quantidade disponível
    // ========================================
    $sql = "
        UPDATE livros 
        SET quantidade_disponivel = quantidade_disponivel + 1 
        WHERE id = :livro_id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['livro_id' => $emprestimo['livro_id']]);
    
    // ========================================
    // CONFIRMAR TRANSAÇÃO
    // ========================================
    $pdo->commit();
    
    // ========================================
    // MONTAR MENSAGEM DE SUCESSO
    // ========================================
    $mensagem = sprintf(
        "✅ Devolução registrada com sucesso!<br><br>" .
        "<strong>Empréstimo:</strong> #%d<br>" .
        "<strong>Cliente:</strong> %s<br>" .
        "<strong>Livro:</strong> %s<br>" .
        "<strong>Data de Empréstimo:</strong> %s<br>" .
        "<strong>Data de Devolução Prevista:</strong> %s<br>" .
        "<strong>Data de Devolução Real:</strong> %s<br>",
        $emprestimo_id,
        $emprestimo['cliente_nome'],
        $emprestimo['livro_titulo'],
        formatarData($emprestimo['data_emprestimo']),
        formatarData($emprestimo['data_devolucao_prevista']),
        formatarData($data_atual)
    );
    
    // Adicionar informação sobre atraso/multa
    if ($dias_atraso > 0) {
        $mensagem .= sprintf(
            "<strong style='color: #f44336;'>⚠️ Atraso:</strong> %d dia(s)<br>" .
            "<strong style='color: #f44336;'>💰 Multa:</strong> %s<br>",
            $dias_atraso,
            formatarMoeda($multa)
        );
    } else {
        $mensagem .= "<strong style='color: #4CAF50;'>✓ Devolução no prazo!</strong> Sem multa.";
    }
    
    redirecionarComMensagem(
        'emprestimos.php',
        $dias_atraso > 0 ? MSG_AVISO : MSG_SUCESSO,
        $mensagem
    );
    
} catch (Exception $e) {
    // ========================================
    // ERRO - DESFAZER TRANSAÇÃO
    // ========================================
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    redirecionarComMensagem(
        'emprestimos.php',
        MSG_ERRO,
        $e->getMessage()
    );
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $mensagem_erro = "Erro ao processar devolução.";
    
    if (DEBUG_MODE) {
        $mensagem_erro .= " Detalhes: " . $e->getMessage();
    }
    
    redirecionarComMensagem(
        'emprestimos.php',
        MSG_ERRO,
        $mensagem_erro
    );
}
?>
