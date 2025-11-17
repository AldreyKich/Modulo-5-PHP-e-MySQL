<?php
// emprestimo_renovar.php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php'; // Adicionei funcoes.php para usar 'redirecionarComMensagem' se necessário, ou pelo menos 'formatarData'

// Definindo constantes que deveriam estar em config.php, mas são necessárias aqui
if (!defined('PRAZO_EMPRESTIMO_DIAS')) define('PRAZO_EMPRESTIMO_DIAS', 7);
if (!defined('MSG_SUCESSO')) define('MSG_SUCESSO', 'success');
if (!defined('MSG_ERRO')) define('MSG_ERRO', 'danger');


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
      'id'    => $emprestimo_id
    ]);

    $mensagem = "Empréstimo renovado! Nova data de devolução: " . date('d/m/Y', strtotime($nova_data));
        
        // Redireciona para a página de confirmação de renovação, passando o ID e a mensagem.
    header("Location: emprestimo_renovacao_confirmacao.php?id={$emprestimo_id}&msg=" . MSG_SUCESSO . "&detalhes=" . urlencode($mensagem));
    exit;

  } catch (Exception $e) {
    // Usa redirecionamento mais seguro para mensagens de erro
        redirecionarComMensagem(
            'emprestimos.php',
            MSG_ERRO,
            $e->getMessage()
        );
    exit;
  }

} else {
  header("Location: emprestimos.php");
  exit;
}
?>