<?php
/**
 * Página de Confirmação de Devolução de Empréstimo
 * * Exibe a mensagem de sucesso e o link para imprimir o comprovante de devolução.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$emprestimo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$multa = isset($_GET['multa']) ? (float)$_GET['multa'] : 0.00;
$tipo_mensagem = isset($_GET['msg']) ? $_GET['msg'] : 'success';

if ($emprestimo_id <= 0) {
    exibirMensagem('danger', '❌ ID de empréstimo inválido ou não informado.');
    echo '<p><a href="emprestimos.php" class="btn btn-secondary">⬅️ Voltar para Empréstimos</a></p>';
    require_once 'includes/footer.php';
    exit;
}

$titulo_multa = $multa > 0 ? '⚠️ Devolução com Atraso' : '✅ Devolução Registrada';
$cor_titulo = $multa > 0 ? '#FF8C00' : '#4CAF50';
$multa_formatada = formatarMoeda($multa);

?>

<h1 style="color: <?= $cor_titulo ?>;"><?= $titulo_multa ?></h1>

<div class="card p-4 shadow-sm" style="margin-top: 20px; text-align: center;">
    <p class="lead">A devolução do empréstimo **#<?= $emprestimo_id ?>** foi processada com sucesso.</p>
    
    <?php if ($multa > 0): ?>
        <div class="alert alert-danger h4" role="alert" style="font-weight: bold;">
            💰 Multa Cobrada: <?= $multa_formatada ?>
        </div>
        <p class="text-muted">O comprovante abaixo incluirá o registro desta multa e o pagamento efetuado.</p>
    <?php else: ?>
        <div class="alert alert-success h4" role="alert" style="font-weight: bold;">
            Devolução realizada dentro do prazo.
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
        <!-- Botão para Imprimir Comprovante (Passa o parâmetro 'tipo=devolucao' para mudar o layout do PDF) -->
        <a href="comprovante_pdf.php?id=<?= $emprestimo_id ?>&tipo=devolucao" target="_blank" class="btn btn-primary btn-lg" style="margin-right: 15px;">
            🖨️ Imprimir Comprovante de Devolução
        </a>
        
        <!-- Botão para Voltar à Lista -->
        <a href="emprestimos.php" class="btn btn-secondary btn-lg">
            ⬅️ Voltar para a Lista de Empréstimos
        </a>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>