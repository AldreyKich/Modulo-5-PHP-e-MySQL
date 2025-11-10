<?php
/**
 * Script de processamento para salvar (INSERT) ou atualizar (UPDATE) um autor.
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários, incluindo a classe Database
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redireciona se a página for acessada diretamente sem POST
    header("Location: autores.php");
    exit();
}

// -------------------------------------------------------------------------
// 1. OBTENÇÃO DA CONEXÃO E VALIDAÇÃO BÁSICA
// -------------------------------------------------------------------------

try {
    // Obtém a instância da conexão PDO do padrão Singleton
    $conn = Database::getInstance()->getConnection();
} catch (Exception $e) {
    // Em caso de falha na conexão, loga o erro e interrompe
    error_log("Erro de Conexão: " . $e->getMessage());
    header("Location: erro.php?msg=Falha ao conectar ao banco de dados.");
    exit();
}

// Obtém o ID. Se existir, é uma EDIÇÃO (UPDATE). Se for 0 ou nulo, é um NOVO CADASTRO (INSERT).
$autor_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$acao = $autor_id > 0 ? 'edição' : 'cadastro';

// -------------------------------------------------------------------------
// 2. COLETA E SANEAMENTO DOS DADOS DO FORMULÁRIO
// -------------------------------------------------------------------------

$nome = trim($_POST['nome'] ?? '');
$nacionalidade = trim($_POST['nacionalidade'] ?? '');
$data_nascimento = trim($_POST['data_nascimento'] ?? null);
$biografia = trim($_POST['biografia'] ?? '');

// Verifica se o campo obrigatório (nome) está preenchido
if (empty($nome)) {
    header("Location: erro.php?msg=O campo Nome é obrigatório.");
    exit();
}

// Converte a data_nascimento para NULL se estiver vazia
if (empty($data_nascimento)) {
    $data_nascimento = null;
}

// -------------------------------------------------------------------------
// 3. MONTAGEM E EXECUÇÃO DA CONSULTA SQL
// -------------------------------------------------------------------------

try {
    $conn->beginTransaction();

    if ($acao === 'edição') {
        // --- Operação de UPDATE (Edição) ---
        $sql = "UPDATE autores SET 
                    nome = :nome, 
                    nacionalidade = :nacionalidade, 
                    data_nascimento = :data_nascimento, 
                    biografia = :biografia
                WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $autor_id, PDO::PARAM_INT);
        $mensagem_sucesso = "✅ Autor atualizado com sucesso!";

    } else {
        // --- Operação de INSERT (Novo Cadastro) ---
        $sql = "INSERT INTO autores (nome, nacionalidade, data_nascimento, biografia) 
                VALUES (:nome, :nacionalidade, :data_nascimento, :biografia)";
        
        $stmt = $conn->prepare($sql);
        $mensagem_sucesso = "✅ Autor cadastrado com sucesso!";
    }

    // Vinculação dos parâmetros comuns
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':nacionalidade', $nacionalidade);
    // BindParam para data pode exigir um tipo específico ou passar como string, 
    // mas o PDO lida bem com strings que representam datas
    $stmt->bindParam(':data_nascimento', $data_nascimento); 
    $stmt->bindParam(':biografia', $biografia);

    $stmt->execute();
    
    $conn->commit();

    // -------------------------------------------------------------------------
    // 4. REDIRECIONAMENTO DE SUCESSO
    // -------------------------------------------------------------------------
    
    // Após a edição/cadastro, redireciona para a listagem
    header("Location: autores.php?msg=" . urlencode($mensagem_sucesso));
    exit();

} catch (PDOException $e) {
    // Em caso de erro, desfaz as alterações (se houver)
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Loga o erro detalhado
    error_log("Erro durante a $acao do autor: " . $e->getMessage());

    // Redireciona com mensagem de erro amigável
    $mensagem_erro = "❌ Erro ao tentar realizar a $acao do autor. Tente novamente.";
    header("Location: erro.php?msg=" . urlencode($mensagem_erro));
    exit();
}
?>