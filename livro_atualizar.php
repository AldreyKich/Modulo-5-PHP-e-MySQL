<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $titulo = trim($_POST['titulo']);
    $autor_id = (int)$_POST['autor_id'];
    $isbn = trim($_POST['isbn']);
    $ano_publicacao = trim($_POST['ano_publicacao']);
    $editora = trim($_POST['editora']);
    $numero_paginas = trim($_POST['numero_paginas']);
    $categoria = trim($_POST['categoria']);
    $localizacao = trim($_POST['localizacao']);
    $quantidade_total = (int)$_POST['quantidade_total'];
    $quantidade_disponivel = (int)$_POST['quantidade_disponivel'];

    // Validações básicas
    $erros = [];
    if ($id <= 0) $erros[] = "ID inválido.";
    if (empty($titulo)) $erros[] = "O título é obrigatório.";
    if ($autor_id <= 0) $erros[] = "Selecione um autor.";
    if ($quantidade_total < 1) $erros[] = "Quantidade total deve ser pelo menos 1.";
    if ($quantidade_disponivel < 0) $erros[] = "Quantidade disponível inválida.";
    if ($quantidade_disponivel > $quantidade_total) $erros[] = "Disponível não pode ser maior que total.";

    if (empty($erros)) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $sql = "UPDATE livros SET
                        titulo = :titulo,
                        autor_id = :autor_id,
                        isbn = :isbn,
                        ano_publicacao = :ano_publicacao,
                        editora = :editora,
                        numero_paginas = :numero_paginas,
                        categoria = :categoria,
                        localizacao = :localizacao,
                        quantidade_total = :quantidade_total,
                        quantidade_disponivel = :quantidade_disponivel
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'titulo' => $titulo,
                'autor_id' => $autor_id,
                'isbn' => $isbn,
                'ano_publicacao' => $ano_publicacao,
                'editora' => $editora,
                'numero_paginas' => $numero_paginas,
                'categoria' => $categoria,
                'localizacao' => $localizacao,
                'quantidade_total' => $quantidade_total,
                'quantidade_disponivel' => $quantidade_disponivel
            ]);

            header("Location: livros.php?msg=atualizado");
            exit;

        } catch (PDOException $e) {
            exibirMensagem('erro', 'Erro ao atualizar livro: ' . $e->getMessage());
        }
    } else {
        require_once 'includes/header.php';
        echo "<div class='alert alert-danger'><ul>";
        foreach ($erros as $erro) {
            echo "<li>" . htmlspecialchars($erro) . "</li>";
        }
        echo "</ul></div>";
        echo "<a href='livro_editar.php?id=$id' class='btn btn-warning'>Voltar</a>";
        require_once 'includes/footer.php';
    }
} else {
    header("Location: livros.php");
    exit;
}
?>
