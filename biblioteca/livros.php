<?php
/**
 * Listagem de Livros
 * * Exibe o catálogo completo de livros com:
 * - Filtros por título, autor e categoria
 * - Paginação
 * - Indicação de disponibilidade
 * - Ações de editar, excluir e emprestar
 * - EXIBIÇÃO DA CAPA DO LIVRO (NOVO)
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.1 (Com Imagem de Capa)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// --- DEFINIÇÃO DO DIRETÓRIO DE CAPAS ---
// Certifique-se de que esta constante ou variável aponte para o local correto
// O caminho deve ser acessível via URL (Ex: /uploads/capas/livro.jpg)
define('DIRETORIO_CAPAS', 'uploads/capas/'); // <<-- MODIFICAR AQUI SE NECESSÁRIO

// ========================================
// PAGINAÇÃO
// ========================================
$por_pagina = REGISTROS_POR_PAGINA;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

// ========================================
// FILTROS
// ========================================
$filtro_busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';
$filtro_autor = isset($_GET['autor']) ? (int)$_GET['autor'] : 0;
$filtro_categoria = isset($_GET['categoria']) ? limparInput($_GET['categoria']) : '';
$filtro_disponivel = isset($_GET['disponivel']) ? (int)$_GET['disponivel'] : 0;

try {
    // ========================================
    // BUSCAR CATEGORIAS ÚNICAS
    // ========================================
    $sql = "SELECT DISTINCT categoria FROM livros WHERE categoria IS NOT NULL ORDER BY categoria";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // ========================================
    // BUSCAR AUTORES
    // ========================================
    $sql = "SELECT id, nome FROM autores ORDER BY nome";
    $stmt = $pdo->query($sql);
    $autores = $stmt->fetchAll();
    
    // ========================================
    // CONSTRUIR QUERY COM FILTROS
    // ========================================
    $where_clauses = [];
    $params = [];
    
    // Filtro por busca (título)
    if (!empty($filtro_busca)) {
        $where_clauses[] = "l.titulo LIKE :busca";
        $params['busca'] = "%$filtro_busca%";
    }
    
    // Filtro por autor
    if ($filtro_autor > 0) {
        $where_clauses[] = "l.autor_id = :autor_id";
        $params['autor_id'] = $filtro_autor;
    }
    
    // Filtro por categoria
    if (!empty($filtro_categoria)) {
        $where_clauses[] = "l.categoria = :categoria";
        $params['categoria'] = $filtro_categoria;
    }
    
    // Filtro por disponibilidade
    if ($filtro_disponivel == 1) {
        $where_clauses[] = "l.quantidade_disponivel > 0";
    }
    
    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";
    
    // ========================================
    // CONTAR REGISTROS
    // ========================================
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM livros l $where_sql");
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $por_pagina);
    
    // ========================================
    // BUSCAR LIVROS
    // ========================================
    $sql = "
        SELECT 
            l.*,
            a.nome AS autor_nome,
            a.nacionalidade AS autor_nacionalidade,
            (SELECT COUNT(*) FROM emprestimos WHERE livro_id = l.id) AS total_emprestimos,
            (SELECT COUNT(*) FROM emprestimos WHERE livro_id = l.id AND status = 'Ativo') AS emprestimos_ativos
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        $where_sql
        ORDER BY l.titulo
        LIMIT :limite OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $livros = $stmt->fetchAll();

?>

    <h1>📚 Catálogo de Livros</h1>

    <div style="margin-bottom: 25px;">
        <a href="livro_novo.php" class="btn btn-success">
            ➕ Cadastrar Novo Livro
        </a>
        <a href="autor_novo.php" class="btn btn-info">
            ✍️ Cadastrar Autor
        </a>
    </div>

    <div class="card">
        <h3>🔍 Filtros de Busca</h3>
        <form method="GET" action="livros.php" style="background: transparent; padding: 0;">
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="busca">Buscar por título:</label>
                        <input 
                            type="text" 
                            id="busca" 
                            name="busca" 
                            value="<?= htmlspecialchars($filtro_busca) ?>"
                            placeholder="Digite o título do livro..."
                        >
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="autor">Filtrar por autor:</label>
                        <select id="autor" name="autor">
                            <option value="0">Todos os autores</option>
                            <?php foreach ($autores as $autor): ?>
                                <option value="<?= $autor['id'] ?>" <?= $filtro_autor == $autor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($autor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="categoria">Filtrar por categoria:</label>
                        <select id="categoria" name="categoria">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $filtro_categoria == $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="disponivel">Disponibilidade:</label>
                        <select id="disponivel" name="disponivel">
                            <option value="0">Todos</option>
                            <option value="1" <?= $filtro_disponivel == 1 ? 'selected' : '' ?>>
                                Apenas disponíveis
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn">Filtrar</button>
            <a href="livros.php" class="btn btn-secondary">Limpar Filtros</a>
        </form>
    </div>

    <p style="color: #666; margin: 20px 0;">
        <?php if ($total_registros > 0): ?>
            Exibindo <?= count($livros) ?> de <?= $total_registros ?> livro(s)
            <?php if (!empty($filtro_busca)): ?>
                | Busca: <strong><?= htmlspecialchars($filtro_busca) ?></strong>
            <?php endif; ?>
        <?php else: ?>
            Nenhum livro encontrado
        <?php endif; ?>
    </p>

    <?php if (count($livros) > 0): ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <?php foreach ($livros as $livro): 
                $disponivel = $livro['quantidade_disponivel'] > 0;
                // --- Determina o caminho da imagem ou um placeholder ---
                $caminho_capa = !empty($livro['capa_imagem']) 
                    ? DIRETORIO_CAPAS . htmlspecialchars($livro['capa_imagem'])
                    : 'assets/img/placeholder_livro.png'; // <<-- CRIE UM IMAGEM PADRÃO
            ?>
                <div class="card" style="position: relative; overflow: hidden; display: flex; <?= !$disponivel ? 'opacity: 0.7;' : '' ?>">
                    
                    <div style="flex-shrink: 0; width: 100px; height: 150px; margin-right: 15px; border-radius: 4px; overflow: hidden;">
                        <img 
                            src="<?= $caminho_capa ?>" 
                            alt="Capa do livro: <?= htmlspecialchars($livro['titulo']) ?>" 
                            style="width: 100%; height: 100%; object-fit: cover; display: block;"
                        >
                    </div>
                    
                    <div style="flex-grow: 1;">
                        
                        <div style="position: absolute; top: 15px; right: 15px;">
                            <?php if ($disponivel): ?>
                                <span class="badge badge-success">
                                    ✓ Disponível
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    ✗ Indisponível
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 style="margin: 0 0 5px 0; padding-right: 100px; font-size: 1.2em;">
                            <?= htmlspecialchars($livro['titulo']) ?>
                        </h3>
                        
                        <p style="color: #666; margin: 0 0 5px 0; font-size: 0.9em;">
                            <strong>Autor:</strong> <?= htmlspecialchars($livro['autor_nome']) ?>
                        </p>
                        
                        <?php if ($livro['categoria']): ?>
                            <p style="margin: 5px 0;">
                                <span class="badge badge-info" style="font-size: 0.8em;">
                                    <?= htmlspecialchars($livro['categoria']) ?>
                                </span>
                            </p>
                        <?php endif; ?>
                        
                        <p style="color: #666; margin: 10px 0 5px 0; font-size: 0.9em;">
                            <strong>Disponíveis:</strong> 
                            <?= $livro['quantidade_disponivel'] ?> de <?= $livro['quantidade_total'] ?>
                        </p>
                        
                        <div style="margin-top: 10px; padding-top: 5px; border-top: 1px solid #e0e0e0;">
                            <a href="livro_editar.php?id=<?= $livro['id'] ?>" 
                               class="btn btn-warning btn-small">
                                ✏️ Editar
                            </a>
                            
                            <?php if ($disponivel): ?>
                                <a href="emprestimo_novo.php?livro_id=<?= $livro['id'] ?>" 
                                   class="btn btn-success btn-small">
                                    📋 Emprestar
                                </a>
                            <?php endif; ?>
                            
                            <a href="livro_excluir.php?id=<?= $livro['id'] ?>" 
                               class="btn btn-danger btn-small confirm-delete">
                                🗑️ Excluir
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_paginas > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin: 30px 0;">
                <?php 
                // Função auxiliar para construir a URL de paginação, incluindo todos os filtros
                function buildPaginationUrl($pagina, $busca, $autor, $categoria, $disponivel) {
                    return "?pagina={$pagina}&busca=" . urlencode($busca) . "&autor={$autor}&categoria=" . urlencode($categoria) . "&disponivel={$disponivel}";
                }
                ?>

                <?php if ($pagina_atual > 1): ?>
                    <a href="<?= buildPaginationUrl($pagina_atual - 1, $filtro_busca, $filtro_autor, $filtro_categoria, $filtro_disponivel) ?>" 
                       class="btn btn-secondary btn-small">
                        « Anterior
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($i == $pagina_atual): ?>
                        <span class="btn btn-small"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= buildPaginationUrl($i, $filtro_busca, $filtro_autor, $filtro_categoria, $filtro_disponivel) ?>" 
                           class="btn btn-secondary btn-small">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="<?= buildPaginationUrl($pagina_atual + 1, $filtro_busca, $filtro_autor, $filtro_categoria, $filtro_disponivel) ?>" 
                       class="btn btn-secondary btn-small">
                        Próxima »
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhum livro encontrado.</strong><br>
            <?php if (!empty($filtro_busca) || $filtro_autor > 0 || !empty($filtro_categoria)): ?>
                Tente ajustar os filtros de busca.
            <?php else: ?>
                Comece <a href="livro_novo.php" style="color: #0c5460; text-decoration: underline;">cadastrando um novo livro</a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar livros: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>