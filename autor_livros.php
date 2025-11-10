<?php
// Inclui os arquivos necessários.
// 'config/database.php' DEVE conter a classe Database que você forneceu.
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

// -------------------------------------------------------------------------
// CORREÇÃO ESSENCIAL: Obtém a instância da conexão PDO do padrão Singleton.
// A variável $conn agora está definida e pronta para ser usada nas consultas.
// -------------------------------------------------------------------------
try {
    $conn = Database::getInstance()->getConnection(); 
} catch (Exception $e) {
    // Caso a conexão falhe, exibe o erro e interrompe.
    die("Falha ao obter conexão com o banco de dados: " . $e->getMessage());
}

// 1. Obtém o ID do autor da URL
// O link esperado é, por exemplo: autor_livros.php?id=6
$autor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verifica se um ID de autor válido foi fornecido
if ($autor_id <= 0) {
    // Redireciona se o ID for inválido ou ausente
    header("Location: /biblioteca/erro.php?msg=ID de autor inválido ou ausente.");
    exit();
}

// 2. Consulta para obter o nome e detalhes do autor (para o cabeçalho)
$stmt_autor = $conn->prepare("SELECT nome, nacionalidade, data_nascimento FROM autores WHERE id = :autor_id");
$stmt_autor->bindParam(':autor_id', $autor_id, PDO::PARAM_INT);
$stmt_autor->execute();
$autor_info = $stmt_autor->fetch(PDO::FETCH_ASSOC);

if (!$autor_info) {
    // Se o autor não for encontrado
    header("Location: /biblioteca/erro.php?msg=Autor não encontrado com o ID fornecido.");
    exit();
}

$autor_nome = $autor_info['nome'];
// Formata a data de nascimento ou exibe "Não informada"
$data_nascimento = $autor_info['data_nascimento'] ? date('d/m/Y', strtotime($autor_info['data_nascimento'])) : "Não informada";

// 3. Consulta principal para buscar os livros do autor
$sql = "
    SELECT 
        l.id AS livro_id,
        l.titulo,
        l.isbn,
        l.ano_publicacao,
        l.editora
    FROM autores a
    LEFT JOIN livros l ON a.id = l.autor_id
    WHERE a.id = :autor_id
    ORDER BY l.titulo
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':autor_id', $autor_id, PDO::PARAM_INT); // Vincula o ID à consulta principal
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <!-- Título da página com o nome do autor -->
    <title>Livros de <?php echo htmlspecialchars($autor_nome); ?></title>
    <!-- Inclui Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <!-- Título dinâmico -->
    <h2 class="text-center mb-4 text-primary">Livros do Autor: <?php echo htmlspecialchars($autor_nome); ?></h2>
    
    <!-- Botão para Voltar -->
    <p class="text-center">
        <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">
            &larr; Voltar
        </a>
    </p>

    <?php
    // Verifica se há livros e se o primeiro registro tem um ID de livro válido
    $tem_livros = count($dados) > 0 && !empty($dados[0]['livro_id']);

    // Informações do Autor (cabeçalho)
    echo "
    <div class='card mb-4 shadow-sm'>
        <div class='card-header bg-primary text-white'>
            <h5 class='mb-0'>Informações do Autor</h5>
            <small>" . htmlspecialchars($autor_info['nacionalidade']) . " - Nascimento: {$data_nascimento}</small>
        </div>
        <div class='card-body p-0'>
            <table class='table table-striped mb-0'>
                <thead class='table-light'>
                    <tr>
                        <th>Título</th>
                        <th>Ano</th>
                        <th>ISBN</th>
                        <th>Editora</th>
                    </tr>
                </thead>
                <tbody>
    ";

    if ($tem_livros) {
        // Lista de Livros
        foreach ($dados as $linha) {
            echo "
                <tr>
                    <td>" . htmlspecialchars($linha['titulo']) . "</td>
                    <td>" . htmlspecialchars($linha['ano_publicacao']) . "</td>
                    <td>" . htmlspecialchars($linha['isbn']) . "</td>
                    <td>" . htmlspecialchars($linha['editora']) . "</td>
                </tr>
            ";
        }
    } else {
         // Mensagem de que o autor não possui livros
         echo "
            <tr>
                <td colspan='4'>
                    <em class='d-block text-center p-3'>Este autor ainda não possui livros cadastrados.</em>
                </td>
            </tr>
         ";
    }
    
    echo "</tbody></table></div></div>";
    ?>

</div>
</body>
</html>