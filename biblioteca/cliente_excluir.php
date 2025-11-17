<?php
require_once 'config/database.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    if ($id > 0) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // Verificar se o cliente existe
            $verifica = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
            $verifica->execute(['id' => $id]);
            $cliente = $verifica->fetch();

            if ($cliente) {
                // Excluir o registro
                $sql = "DELETE FROM clientes WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);

                // Redirecionar com mensagem de sucesso
                header("Location: clientes.php?msg=excluido");
                exit;
            } else {
                // Caso o ID não exista
                require_once 'includes/header.php';
                echo "<p style='color:red;'>Cliente não encontrado.</p>";
                echo "<a href='clientes.php' class='btn'>Voltar</a>";
                require_once 'includes/footer.php';
            }
        } catch (PDOException $e) {
            require_once 'includes/header.php';
            echo "<p style='color:red;'>Erro ao excluir: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<a href='clientes.php' class='btn'>Voltar</a>";
            require_once 'includes/footer.php';
        }
    } else {
        header("Location: clientes.php?msg=id_invalido");
        exit;
    }
} else {
    header("Location: clientes.php");
    exit;
}
?>
