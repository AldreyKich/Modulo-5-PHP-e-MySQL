<?php
// index.php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/funcoes.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    // Buscar estatísticas gerais
    $sql = "
        SELECT
            (SELECT COUNT(*) FROM livros) AS total_livros,
            (SELECT SUM(quantidade_disponivel) FROM livros) AS livros_disponiveis,
            (SELECT COUNT(*) FROM clientes) AS total_clientes,
            (SELECT COUNT(*) FROM autores) AS total_autores,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo') AS emprestimos_ativos,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados
    ";

    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch();

    echo "<h1>Bem-vindo ao Sistema de Biblioteca</h1>";
    echo "<p>Gerencie livros, clientes e empréstimos de forma eficiente.</p>";

    // Cards de estatísticas
    echo "<div style='display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:20px; margin:30px 0;'>";

    // Card Total de Livros
    echo "<div style='background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:25px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin:0; font-size:42px;'>" . $stats['total_livros'] . "</h2>";
    echo "<p style='margin:10px 0 0 0; font-size:16px;'>Total de Livros</p>";
    echo "<a href='livros.php' style='color:white; text-decoration:underline;'>Ver todos</a>";
    echo "</div>";

    // Card Livros Disponíveis
    echo "<div style='background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%); color:white; padding:25px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin:0; font-size:42px;'>" . $stats['livros_disponiveis'] . "</h2>";
    echo "<p style='margin:10px 0 0 0; font-size:16px;'>Livros Disponíveis</p>";
    echo "</div>";

    // Card Total de Clientes
    echo "<div style='background:linear-gradient(135deg,#fa709a 0%,#fee140 100%); color:white; padding:25px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin:0; font-size:42px;'>" . $stats['total_clientes'] . "</h2>";
    echo "<p style='margin:10px 0 0 0; font-size:16px;'>Clientes Cadastrados</p>";
    echo "<a href='clientes.php' style='color:white; text-decoration:underline;'>Ver todos</a>";
    echo "</div>";

    // Card Empréstimos Ativos
    echo "<div style='background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%); color:white; padding:25px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin:0; font-size:42px;'>" . $stats['emprestimos_ativos'] . "</h2>";
    echo "<p style='margin:10px 0 0 0; font-size:16px;'>Empréstimos Ativos</p>";
    echo "<a href='emprestimos.php' style='color:white; text-decoration:underline;'>Ver todos</a>";
    echo "</div>";

    echo "</div>";

    // Alerta de empréstimos atrasados
    if ($stats['emprestimos_atrasados'] > 0) {
        echo "<div style='background:#ffebee; border-left:4px solid #f44336; padding:15px; margin:20px 0;'>";
        echo "<strong style='color:#c62828;'>ATENÇÃO!</strong> ";
        echo "Existem " . $stats['emprestimos_atrasados'] . " empréstimo(s) em atraso.";
        echo " <a href='emprestimos.php?filtro=atrasados'>Ver detalhes</a>";
        echo "</div>";
    }

    // Últimos livros cadastrados
    echo "<h2 style='margin-top:40px;'>Últimos Livros Cadastrados</h2>";

    $sql = "
        SELECT l.id, l.titulo, a.nome AS autor, l.ano_publicacao, l.quantidade_disponivel
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        ORDER BY l.id DESC
        LIMIT 5
    ";
    $stmt = $pdo->query($sql);
    $ultimos_livros = $stmt->fetchAll();

    if (count($ultimos_livros) > 0) {
        echo "<table>";
        echo "<tr><th>Título</th><th>Autor</th><th>Ano</th><th>Disponíveis</th><th>Ações</th></tr>";
        foreach ($ultimos_livros as $livro) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($livro['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($livro['autor']) . "</td>";
            echo "<td>" . $livro['ano_publicacao'] . "</td>";
            echo "<td>" . $livro['quantidade_disponivel'] . "</td>";
            echo "<td><a href='livro_detalhes.php?id=" . $livro['id'] . "' class='btn'>Ver Detalhes</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Top 5 livros mais emprestados
    echo "<h2 style='margin-top:40px;'>Top 5 Livros Mais Emprestados</h2>";

    $sql = "
        SELECT l.titulo, a.nome AS autor, COUNT(e.id) AS total_emprestimos
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        LEFT JOIN emprestimos e ON l.id = e.livro_id
        GROUP BY l.id
        HAVING total_emprestimos > 0
        ORDER BY total_emprestimos DESC
        LIMIT 5
    ";
    $stmt = $pdo->query($sql);
    $top_livros = $stmt->fetchAll();

    if (count($top_livros) > 0) {
        echo "<table>";
        echo "<tr><th>Posição</th><th>Título</th><th>Autor</th><th>Empréstimos</th></tr>";
        $posicao = 1;
        foreach ($top_livros as $livro) {
            echo "<tr>";
            echo "<td style='font-weight:bold; text-align:center;'>#" . $posicao . "</td>";
            echo "<td>" . htmlspecialchars($livro['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($livro['autor']) . "</td>";
            echo "<td style='text-align:center;'>" . $livro['total_emprestimos'] . "</td>";
            echo "</tr>";
            $posicao++;
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum empréstimo registrado ainda.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro ao carregar dados: " . $e->getMessage() . "</p>";
}

require_once 'includes/footer.php';
?>
