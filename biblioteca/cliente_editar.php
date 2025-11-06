<?php
require_once 'config/database_pdo.php';
require_once 'includes/header.php';

$db = new DatabasePDO();
$pdo = $db->getConexao();

$cliente_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($cliente_id > 0) {
    try {
        $sql = "SELECT * FROM clientes WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $cliente_id]);
        $cliente = $stmt->fetch();

        if ($cliente) {
?>
            <h1>Editar Cliente</h1>

            <form method="POST" action="cliente_atualizar.php">
                <input type="hidden" name="id" value="<?= $cliente['id'] ?>">

                <div style="margin-bottom: 15px;">
                    <label for="nome">Nome Completo:</label><br>
                    <input type="text" id="nome" name="nome"
                           value="<?= htmlspecialchars($cliente['nome']) ?>"
                           required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="email">E-mail:</label><br>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($cliente['email']) ?>"
                           required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="telefone">Telefone:</label><br>
                    <input type="text" id="telefone" name="telefone"
                           value="<?= htmlspecialchars($cliente['telefone']) ?>"
                           required style="width: 100%; padding: 8px;">
                </div>

                <button type="submit" class="btn">Atualizar</button>
                <a href="clientes.php" class="btn btn-warning">Cancelar</a>
            </form>
<?php
        } else {
            echo "<p>Cliente não encontrado.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erro: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>ID inválido.</p>";
}

require_once 'includes/footer.php';
?>
