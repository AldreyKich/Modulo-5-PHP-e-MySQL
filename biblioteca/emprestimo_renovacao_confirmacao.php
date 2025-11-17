    <?php
/**
 * Página de Confirmação de Renovação de Empréstimo
 * * Exibe a mensagem de sucesso após a renovação e fornece o link para imprimir o comprovante em PDF.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$emprestimo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensagem_detalhe = isset($_GET['detalhes']) ? htmlspecialchars($_GET['detalhes']) : 'Empréstimo renovado com sucesso.';

if ($emprestimo_id <= 0) {
    exibirMensagem('erro', '❌ ID de empréstimo inválido ou não informado.');
    echo '<p><a href="emprestimos.php" class="btn btn-secondary">⬅️ Voltar para Empréstimos</a></p>';
    require_once 'includes/footer.php';
    exit;
}

?>

<h1 style="color: #4CAF50;">♻️ Empréstimo Renovado!</h1>

<div class="card p-4 shadow-sm" style="margin-top: 20px; text-align: center;">
    <p class="lead">O empréstimo **#<?= $emprestimo_id ?>** foi renovado com sucesso.</p>
    
    <?php if ($mensagem_detalhe): ?>
        <p class="h5" style="color: #38761D; font-weight: bold;"><?= $mensagem_detalhe ?></p>
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
        <!-- Botão para Imprimir Comprovante (Abre em nova aba, usa o mesmo comprovante_pdf.php) -->
        <a href="comprovante_pdf.php?id=<?= $emprestimo_id ?>" target="_blank" class="btn btn-success btn-lg" style="margin-right: 15px;">
            🖨️ Imprimir Novo Comprovante PDF
        </a>
        
        <!-- Botão para Voltar à Lista -->
        <a href="emprestimos.php" class="btn btn-secondary btn-lg">
            ⬅️ Voltar para a Lista de Empréstimos
        </a>
    </div>
    
    <p class="mt-4 text-muted"><small>O comprovante reflete a nova data de devolução e será aberto em uma nova janela/aba.</small></p>
</div>

<?php
require_once 'includes/footer.php';
?>