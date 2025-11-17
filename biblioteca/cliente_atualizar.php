<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Receber dados do formulário
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);

    // Validar dados
    $erros = [];

    if ($id <= 0) {
        $erros[] = "ID inválido para atualização.";
    }

    if (empty($nome)) {
        $erros[] = "Nome é obrigatório.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }

    if (empty($telefone)) {
        $erros[] = "Telefone é obrigatório.";
    }

    // Se não houver erros, atualizar no banco
    if (empty($erros)) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $sql = "UPDATE clientes 
                    SET nome = :nome, email = :email, telefone = :telefone 
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone
            ]);

            // Redirecionar com mensagem de sucesso
            header("Location: clientes.php?msg=atualizado");
            exit;

        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar: " . $e->getMessage();
        }
    }

    // Se houver erros, exibir
    if (!empty($erros)) {
        require_once 'includes/header.php';
        echo "<h2>Erros encontrados:</h2>";
        echo "<ul>";
        foreach ($erros as $erro) {
            echo "<li style='color:red;'>" . htmlspecialchars($erro) . "</li>";
        }
        echo "</ul>";
        echo "<a href='clientes.php' class='btn'>Voltar</a>";
        require_once 'includes/footer.php';
    }

} else {
    header("Location: clientes.php");
    exit;
}
?>
