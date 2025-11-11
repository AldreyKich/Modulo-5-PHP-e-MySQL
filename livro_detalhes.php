<?php
/**
 * Exibe os detalhes completos de um livro específico do acervo.
 * Inclui informações sobre o livro e seu respectivo autor.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// -------------------------------------------------------------------------
// 1. OBTENÇÃO E VALIDAÇÃO DO ID
// -------------------------------------------------------------------------

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    exibirMensagem('erro', '❌ ID do livro não informado ou inválido.');
    echo '<p><a href="livros.php" class="btn btn-secondary">⬅️ Voltar para Livros</a></p>';
    require_once 'includes/footer.php';
    exit;
}

// -------------------------------------------------------------------------
// 2. BUSCA DOS DADOS DO LIVRO (COM JOIN)
// -------------------------------------------------------------------------

try {
    $sql = "SELECT 
                l.*, 
                a.nome AS nome_autor, 
                a.nacionalidade AS nacionalidade_autor
            FROM 
                livros l
            JOIN 
                autores a ON l.autor_id = a.id
            WHERE 
                l.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $livro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livro) {
        exibirMensagem('aviso', '⚠️ Livro não encontrado no acervo.');
        echo '<p><a href="livros.php" class="btn btn-secondary">⬅️ Voltar para Livros</a></p>';
        require_once 'includes/footer.php';
        exit;
    }
    
    // -------------------------------------------------------------------------
    // 3. EXIBIÇÃO DOS DETALHES
    // -------------------------------------------------------------------------
?>

<h1 style="border-bottom: 2px solid #ccc; padding-bottom: 10px;">
    📚 Detalhes do Livro: <?= htmlspecialchars($livro['titulo']) ?>
</h1>

<!-- Informações Básicas -->
<div class="card" style="margin-bottom: 20px;">
    <h3>📖 Informações Principais</h3>
    <dl class="details-list">
        <dt>Título:</dt>
        <dd><?= htmlspecialchars($livro['titulo']) ?></dd>
        
        <dt>Autor:</dt>
        <dd>
            <a href="autor_detalhes.php?id=<?= $livro['autor_id'] ?>">
                <?= htmlspecialchars($livro['nome_autor']) ?> 
            </a>
            <?php if ($livro['nacionalidade_autor']): ?>
                (<?= htmlspecialchars($livro['nacionalidade_autor']) ?>)
            <?php endif; ?>
        </dd>
        
        <dt>ISBN:</dt>
        <dd><?= !empty($livro['isbn']) ? htmlspecialchars($livro['isbn']) : 'N/A' ?></dd>
        
        <dt>Ano de Publicação:</dt>
        <dd><?= !empty($livro['ano_publicacao']) ? htmlspecialchars($livro['ano_publicacao']) : 'N/A' ?></dd>
        
        <dt>Editora:</dt>
        <dd><?= !empty($livro['editora']) ? htmlspecialchars($livro['editora']) : 'N/A' ?></dd>
        
        <dt>Número de Páginas:</dt>
        <dd><?= !empty($livro['numero_paginas']) ? htmlspecialchars($livro['numero_paginas']) . ' pág.' : 'N/A' ?></dd>
    </dl>
</div>

<!-- Informações de Acervo e Localização -->
<div class="card" style="margin-bottom: 20px;">
    <h3>🏷️ Acervo e Localização</h3>
    <dl class="details-list">
        <dt>Categoria/Gênero:</dt>
        <dd><?= !empty($livro['categoria']) ? htmlspecialchars($livro['categoria']) : 'Não Classificado' ?></dd>
        
        <dt>Localização:</dt>
        <dd><?= !empty($livro['localizacao']) ? htmlspecialchars($livro['localizacao']) : 'Não Informada' ?></dd>
    </dl>
</div>

<!-- Informações de Estoque -->
<div class="card" style="margin-bottom: 30px;">
    <h3>📊 Quantidade em Estoque</h3>
    <dl class="details-list">
        <dt>Total de Exemplares:</dt>
        <dd><?= htmlspecialchars($livro['quantidade_total']) ?></dd>
        
        <dt>Disponível para Empréstimo:</dt>
        <dd style="font-weight: bold; color: <?= ($livro['quantidade_disponivel'] > 0) ? '#1E88E5' : '#D32F2F' ?>;">
            <?= htmlspecialchars($livro['quantidade_disponivel']) ?>
        </dd>
        
        <dt>Emprestados:</dt>
        <dd><?= htmlspecialchars($livro['quantidade_total'] - $livro['quantidade_disponivel']) ?></dd>
    </dl>
</div>

<!-- Ações -->
<div class="actions">
    <a href="livro_editar.php?id=<?= $livro['id'] ?>" class="btn btn-primary">
        ✏️ Editar Livro
    </a>
    <a href="livro_excluir.php?id=<?= $livro['id'] ?>" class="btn btn-danger">
        🗑️ Excluir Livro
    </a>
    <a href="livros.php" class="btn btn-secondary">
        ⬅️ Voltar para a Lista
    </a>
</div>

<!-- Estilização simples para detalhes -->
<style>
.details-list {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 10px 15px;
    margin: 20px 0;
}
.details-list dt {
    font-weight: bold;
    color: #555;
    grid-column: 1 / 2;
    text-align: right;
}
.details-list dd {
    margin: 0;
    grid-column: 2 / 3;
    word-break: break-word;
}
.card h3 {
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
    margin-bottom: 15px;
    color: #444;
}
</style>

<?php
} catch (PDOException $e) {
    // Trata erro de banco de dados
    exibirMensagem('erro', '❌ Erro ao buscar detalhes do livro: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>