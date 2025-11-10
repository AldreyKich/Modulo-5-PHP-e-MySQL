<?php
/**
 * Classe Database - Padrão Singleton
 * 
 * Esta classe implementa o padrão Singleton para garantir que apenas
 * UMA instância da conexão com o banco de dados seja criada durante
 * toda a execução da aplicação.
 * 
 * VANTAGENS DO SINGLETON:
 * - Economia de recursos (uma única conexão)
 * - Consistência (todos usam a mesma conexão)
 * - Controle centralizado
 * 
 * @author Sistema Biblioteca
 * @version 1.0
 */

class Database {
    // Propriedade estática que armazena a única instância da classe
    private static $instance = null;
    
    // Propriedade que armazena a conexão PDO
    private $pdo;
    
    // Configurações do banco de dados
    private $host = "localhost";
    private $db = "biblioteca";
    private $user = "root";
    private $pass = "";
    private $charset = "utf8mb4";
    
    /**
     * Construtor privado - impede criação de novas instâncias
     * 
     * O construtor é privado para que ninguém possa fazer:
     * $db = new Database(); (isso causaria erro!)
     * 
     * A única forma de obter a instância é através do método getInstance()
     */
    private function __construct() {
        try {
            // DSN (Data Source Name) - string de conexão
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            
            // Opções de configuração do PDO
            $options = [
                // Modo de erro: lança exceções quando há erros
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                
                // Modo de fetch padrão: retorna arrays associativos
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                
                // Desabilita emulação de prepared statements (mais seguro)
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Cria a conexão PDO
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch(PDOException $e) {
            // Em produção, você deve logar este erro em um arquivo
            // e mostrar uma mensagem genérica ao usuário
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    /**
     * Método estático para obter a instância única
     * 
     * Este é o método que você deve usar para acessar o banco:
     * $db = Database::getInstance();
     * 
     * @return Database Instância única da classe
     */
    public static function getInstance() {
        // Se ainda não existe uma instância, cria uma
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        
        // Retorna a instância (nova ou existente)
        return self::$instance;
    }
    
    /**
     * Retorna a conexão PDO para executar queries
     * 
     * @return PDO Objeto de conexão PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Previne clonagem da instância
     * 
     * Sem este método, alguém poderia fazer:
     * $db2 = clone $db1; (criando uma segunda conexão!)
     */
    private function __clone() {
        // Método vazio - apenas impede clonagem
    }
    
    /**
     * Previne deserialização da instância
     * 
     * Sem este método, a instância poderia ser serializada/deserializada
     * criando múltiplas instâncias
     */
    public function __wakeup() {
        throw new Exception("Não é possível deserializar um Singleton.");
    }
}
