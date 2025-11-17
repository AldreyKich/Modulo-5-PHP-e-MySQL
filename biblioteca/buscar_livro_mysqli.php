<?php
require_once 'config/database_mysqli.php';

// Parâmetro de busca
$termo_busca = "Dom";

// Consulta com filtro
$sql = "
    SELECT 
        l.id, 
        l.titulo, 
        a.nome AS autor
    FROM livros l
    INNER JOIN autores a ON l.autor_id = a.id
    WHERE l.titulo LIKE '%" . mysqli_real_escape_string($conexao, $termo_busca) . "%'
";

// Executar consulta
$resultado = mysqli_query($conexao, $sql);

if (mysqli_num_rows($resultado) > 0) {
    echo "<h3>Resultados para: " . htmlspecialchars($termo_busca) . "</h3>";

    while ($linha = mysqli_fetch_assoc($resultado)) {
        echo "<p>";
        echo "<strong>" . htmlspecialchars($linha['titulo']) . "</strong><br>";
        echo "Autor: " . htmlspecialchars($linha['autor']);
        echo "</p>";
    }
} else {
    echo "Nenhum resultado encontrado.";
}

// Fechar conexão
mysqli_close($conexao);
?>
