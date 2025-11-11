<?php
/**
 * Exibe os detalhes completos de um autor específico, incluindo a lista
 * de todos os livros associados a ele no acervo.
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
    exibirMensagem('erro', '❌ ID do autor não informado ou inválido.');
    echo '<p><a href="autores.php" class="btn btn-secondary">⬅️ Voltar para Autores</a></p>';
    require_once 'includes/footer.php';
    exit;
}

// -------------------------------------------------------------------------
// 2. BUSCA DOS DADOS DO AUTOR
// -------------------------------------------------------------------------

try {
    // Busca dados básicos do autor
    $sqlAutor = "SELECT * FROM autores WHERE id = :id";
    $stmtAutor = $pdo->prepare($sqlAutor);
    $stmtAutor->execute(['id' => $id]);
    $autor = $stmtAutor->fetch(PDO::FETCH_ASSOC);

    if (!$autor) {
        exibirMensagem('aviso', '⚠️ Autor não encontrado no cadastro.');
        echo '<p><a href="autores.php" class="btn btn-secondary">⬅️ Voltar para Autores</a></p>';
        require_once 'includes/footer.php';
        exit;
    }

    // Busca todos os livros escritos por este autor
    $sqlLivros = "SELECT * FROM livros WHERE autor_id = :id ORDER BY ano_publicacao DESC, titulo ASC";
    $stmtLivros = $pdo->prepare($sqlLivros);
    $stmtLivros->execute(['id' => $id]);
    $livros = $stmtLivros->fetchAll(PDO::FETCH_ASSOC);

    $totalLivros = count($livros);

    // -------------------------------------------------------------------------
    // 3. EXIBIÇÃO DOS DETALHES
    // -------------------------------------------------------------------------
?>

<h1 style="border-bottom: 2px solid #ddd; padding-bottom: 10px;">
    👤 Detalhes do Autor: <?= htmlspecialchars($autor['nome']) ?>
</h1>

<!-- Informações Básicas do Autor -->
<div class="card" style="margin-bottom: 20px;">
    <h3>Informações Pessoais</h3>
    <dl class="details-list">
        <dt>Nome Completo:</dt>
        <dd style="font-size: 1.1em; font-weight: bold;"><?= htmlspecialchars($autor['nome']) ?></dd>

        <dt>Nacionalidade:</dt>
        <dd><?= !empty($autor['nacionalidade']) ? htmlspecialchars($autor['nacionalidade']) : 'Não Informada' ?></dd>

        <dt>Data de Nascimento:</dt>
        <dd>
            <?php 
                if (!empty($autor['data_nascimento']) && $autor['data_nascimento'] != '0000-00-00') {
                    echo formatarData($autor['data_nascimento']);
                } else {
                    echo 'Não Informada';
                }
            ?>
        </dd>

        <dt>Mini Biografia/Notas:</dt>
        <dd>
            <p style="white-space: pre-wrap; margin-top: 5px;">
                <?= !empty($autor['biografia']) ? htmlspecialchars($autor['biografia']) : 'Sem notas ou biografia cadastrada.' ?>
            </p>
        </dd>
    </dl>
</div>

<!-- Ações do Autor -->
<div class="actions" style="margin-bottom: 30px;">
    <a href="autor_editar.php?id=<?= $autor['id'] ?>" class="btn btn-primary">
        ✏️ Editar Autor
    </a>
    <?php if ($totalLivros == 0): ?>
        <a href="autor_excluir.php?id=<?= $autor['id'] ?>" class="btn btn-danger">
            🗑️ Excluir Autor
        </a>
    <?php else: ?>
        <button class="btn btn-danger" disabled title="Exclua os <?= $totalLivros ?> livros deste autor primeiro." style="opacity: 0.7;">
            🗑️ Excluir Autor (<?= $totalLivros ?> livros)
        </button>
    <?php endif; ?>
    <a href="autores.php" class="btn btn-secondary">
        ⬅️ Voltar para a Lista
    </a>
</div>


<!-- Livros do Autor -->
<div class="card">
    <h3>📚 Livros Publicados (<?= $totalLivros ?>)</h3>
    <?php if ($totalLivros > 0): ?>
        <table class="table-striped">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Ano</th>
                    <th>Categoria</th>
                    <th>Estoque (Disp/Total)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livros as $livro): ?>
                <tr>
                    <td>
                        <a href="livro_detalhes.php?id=<?= $livro['id'] ?>" style="font-weight: bold; color: #1565C0;">
                            <?= htmlspecialchars($livro['titulo']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($livro['ano_publicacao']) ?? 'N/A' ?></td>
                    <td><?= htmlspecialchars($livro['categoria']) ?? 'N/A' ?></td>
                    <td>
                        <span style="color: <?= ($livro['quantidade_disponivel'] > 0) ? 'green' : 'red' ?>; font-weight: bold;">
                            <?= $livro['quantidade_disponivel'] ?>
                        </span>
                        / <?= $livro['quantidade_total'] ?>
                    </td>
                    <td>
                        <a href="livro_editar.php?id=<?= $livro['id'] ?>" class="btn btn-warning btn-small">Editar</a>
                        <a href="livro_excluir.php?id=<?= $livro['id'] ?>" class="btn btn-danger btn-small">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            Nenhum livro cadastrado para este autor. Você pode <a href="livro_novo.php" style="color: #0c5460; text-decoration: underline;">adicionar um novo livro</a> agora.
        </div>
    <?php endif; ?>
</div>

<!-- Estilização simples para detalhes (replicando o padrão de livro_detalhes.php) -->
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
.table-striped {
    width: 100%;
    border-collapse: collapse;
}
.table-striped th, .table-striped td {
    padding: 12px 15px;
    border: 1px solid #eee;
    text-align: left;
}
.table-striped th {
    background-color: #f8f8f8;
    color: #333;
}
.table-striped tbody tr:nth-child(odd) {
    background-color: #fdfdfd;
}
.btn-small {
    padding: 5px 10px;
    font-size: 0.85em;
    margin-right: 5px;
}
</style>

<?php
} catch (PDOException $e) {
    // Trata erro de banco de dados
    exibirMensagem('erro', '❌ Erro ao buscar detalhes do autor: ' . $e->getMessage());
}

require_once 'includes/footer.php';

/**
 * Função auxiliar para formatar a data (simulada, pois o arquivo funcoes.php não foi fornecido)
 * Se não for definida em funcoes.php, esta será usada.
 * @param string $data Data no formato YYYY-MM-DD
 * @return string Data formatada como DD/MM/YYYY ou 'N/A'
 */
if (!function_exists('formatarData')) {
    function formatarData($data) {
        if (empty($data) || $data == '0000-00-00') {
            return 'N/A';
        }
        return date('d/m/Y', strtotime($data));
    }
}
?>