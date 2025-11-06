<?php
class DatabasePDO {
    private $host = 'localhost';
    private $banco = 'biblioteca';
    private $usuario = 'root';
    private $senha = '';
    private $pdo;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->banco};charset=utf8mb4";

            $this->pdo = new PDO($dsn, $this->usuario, $this->senha);

            // Configurar modo de erro
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Modo de fetch padrão
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }

    public function getConexao() {
        return $this->pdo;
    }
}

// Usar a classe
$db = new DatabasePDO();
$pdo = $db->getConexao();
echo "Conexão PDO estabelecida com sucesso!";
?>
