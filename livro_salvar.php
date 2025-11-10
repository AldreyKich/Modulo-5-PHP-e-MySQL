<?php
/**
 * Script de processamento para salvar (INSERT) ou atualizar (UPDATE) um livro.
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redireciona se a página for acessada diretamente sem POST
    header("Location: livros.php");
    exit();
}

// -------------------------------------------------------------------------
// 1. OBTENÇÃO DA CONEXÃO
// -------------------------------------------------------------------------

try {
    // Obtém a instância da conexão PDO
    $conn = Database::getInstance()->getConnection();
} catch (Exception $e) {
    // Em caso de falha na conexão, loga o erro e interrompe
    error_log("Erro de Conexão: " . $e->getMessage());
    header("Location: erro.php?msg=Falha ao conectar ao banco de dados.");
    exit();
}

// -------------------------------------------------------------------------
// 2. COLETA, SANEAMENTO E VALIDAÇÃO
// -------------------------------------------------------------------------

// Variáveis de controle
$livro_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$acao = $livro_id > 0 ? 'edição' : 'cadastro';

// Coleta e saneamento dos dados
$titulo = trim($_POST['titulo'] ?? '');
$autor_id = intval($_POST['autor_id'] ?? 0);
$isbn = trim($_POST['isbn'] ?? null);
$ano_publicacao = trim($_POST['ano_publicacao'] ?? null);
$editora = trim($_POST['editora'] ?? null);
$numero_paginas = intval($_POST['numero_paginas'] ?? 0);
$categoria = trim($_POST['categoria'] ?? null);
$localizacao = trim($_POST['localizacao'] ?? null);
$quantidade_total = intval($_POST['quantidade_total'] ?? 0);
$quantidade_disponivel = intval($_POST['quantidade_disponivel'] ?? 0);

// Array para armazenar mensagens de erro
$erros = [];

// Validações
if (empty($titulo)) {
    $erros[] = "O título é obrigatório.";
}
if ($autor_id <= 0) {
    $erros[] = "Selecione um autor válido.";
}
if ($quantidade_total < 1) {
    $erros[] = "A quantidade total deve ser pelo menos 1.";
}
if ($quantidade_disponivel < 0) {
    $erros[] = "A quantidade disponível não pode ser negativa.";
}
if ($quantidade_disponivel > $quantidade_total) {
    $erros[] = "A quantidade disponível não pode ser maior que a quantidade total.";
}

// Se houver erros, redireciona de volta ao formulário
if (!empty($erros)) {
    // Monta a URL de retorno (para edição ou novo)
    $redirecionamento_url = ($livro_id > 0) ? "livro_editar.php?id={$livro_id}" : "livro_novo.php";
    $mensagem_erro_url = urlencode("❌ Erro de validação: " . implode('. ', $erros));
    
    // Redireciona com a mensagem de erro no parâmetro 'msg'
    header("Location: {$redirecionamento_url}&msg={$mensagem_erro_url}");
    exit();
}

// Trata campos opcionais para serem NULL no banco de dados se vazios
$ano_publicacao = empty($ano_publicacao) ? null : $ano_publicacao;
$isbn = empty($isbn) ? null : $isbn;
$editora = empty($editora) ? null : $editora;
$categoria = empty($categoria) ? null : $categoria;
$localizacao = empty($localizacao) ? null : $localizacao;
// Garante que 0 páginas também seja NULL
$numero_paginas = ($numero_paginas <= 0) ? null : $numero_paginas;

// -------------------------------------------------------------------------
// 3. MONTAGEM E EXECUÇÃO DA CONSULTA SQL
// -------------------------------------------------------------------------

try {
    $conn->beginTransaction();

    if ($acao === 'edição') {
        // --- Operação de UPDATE (Edição) ---
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
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $livro_id, PDO::PARAM_INT);
        $mensagem_sucesso = "✅ Livro atualizado com sucesso!";

    } else {
        // --- Operação de INSERT (Novo Cadastro) ---
        $sql = "INSERT INTO livros (
                    titulo, autor_id, isbn, ano_publicacao, editora, numero_paginas, 
                    categoria, localizacao, quantidade_total, quantidade_disponivel
                ) VALUES (
                    :titulo, :autor_id, :isbn, :ano_publicacao, :editora, :numero_paginas, 
                    :categoria, :localizacao, :quantidade_total, :quantidade_disponivel
                )";
        
        $stmt = $conn->prepare($sql);
        $mensagem_sucesso = "✅ Livro cadastrado com sucesso!";
    }

    // Vinculação dos parâmetros comuns
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':autor_id', $autor_id, PDO::PARAM_INT);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->bindParam(':ano_publicacao', $ano_publicacao);
    $stmt->bindParam(':editora', $editora);
    $stmt->bindParam(':numero_paginas', $numero_paginas, PDO::PARAM_INT);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':localizacao', $localizacao);
    $stmt->bindParam(':quantidade_total', $quantidade_total, PDO::PARAM_INT);
    $stmt->bindParam(':quantidade_disponivel', $quantidade_disponivel, PDO::PARAM_INT);

    $stmt->execute();
    
    $conn->commit();

    // -------------------------------------------------------------------------
    // 4. REDIRECIONAMENTO DE SUCESSO
    // -------------------------------------------------------------------------
    
    header("Location: livros.php?msg=" . urlencode($mensagem_sucesso));
    exit();

} catch (PDOException $e) {
    // Em caso de erro, desfaz as alterações
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Loga o erro detalhado
    error_log("Erro durante a $acao do livro: " . $e->getMessage());

    // Redireciona com mensagem de erro amigável
    $mensagem_erro = "❌ Erro ao tentar realizar a $acao do livro. Tente novamente.";
    header("Location: erro.php?msg=" . urlencode($mensagem_erro));
    exit();
}
?>