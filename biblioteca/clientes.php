<?php
require_once 'includes/header.php';
?>

<h1>Cadastrar Novo Cliente</h1>

<form method="POST" action="cliente_salvar.php">
    <div style="margin-bottom: 15px;">
        <label for="nome">Nome Completo:</label><br>
        <input type="text" id="nome" name="nome" required style="width: 100%; padding: 8px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required style="width: 100%; padding: 8px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="telefone">Telefone:</label><br>
        <input type="text" id="telefone" name="telefone" required style="width: 100%; padding: 8px;">
    </div>

    <button type="submit" class="btn">Cadastrar</button>
    <a href="clientes.php" class="btn btn-warning">Cancelar</a>
</form>

<?php
require_once 'includes/footer.php';
?>
