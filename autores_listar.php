<?php
/**
 * Listagem de Autores
 * 
 * Exibe todos os autores cadastrados com suas obras
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    // ========================================
    // BUSCAR AUTORES COM CONTAGEM DE LIVROS
    // ========================================
    $sql = "
        SELECT 
            a.*,
            COUNT(l.id) AS total_livros
        FROM autores a
        LEFT JOIN livros l ON a.id = l.autor_id
        GROUP BY a.id
        ORDER BY a.nome
    ";
    
    $stmt = $pdo->query($sql);
    $autores = $stmt->fetchAll();

?>

    <h1>✍️ Autores Cadastrados</h1>

    <div style="margin-bottom: 25px;">
        <a href="autor_novo.php" class="btn btn-success">
            ➕ Cadastrar Novo Autor
        </a>
    </div>

    <?php if (count($autores) > 0): ?>
        
        <p style="color: #666; margin: 20px 0;">
            Total de <?= count($autores) ?> autor(es) cadastrado(s)
        </p>

        <!-- Grid de Autores -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
            <?php foreach ($autores as $autor): ?>
                <div class="card">
                    <h3 style="margin: 0 0 15px 0; color: #667eea;">
                        <?= htmlspecialchars($autor['nome']) ?>
                    </h3>
                    
                    <?php if ($autor['nacionalidade']): ?>
                        <p style="color: #666; margin: 5px 0;">
                            <strong>🌍 Nacionalidade:</strong> 
                            <?= htmlspecialchars($autor['nacionalidade']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($autor['data_nascimento']): ?>
                        <p style="color: #666; margin: 5px 0;">
                            <strong>📅 Nascimento:</strong> 
                            <?= formatarData($autor['data_nascimento']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <p style="margin: 10px 0;">
                        <span class="badge badge-info">
                            📚 <?= $autor['total_livros'] ?> livro(s) cadastrado(s)
                        </span>
                    </p>
                    
                    <?php if ($autor['biografia']): ?>
                        <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 15px 0;">
                            <strong style="display: block; margin-bottom: 8px;">Biografia:</strong>
                            <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6;">
                                <?= resumirTexto($autor['biografia'], 150) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Ações -->
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                        <a href="autor_livros.php?id=<?= $autor['id'] ?>" 
                           class="btn btn-info btn-small">
                            📚 Ver Livros
                        </a>
                        
                        <a href="autor_editar.php?id=<?= $autor['id'] ?>" 
                           class="btn btn-warning btn-small">
                            ✏️ Editar
                        </a>
                        
                        <?php if ($autor['total_livros'] == 0): ?>
                            <a href="autor_excluir.php?id=<?= $autor['id'] ?>" 
                               class="btn btn-danger btn-small confirm-delete">
                                🗑️ Excluir
                            </a>
                        <?php else: ?>
                            <button 
                                class="btn btn-danger btn-small" 
                                disabled 
                                title="Não é possível excluir autor com livros cadastrados"
                                style="opacity: 0.5; cursor: not-allowed;">
                                🗑️ Excluir
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhum autor cadastrado.</strong><br>
            Comece <a href="autor_novo.php" style="color: #0c5460; text-decoration: underline;">cadastrando um novo autor</a>.
        </div>
    <?php endif; ?>

<?php

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar autores: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>
