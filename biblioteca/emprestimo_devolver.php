<?php
/**
 * Processa a Devolução de Empréstimo
 * * Registra a devolução do livro:
 * 1. Calcula se há atraso e multa
 * 2. Atualiza o status do empréstimo
 * 3. Devolve o livro ao estoque
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.1 (Redireciona para página de confirmação de devolução)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Definindo constantes de mensagem que são necessárias aqui
if (!defined('MSG_SUCESSO')) define('MSG_SUCESSO', 'success');
if (!defined('MSG_ERRO')) define('MSG_ERRO', 'danger');
if (!defined('MSG_AVISO')) define('MSG_AVISO', 'warning'); // Adicionado para atraso/multa

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
    // REDIRECIONAR PARA PÁGINA DE CONFIRMAÇÃO DE DEVOLUÇÃO
    // O PDF será gerado a partir desta nova página.
    // ========================================
    
    // Se há atraso, muda o tipo da mensagem para aviso (cor laranja/vermelha)
    $tipo_mensagem = $dias_atraso > 0 ? MSG_AVISO : MSG_SUCESSO;
    
    // Envia dados essenciais para a página de confirmação via URL
    header("Location: emprestimo_devolucao_confirmacao.php?id={$emprestimo_id}&multa={$multa}&msg={$tipo_mensagem}");
    exit;

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