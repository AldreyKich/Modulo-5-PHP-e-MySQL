<?php
/**
 * Processamento do Formulário de Cadastro de Novo Livro
 * * Salva os dados do novo livro, incluindo o upload da capa, no acervo.
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.1 (Com Upload de Imagem)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// Define o diretório de upload (ASSUMA que você definiu a constante DIRETORIO_CAPAS em config.php)
// Exemplo: define('DIRETORIO_CAPAS', 'uploads/capas/');
if (!defined('DIRETORIO_CAPAS')) {
    // Definindo um valor padrão se não estiver em config.php (ajuste conforme a necessidade)
    define('DIRETORIO_CAPAS', 'uploads/capas/'); 
}

$db = Database::getInstance();
$pdo = $db->getConnection();

// ===========================================
// FUNÇÃO DE UPLOAD (Pode estar em includes/funcoes.php, mas está aqui para completude)
// ===========================================
function processarUploadCapa(array $file_array)
{
    $upload_dir = DIRETORIO_CAPAS;
    
    // Se o diretório não existir, tenta criar
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            exibirMensagem('erro', 'Falha ao criar o diretório de uploads. Verifique as permissões: ' . $upload_dir);
            return false;
        }
    }
    
    // Configurações de segurança e validação
    $max_size = 2 * 1024 * 1024; // 2 MB
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file_array['error'] !== UPLOAD_ERR_OK) {
        if ($file_array['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // Não foi enviado arquivo, retorna NULL
        }
        exibirMensagem('erro', 'Erro no upload do arquivo (código: ' . $file_array['error'] . ').');
        return false;
    }

    if ($file_array['size'] > $max_size) {
        exibirMensagem('erro', 'O arquivo é muito grande. Máximo permitido: 2MB.');
        return false;
    }

    // Validação real do tipo de arquivo (MIME Type)
    $mime_type = mime_content_type($file_array['tmp_name']);
    if (!in_array($mime_type, $allowed_mimes)) {
        exibirMensagem('erro', 'Tipo de arquivo não permitido. Use JPG, PNG ou WebP.');
        return false;
    }

    // Cria um nome de arquivo único e seguro
    $ext = pathinfo($file_array['name'], PATHINFO_EXTENSION);
    $nome_base = md5(uniqid(rand(), true));
    $nome_final = $nome_base . '.' . $ext;
    $caminho_final = $upload_dir . $nome_final;

    // Move o arquivo para o destino
    if (move_uploaded_file($file_array['tmp_name'], $caminho_final)) {
        return $nome_final;
    } else {
        exibirMensagem('erro', 'Falha ao mover o arquivo de upload para o destino.');
        return false;
    }
}
// ===========================================


// 1. Coleta e Limpeza de Dados do Formulário
$titulo = limparInput($_POST['titulo'] ?? '');
$autor_id = (int)($_POST['autor_id'] ?? 0);
$isbn = limparInput($_POST['isbn'] ?? '');
$ano_publicacao = limparInput($_POST['ano_publicacao'] ?? null);
$editora = limparInput($_POST['editora'] ?? '');
$numero_paginas = limparInput($_POST['numero_paginas'] ?? null);
$quantidade_total = (int)($_POST['quantidade_total'] ?? 0);
$quantidade_disponivel = (int)($_POST['quantidade_disponivel'] ?? 0);
$categoria = limparInput($_POST['categoria'] ?? '');
$localizacao = limparInput($_POST['localizacao'] ?? '');

// 2. Validação Mínima (Embora o JS faça, o PHP é a validação de segurança)
if (empty($titulo) || $autor_id <= 0 || $quantidade_total < 1 || $quantidade_disponivel < 0 || $quantidade_disponivel > $quantidade_total) {
    exibirMensagem('erro', 'Dados obrigatórios inválidos ou faltando. Por favor, volte e verifique o formulário.');
    // header('Location: livro_novo.php'); // Opcional: redirecionar de volta
    exit;
}

// 3. Processamento do Upload da Capa
$capa_nome = processarUploadCapa($_FILES['capa_imagem'] ?? []);

if ($capa_nome === false) {
    // Se o upload retornou FALSE, significa que houve um erro e a mensagem já foi exibida dentro da função.
    exit; 
}


// 4. Inserção no Banco de Dados
try {
    $sql = "INSERT INTO livros 
            (titulo, autor_id, isbn, ano_publicacao, editora, numero_paginas, 
             quantidade_total, quantidade_disponivel, categoria, localizacao, capa_imagem, created_at) 
            VALUES 
            (:titulo, :autor_id, :isbn, :ano_publicacao, :editora, :numero_paginas, 
             :quantidade_total, :quantidade_disponivel, :categoria, :localizacao, :capa_imagem, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        'titulo' => $titulo,
        'autor_id' => $autor_id,
        'isbn' => empty($isbn) ? null : $isbn,
        'ano_publicacao' => empty($ano_publicacao) ? null : (int)$ano_publicacao,
        'editora' => empty($editora) ? null : $editora,
        'numero_paginas' => empty($numero_paginas) ? null : (int)$numero_paginas,
        'quantidade_total' => $quantidade_total,
        'quantidade_disponivel' => $quantidade_disponivel,
        'categoria' => empty($categoria) ? null : $categoria,
        'localizacao' => empty($localizacao) ? null : $localizacao,
        'capa_imagem' => $capa_nome, // Salva o nome do arquivo (ou NULL se não houve upload)
    ]);
    
    // 5. Redirecionamento de Sucesso
    exibirMensagem('sucesso', 'Livro "' . $titulo . '" cadastrado com sucesso! ID: ' . $pdo->lastInsertId());
    header('Location: livros.php');
    exit;

} catch (PDOException $e) {
    // Se a inserção falhar, apaga o arquivo recém-upload para evitar lixo
    if (!empty($capa_nome)) {
        @unlink(DIRETORIO_CAPAS . $capa_nome); 
    }
    exibirMensagem('erro', 'Erro ao cadastrar livro no banco de dados: ' . $e->getMessage());
    header('Location: livro_novo.php');
    exit;
}

?>