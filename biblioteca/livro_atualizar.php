<?php
/**
 * Processamento do Formulário de Edição de Livro
 * * Atualiza os dados de um livro existente, incluindo a manipulação da capa.
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.1 (Com Edição de Imagem)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php'; // Inclui a função processarUploadCapa()

// Define o diretório físico (ASSUMA que você definiu a constante DIRETORIO_CAPAS em config.php)
if (!defined('DIRETORIO_CAPAS')) {
    define('DIRETORIO_CAPAS', 'uploads/capas/'); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 1. Coleta e Limpeza de Dados
    $id = (int)$_POST['id'];
    $titulo = limparInput($_POST['titulo']);
    $autor_id = (int)$_POST['autor_id'];
    $isbn = limparInput($_POST['isbn']);
    $ano_publicacao = limparInput($_POST['ano_publicacao']);
    $editora = limparInput($_POST['editora']);
    $numero_paginas = limparInput($_POST['numero_paginas']);
    $categoria = limparInput($_POST['categoria']);
    $localizacao = limparInput($_POST['localizacao']);
    $quantidade_total = (int)$_POST['quantidade_total'];
    $quantidade_disponivel = (int)$_POST['quantidade_disponivel'];
    
    // NOVO: Dados da Capa
    $capa_imagem_atual = limparInput($_POST['capa_imagem_atual'] ?? null); // Nome do arquivo atual
    $remover_capa = isset($_POST['remover_capa']) && $_POST['remover_capa'] == '1'; // Se o checkbox foi marcado
    $novo_upload = isset($_FILES['capa_imagem']) && $_FILES['capa_imagem']['error'] === UPLOAD_ERR_OK;


    // 2. Validações básicas
    $erros = [];
    if ($id <= 0) $erros[] = "ID inválido.";
    if (empty($titulo)) $erros[] = "O título é obrigatório.";
    if ($autor_id <= 0) $erros[] = "Selecione um autor.";
    if ($quantidade_total < 1) $erros[] = "Quantidade total deve ser pelo menos 1.";
    if ($quantidade_disponivel < 0) $erros[] = "Quantidade disponível inválida.";
    if ($quantidade_disponivel > $quantidade_total) $erros[] = "Disponível não pode ser maior que total.";

    if (empty($erros)) {
        $capa_imagem_nome = $capa_imagem_atual; // Por padrão, mantém a capa atual

        // ===========================================
        // 3. Lógica de Manipulação da Capa
        // ===========================================

        if ($remover_capa) {
            // A) Opção de Remoção marcada
            if (!empty($capa_imagem_atual)) {
                @unlink(DIRETORIO_CAPAS . $capa_imagem_atual); // Deleta o arquivo físico
            }
            $capa_imagem_nome = null; // Define o campo do BD como NULL
            
        } else if ($novo_upload) {
            // B) Novo Arquivo Enviado (Sobrescrever)
            $capa_imagem_nome = processarUploadCapa($_FILES['capa_imagem']);

            if ($capa_imagem_nome === false) {
                // Se o processamento falhou, a função já exibiu a mensagem de erro.
                exit; 
            }

            if ($capa_imagem_nome !== null && !empty($capa_imagem_atual)) {
                // Se o novo upload foi bem-sucedido, deleta o arquivo antigo
                @unlink(DIRETORIO_CAPAS . $capa_imagem_atual);
            }
        }
        // Se nenhuma das opções acima for verdadeira, capa_imagem_nome = capa_imagem_atual (mantém o que estava)


        // 4. Atualização no Banco de Dados
        try {
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
                        quantidade_disponivel = :quantidade_disponivel,
                        capa_imagem = :capa_imagem,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'titulo' => $titulo,
                'autor_id' => $autor_id,
                'isbn' => empty($isbn) ? null : $isbn,
                'ano_publicacao' => empty($ano_publicacao) ? null : (int)$ano_publicacao,
                'editora' => empty($editora) ? null : $editora,
                'numero_paginas' => empty($numero_paginas) ? null : (int)$numero_paginas,
                'categoria' => empty($categoria) ? null : $categoria,
                'localizacao' => empty($localizacao) ? null : $localizacao,
                'quantidade_total' => $quantidade_total,
                'quantidade_disponivel' => $quantidade_disponivel,
                'capa_imagem' => $capa_imagem_nome // Novo nome, ou NULL, ou nome antigo
            ]);

            // Redirecionamento de Sucesso
            header("Location: livros.php?msg=atualizado");
            exit;

        } catch (PDOException $e) {
            // Se falhar a atualização no BD, tentamos remover o novo arquivo que pode ter sido feito upload
            if ($novo_upload && $capa_imagem_nome !== false) {
                 @unlink(DIRETORIO_CAPAS . $capa_imagem_nome); 
            }
            exibirMensagem('erro', 'Erro ao atualizar livro: ' . $e->getMessage());
        }
    } else {
        // Se houver erros de validação
        require_once 'includes/header.php';
        echo "<div class='alert alert-danger'><ul>";
        foreach ($erros as $erro) {
            echo "<li>" . htmlspecialchars($erro) . "</li>";
        }
        echo "</ul></div>";
        echo "<a href='livro_editar.php?id=$id' class='btn btn-warning'>Voltar</a>";
        require_once 'includes/footer.php';
    }
} else {
    // Acesso direto sem POST
    header("Location: livros.php");
    exit;
}
?>