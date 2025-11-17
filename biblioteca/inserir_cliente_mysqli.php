<?php
require_once 'config/database_mysqli.php';

// Dados do novo cliente
$nome = "Carlos Silva";
$email = "carlos.silva@email.com";
$telefone = "11999887766";

// Escapar dados para prevenir SQL Injection
$nome = mysqli_real_escape_string($conexao, $nome);
$email = mysqli_real_escape_string($conexao, $email);
$telefone = mysqli_real_escape_string($conexao, $telefone);

// Montar SQL
$sql = "INSERT INTO clientes (nome, email, telefone)
        VALUES ('$nome', '$email', '$telefone')";

// Executar
if (mysqli_query($conexao, $sql)) {
    $ultimo_id = mysqli_insert_id($conexao);
    echo "Cliente cadastrado com sucesso! ID: " . $ultimo_id;
} else {
    echo "Erro ao cadastrar: " . mysqli_error($conexao);
}

// Fechar conexão
mysqli_close($conexao);
?>
