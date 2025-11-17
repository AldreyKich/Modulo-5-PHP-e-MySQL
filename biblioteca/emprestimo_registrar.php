<?php
/**
 * Processa o Registro de Novo Empréstimo
 * * Realiza validações e registra o empréstimo usando transação, e redireciona
 * para a página de confirmação/impressão.
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.1 (Com redirecionamento para Confirmação)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Define constantes que deveriam estar em config.php
if (!defined('LIMITE_EMPRESTIMOS_CLIENTE')) define('LIMITE_EMPRESTIMOS_CLIENTE', 3);
if (!defined('PRAZO_EMPRESTIMO_DIAS')) define('PRAZO_EMPRESTIMO_DIAS', 7);
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
if (!defined('MSG_SUCESSO')) define('MSG_SUCESSO', 'success');
if (!defined('MSG_ERRO')) define('MSG_ERRO', 'danger');


// ========================================
// VERIFICA SE É POST
// ========================================
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: emprestimo_novo.php");
    exit;
}

// ========================================
// RECEBE OS DADOS
// ========================================
$cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
$livro_id = isset($_POST['livro_id']) ? (int)$_POST['livro_id'] : 0;

// Validação básica
if ($cliente_id <= 0 || $livro_id <= 0) {
    redirecionarComMensagem(
        'emprestimo_novo.php',
        MSG_ERRO,
        'Dados inválidos. Selecione o cliente e o livro.'
    );
}

// ========================================
// PROCESSAR EMPRÉSTIMO
// ========================================
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // ========================================
    // INICIAR TRANSAÇÃO
    // ========================================
    $pdo->beginTransaction();
    
    // ========================================
    // VALIDAÇÃO 1: Verificar disponibilidade do livro
    // ========================================
    $sql = "SELECT titulo, quantidade_disponivel FROM livros WHERE id = :livro_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['livro_id' => $livro_id]);
    $livro = $stmt->fetch();
    
    if (!$livro) {
        throw new Exception("Livro não encontrado.");
    }
    
    if ($livro['quantidade_disponivel'] <= 0) {
        throw new Exception("O livro '{$livro['titulo']}' não está disponível no momento.");
    }
    
    // ========================================
    // VALIDAÇÃO 2: Verificar dados do cliente
    // ========================================
    $sql = "SELECT nome, status FROM clientes WHERE id = :cliente_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cliente_id' => $cliente_id]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        throw new Exception("Cliente não encontrado.");
    }
    
    if ($cliente['status'] != 'Ativo') {
        throw new Exception("Cliente '{$cliente['nome']}' não está ativo no sistema.");
    }
    
    // ========================================
    // VALIDAÇÃO 3: Verificar limite de empréstimos
    // ========================================
    $sql = "SELECT COUNT(*) FROM emprestimos WHERE cliente_id = :cliente_id AND status = 'Ativo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cliente_id' => $cliente_id]);
    $emprestimos_ativos = $stmt->fetchColumn();
    
    if ($emprestimos_ativos >= LIMITE_EMPRESTIMOS_CLIENTE) {
        throw new Exception(
            "Cliente '{$cliente['nome']}' já possui " . LIMITE_EMPRESTIMOS_CLIENTE . 
            " empréstimo(s) ativo(s). Limite máximo atingido."
        );
    }
    
    // ========================================
    // VALIDAÇÃO 4: Verificar empréstimos em atraso
    // ========================================
    $sql = "
        SELECT COUNT(*) 
        FROM emprestimos 
        WHERE cliente_id = :cliente_id 
        AND status = 'Ativo' 
        AND data_devolucao_prevista < CURDATE()
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cliente_id' => $cliente_id]);
    $emprestimos_atrasados = $stmt->fetchColumn();
    
    if ($emprestimos_atrasados > 0) {
        throw new Exception(
            "Cliente '{$cliente['nome']}' possui empréstimo(s) em atraso e está bloqueado " .
            "para novos empréstimos. Regularize as pendências primeiro."
        );
    }
    
    // ========================================
    // REGISTRAR O EMPRÉSTIMO
    // ========================================
    
    // Calcular datas
    $data_emprestimo = date('Y-m-d');
    $data_devolucao = calcularDataDevolucao($data_emprestimo);
    
    $sql = "
        INSERT INTO emprestimos (
            cliente_id, 
            livro_id, 
            data_emprestimo, 
            data_devolucao_prevista, 
            status
        ) VALUES (
            :cliente_id,
            :livro_id,
            :data_emprestimo,
            :data_devolucao,
            'Ativo'
        )
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'cliente_id' => $cliente_id,
        'livro_id' => $livro_id,
        'data_emprestimo' => $data_emprestimo,
        'data_devolucao' => $data_devolucao
    ]);
    
    // Pega o ID do empréstimo criado
    $emprestimo_id = $pdo->lastInsertId();
    
    // ========================================
    // ATUALIZAR ESTOQUE DO LIVRO
    // ========================================
    $sql = "
        UPDATE livros 
        SET quantidade_disponivel = quantidade_disponivel - 1 
        WHERE id = :livro_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['livro_id' => $livro_id]);
    
    // ========================================
    // CONFIRMAR TRANSAÇÃO
    // ========================================
    $pdo->commit();
    
    // ========================================
    // SUCESSO - REDIRECIONAR PARA CONFIRMAÇÃO
    // ========================================
    
    $mensagem = sprintf(
        "Empréstimo #%d registrado com sucesso! Devolução prevista: %s.",
        $emprestimo_id,
        formatarData($data_devolucao)
    );
    
    redirecionarComMensagem(
        "emprestimo_confirmacao.php?id={$emprestimo_id}",
        MSG_SUCESSO,
        $mensagem
    );
    
} catch (Exception $e) {
    // ========================================
    // ERRO - Desfaz TODAS as operações
    // ========================================
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    redirecionarComMensagem(
        'emprestimo_novo.php',
        MSG_ERRO,
        $e->getMessage()
    );
    
} catch (PDOException $e) {
    // ========================================
    // ERRO DO BANCO DE DADOS
    // ========================================
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $mensagem_erro = "Erro no banco de dados ao registrar empréstimo.";
    
    if (DEBUG_MODE) {
        $mensagem_erro .= " Detalhes: " . $e->getMessage();
    }
    
    redirecionarComMensagem(
        'emprestimo_novo.php',
        MSG_ERRO,
        $mensagem_erro
    );
}
?>