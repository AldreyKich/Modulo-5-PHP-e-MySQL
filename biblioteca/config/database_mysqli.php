<?php
// Parâmetros de conexão
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "biblioteca";

// Criar conexão
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

// Verificar conexão
if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Definir charset
mysqli_set_charset($conexao, "utf8mb4");

echo "Conexão estabelecida com sucesso!";
?>
