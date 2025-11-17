<?php
require_once 'config/database_mysqli.php';

// Consulta SQL
$sql = "
    SELECT 
        l.id, 
        l.titulo, 
        a.nome AS autor, 
        l.ano_publicacao
    FROM livros l
    INNER JOIN autores a ON l.autor_id = a.id
    ORDER BY l.titulo
";

// Executar consulta
$resultado = mysqli_query($conexao, $sql);

// Verificar se há resultados
if (mysqli_num_rows($resultado) > 0) {
    echo "<h2>Lista de Livros</h2>";
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Título</th>
            <th>Autor</th>
            <th>Ano</th>
          </tr>";

    // Exibir cada linha
    while ($linha = mysqli_fetch_assoc($resultado)) {
        echo "<tr>";
        echo "<td>" . $linha['id'] . "</td>";
        echo "<td>" . $linha['titulo'] . "</td>";
        echo "<td>" . $linha['autor'] . "</td>";
        echo "<td>" . $linha['ano_publicacao'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Nenhum livro encontrado.";
}

// Liberar resultado e fechar conexão
mysqli_free_result($resultado);
mysqli_close($conexao);
?>
