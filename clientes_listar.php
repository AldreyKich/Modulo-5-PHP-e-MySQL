<?php
/**
 * Listagem de Clientes
 * 
 * Exibe todos os clientes cadastrados no sistema com:
 * - Filtros de busca
 * - Paginação
 * - Status (Ativo, Inativo, Bloqueado)
 * - Ações (Editar, Excluir, Ver empréstimos)
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

// Obtém a conexão com o banco
$db = Database::getInstance();
$pdo = $db->getConnection();

// ========================================
// CONFIGURAÇÕES DE PAGINAÇÃO
// ========================================
$por_pagina = REGISTROS_POR_PAGINA;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

// ========================================
// FILTROS DE BUSCA
// ========================================
$filtro_busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';

try {
    // ========================================
    // CONSTRUIR QUERY COM FILTROS
    // ========================================
    
    // Array para armazenar condições WHERE
    $where_clauses = [];
    $params = [];

    // Filtro por nome ou email
    if (!empty($filtro_busca)) {
        $where_clauses[] = "(c.nome LIKE :busca OR c.email LIKE :busca)";
        $params['busca'] = "%$filtro_busca%";
    }

    // Filtro por status
    if (!empty($filtro_status)) {
        $where_clauses[] = "c.status = :status";
        $params['status'] = $filtro_status;
    }

    // Monta o WHERE final
    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

    // ========================================
    // CONTAR TOTAL DE REGISTROS
    // ========================================
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes c $where_sql");
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $por_pagina);

    // ========================================
    // BUSCAR CLIENTES
    // ========================================
    $sql = "
        SELECT 
            c.*,
            (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id AND status = 'Ativo') AS emprestimos_ativos,
            (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id AND status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados,
            (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id) AS total_emprestimos
        FROM clientes c
        $where_sql
        ORDER BY c.nome
        LIMIT :limite OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind dos parâmetros de filtro
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    // Bind dos parâmetros de paginação
    $stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $clientes = $stmt->fetchAll();

?>

    <!-- Título da Página -->
    <h1>👥 Gerenciamento de Clientes</h1>

    <!-- Botão para cadastrar novo cliente -->
    <div style="margin-bottom: 25px;">
        <a href="cliente_novo.php" class="btn btn-success">
            ➕ Cadastrar Novo Cliente
        </a>
    </div>

    <!-- ========================================
         FORMULÁRIO DE FILTROS
         ======================================== -->
    <div class="card">
        <h3>🔍 Filtros de Busca</h3>
        <form method="GET" action="clientes.php" style="background: transparent; padding: 0;">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="busca">Buscar por nome ou e-mail:</label>
                        <input 
                            type="text" 
                            id="busca" 
                            name="busca" 
                            value="<?= htmlspecialchars($filtro_busca) ?>"
                            placeholder="Digite o nome ou e-mail..."
                        >
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="status">Filtrar por status:</label>
                        <select id="status" name="status">
                            <option value="">Todos os status</option>
                            <option value="Ativo" <?= $filtro_status == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?= $filtro_status == 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            <option value="Bloqueado" <?= $filtro_status == 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn">Filtrar</button>
            <a href="clientes.php" class="btn btn-secondary">Limpar Filtros</a>
        </form>
    </div>

    <!-- Informações sobre a busca -->
    <p style="color: #666; margin: 20px 0;">
        <?php if ($total_registros > 0): ?>
            Exibindo <?= count($clientes) ?> de <?= $total_registros ?> cliente(s)
            <?php if (!empty($filtro_busca)): ?>
                | Busca por: <strong><?= htmlspecialchars($filtro_busca) ?></strong>
            <?php endif; ?>
            <?php if (!empty($filtro_status)): ?>
                | Status: <strong><?= htmlspecialchars($filtro_status) ?></strong>
            <?php endif; ?>
        <?php else: ?>
            Nenhum cliente encontrado
        <?php endif; ?>
    </p>

    <!-- ========================================
         TABELA DE CLIENTES
         ======================================== -->
    <?php if (count($clientes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Empréstimos</th>
                    <th style="width: 250px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <!-- Nome do Cliente -->
                        <td>
                            <strong><?= htmlspecialchars($cliente['nome']) ?></strong>
                            <?php if ($cliente['cpf']): ?>
                                <br>
                                <small style="color: #999;">CPF: <?= formatarCPF($cliente['cpf']) ?></small>
                            <?php endif; ?>
                        </td>

                        <!-- E-mail -->
                        <td>
                            <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>" style="color: #667eea;">
                                <?= htmlspecialchars($cliente['email']) ?>
                            </a>
                        </td>

                        <!-- Telefone -->
                        <td>
                            <?= formatarTelefone($cliente['telefone']) ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            // Define a classe do badge baseado no status
                            $badge_class = 'badge-info';
                            if ($cliente['status'] == 'Ativo') $badge_class = 'badge-success';
                            if ($cliente['status'] == 'Inativo') $badge_class = 'badge-warning';
                            if ($cliente['status'] == 'Bloqueado') $badge_class = 'badge-danger';
                            ?>
                            <span class="badge <?= $badge_class ?>">
                                <?= $cliente['status'] ?>
                            </span>
                        </td>

                        <!-- Informações de Empréstimos -->
                        <td>
                            <?php if ($cliente['emprestimos_ativos'] > 0): ?>
                                <span class="badge badge-info">
                                    <?= $cliente['emprestimos_ativos'] ?> ativo(s)
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($cliente['emprestimos_atrasados'] > 0): ?>
                                <span class="badge badge-danger">
                                    <?= $cliente['emprestimos_atrasados'] ?> atrasado(s)
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($cliente['emprestimos_ativos'] == 0 && $cliente['emprestimos_atrasados'] == 0): ?>
                                <small style="color: #999;">Sem empréstimos</small>
                            <?php endif; ?>
                            
                            <br>
                            <small style="color: #999;">
                                Total: <?= $cliente['total_emprestimos'] ?> empréstimo(s)
                            </small>
                        </td>

                        <!-- Ações -->
                        <td style="text-align: center;">
                            <a href="cliente_editar.php?id=<?= $cliente['id'] ?>" 
                               class="btn btn-warning btn-small" 
                               title="Editar cliente">
                                ✏️ Editar
                            </a>
                            
                            <a href="cliente_emprestimos.php?id=<?= $cliente['id'] ?>" 
                               class="btn btn-info btn-small"
                               title="Ver empréstimos">
                                📋 Empréstimos
                            </a>
                            
                            <a href="cliente_excluir.php?id=<?= $cliente['id'] ?>" 
                               class="btn btn-danger btn-small confirm-delete"
                               title="Excluir cliente">
                                🗑️ Excluir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ========================================
             PAGINAÇÃO
             ======================================== -->
        <?php if ($total_paginas > 1): ?>
            <div style="
                display: flex; 
                justify-content: center; 
                align-items: center; 
                gap: 10px; 
                margin: 30px 0;
            ">
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=<?= $pagina_atual - 1 ?>&busca=<?= urlencode($filtro_busca) ?>&status=<?= urlencode($filtro_status) ?>" 
                       class="btn btn-secondary btn-small">
                        « Anterior
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($i == $pagina_atual): ?>
                        <span class="btn btn-small" style="background: #667eea;">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="?pagina=<?= $i ?>&busca=<?= urlencode($filtro_busca) ?>&status=<?= urlencode($filtro_status) ?>" 
                           class="btn btn-secondary btn-small">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?= $pagina_atual + 1 ?>&busca=<?= urlencode($filtro_busca) ?>&status=<?= urlencode($filtro_status) ?>" 
                       class="btn btn-secondary btn-small">
                        Próxima »
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhum cliente encontrado.</strong><br>
            <?php if (!empty($filtro_busca) || !empty($filtro_status)): ?>
                Tente ajustar os filtros de busca ou 
                <a href="clientes.php" style="color: #0c5460; text-decoration: underline;">limpar os filtros</a>.
            <?php else: ?>
                Comece <a href="cliente_novo.php" style="color: #0c5460; text-decoration: underline;">cadastrando um novo cliente</a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar clientes: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>
