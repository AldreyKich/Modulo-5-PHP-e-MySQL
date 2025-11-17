<?php
/**
 * Página de Confirmação de Empréstimo
 * * Exibe a mensagem de sucesso após o registro e fornece o link para imprimir o comprovante em PDF.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$emprestimo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($emprestimo_id <= 0) {
    exibirMensagem('erro', '❌ ID de empréstimo inválido ou não informado.');
    echo '<p><a href="emprestimos.php" class="btn btn-secondary">⬅️ Voltar para Empréstimos</a></p>';
    require_once 'includes/footer.php';
    exit;
}

?>

<h1 style="color: #4CAF50;">✅ Empréstimo Registrado com Sucesso!</h1>

<div class="card p-4 shadow-sm" style="margin-top: 20px; text-align: center;">
    <p class="lead">O registro do empréstimo **#<?= $emprestimo_id ?>** foi finalizado com êxito.</p>
    
    <div style="margin-top: 30px;">
        <!-- Botão para Imprimir Comprovante (Abre em nova aba) -->
        <a href="comprovante_pdf.php?id=<?= $emprestimo_id ?>" target="_blank" class="btn btn-success btn-lg" style="margin-right: 15px;">
            🖨️ Imprimir Comprovante PDF
        </a>
        
        <!-- Botão para Voltar à Lista -->
        <a href="emprestimos.php" class="btn btn-secondary btn-lg">
            ⬅️ Voltar para a Lista de Empréstimos
        </a>
    </div>
    
    <p class="mt-4 text-muted"><small>O comprovante será aberto em uma nova janela/aba.</small></p>
</div>

<?php
require_once 'includes/footer.php';
?>