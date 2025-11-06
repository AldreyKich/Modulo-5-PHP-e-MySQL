<?php
// livros.php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/funcoes.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// Paginação
$por_pagina = 15;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

// Filtros
$filtro_busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$filtro_autor = isset($_GET['autor']) ? (int)$_GET['autor'] : 0;

try {
    // Construir WHERE
    $where_clauses = [];
    $params = [];

    if (!empty($filtro_busca)) {
        $where_clauses[] = "l.titulo LIKE :busca";
        $params['busca'] = "%$filtro_busca%";
    }

    if ($filtro_autor > 0) {
        $where_clauses[] = "l.autor_id = :autor_id";
        $params['autor_id'] = $filtro_autor;
    }

    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

    // Contar total de registros
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM livros l $where_sql");
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $por_pagina);

    // Buscar livros
    $sql = "SELECT l.*, a.nome AS autor, a.nacionalidade
            FROM livros l
            INNER JOIN autores a ON l.autor_id = a.id
            $where_sql
            ORDER BY l.titulo
            LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $livros = $stmt->fetchAll();

    // Buscar autores para filtro
    $stmt = $pdo->query("SELECT id, nome FROM autores ORDER BY nome");
    $autores = $stmt->fetchAll();

    // Exibir filtros
    echo "<h1>Catálogo de Livros</h1>";
    echo "<form method='GET' action='livros.php'>";
    echo "<label>Buscar por título:</label>";
    echo "<input type='text' name='busca' value='" . htmlspecialchars($filtro_busca) . "'>";
    echo "<label>Filtrar por autor:</label>";
    echo "<select name='autor'>";
    echo "<option value='0'>Todos os autores</option>";
    foreach ($autores as $autor) {
        $selected = ($autor['id'] == $filtro_autor) ? 'selected' : '';
        echo "<option value='{$autor['id']}' $selected>" . htmlspecialchars($autor['nome']) . "</option>";
    }
    echo "</select>";
    echo "<button type='submit'>Filtrar</button>";
    echo "</form>";

    // Exibir tabela de livros
    if (count($livros) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Título</th><th>Autor</th><th>Nacionalidade</th></tr>";
        foreach ($livros as $livro) {
            echo "<tr>";
            echo "<td>{$livro['id']}</td>";
            echo "<td>" . htmlspecialchars($livro['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($livro['autor']) . "</td>";
            echo "<td>" . htmlspecialchars($livro['nacionalidade']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum livro encontrado.</p>";
    }

    // Paginação
    if ($total_paginas > 1) {
        echo "<div>";
        for ($i = 1; $i <= $total_paginas; $i++) {
            if ($i == $pagina_atual) {
                echo "<strong>$i</strong> ";
            } else {
                $url = "livros.php?pagina=$i&busca=" . urlencode($filtro_busca) . "&autor=$filtro_autor";
                echo "<a href='$url'>$i</a> ";
            }
        }
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro ao carregar livros: " . $e->getMessage() . "</p>";
}

require_once 'includes/footer.php';
?>
