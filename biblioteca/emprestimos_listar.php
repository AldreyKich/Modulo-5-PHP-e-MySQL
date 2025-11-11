<?php
/**
 * Listagem de Empréstimos
 * 
 * Exibe todos os empréstimos com filtros por status,
 * cliente e livro. Permite ações como devolver e renovar.
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// ========================================
// FILTROS
// ========================================
$filtro_status = isset($_GET['filtro']) ? limparInput($_GET['filtro']) : 'todos';
$filtro_busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// ========================================
// PAGINAÇÃO
// ========================================
$por_pagina = REGISTROS_POR_PAGINA;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

try {
    // ========================================
    // CONSTRUIR QUERY COM FILTROS
    // ========================================
    $where_clauses = [];
    $params = [];

    // Filtro por status
    if ($filtro_status == 'ativos') {
        $where_clauses[] = "e.status = 'Ativo'";
    } elseif ($filtro_status == 'atrasados') {
        $where_clauses[] = "e.status = 'Ativo' AND e.data_devolucao_prevista < CURDATE()";
    } elseif ($filtro_status == 'devolvidos') {
        $where_clauses[] = "e.status = 'Devolvido'";
    }

    // Filtro por busca (cliente ou livro)
    if (!empty($filtro_busca)) {
        $where_clauses[] = "(c.nome LIKE :busca OR l.titulo LIKE :busca)";
        $params['busca'] = "%$filtro_busca%";
    }

    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

    // ========================================
    // CONTAR REGISTROS
    // ========================================
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM emprestimos e
        INNER JOIN clientes c ON e.cliente_id = c.id
        INNER JOIN livros l ON e.livro_id = l.id
        $where_sql
    ");
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $por_pagina);

    // ========================================
    // BUSCAR EMPRÉSTIMOS
    // ========================================
    $sql = "
        SELECT 
            e.*,
            c.nome AS cliente_nome,
            c.email AS cliente_email,
            c.telefone AS cliente_telefone,
            l.titulo AS livro_titulo,
            a.nome AS autor_nome,
            DATEDIFF(CURDATE(), e.data_devolucao_prevista) AS dias_atraso,
            CASE 
                WHEN e.status = 'Ativo' AND CURDATE() > e.data_devolucao_prevista 
                THEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) * :valor_multa
                ELSE e.multa
            END AS multa_calculada
        FROM emprestimos e
        INNER JOIN clientes c ON e.cliente_id = c.id
        INNER JOIN livros l ON e.livro_id = l.id
        INNER JOIN autores a ON l.autor_id = a.id
        $where_sql
        ORDER BY 
            CASE 
                WHEN e.status = 'Ativo' AND e.data_devolucao_prevista < CURDATE() THEN 1
                WHEN e.status = 'Ativo' THEN 2
                ELSE 3
            END,
            e.data_emprestimo DESC
        LIMIT :limite OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':valor_multa', VALOR_MULTA_DIA);
    $stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $emprestimos = $stmt->fetchAll();

    // ========================================
    // ESTATÍSTICAS RÁPIDAS
    // ========================================
    $sql = "
        SELECT
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo') AS ativos,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS atrasados,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Devolvido') AS devolvidos
    ";
    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch();

?>

    <!-- Título da Página -->
    <h1>📋 Gerenciamento de Empréstimos</h1>

    <!-- Botão para novo empréstimo -->
    <div style="margin-bottom: 25px;">
        <a href="emprestimo_novo.php" class="btn btn-success">
            ➕ Registrar Novo Empréstimo
        </a>
    </div>

    <!-- ========================================
         ESTATÍSTICAS RÁPIDAS
         ======================================== -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;">
            <div style="font-size: 32px; font-weight: bold; color: #2196F3;">
                <?= $stats['ativos'] ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Empréstimos Ativos</div>
        </div>

        <div style="background: #ffebee; padding: 20px; border-radius: 8px; border-left: 4px solid #f44336;">
            <div style="font-size: 32px; font-weight: bold; color: #f44336;">
                <?= $stats['atrasados'] ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Empréstimos Atrasados</div>
        </div>

        <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; border-left: 4px solid #4CAF50;">
            <div style="font-size: 32px; font-weight: bold; color: #4CAF50;">
                <?= $stats['devolvidos'] ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Devolvidos</div>
        </div>
    </div>

    <!-- ========================================
         FILTROS
         ======================================== -->
    <div class="card">
        <h3>🔍 Filtros</h3>
        
        <!-- Filtros Rápidos -->
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
            <a href="emprestimos.php?filtro=todos" 
               class="btn btn-small <?= $filtro_status == 'todos' ? '' : 'btn-secondary' ?>">
                Todos
            </a>
            <a href="emprestimos.php?filtro=ativos" 
               class="btn btn-small <?= $filtro_status == 'ativos' ? '' : 'btn-secondary' ?>">
                Ativos
            </a>
            <a href="emprestimos.php?filtro=atrasados" 
               class="btn btn-small btn-danger <?= $filtro_status == 'atrasados' ? '' : 'btn-secondary' ?>">
                Atrasados
            </a>
            <a href="emprestimos.php?filtro=devolvidos" 
               class="btn btn-small <?= $filtro_status == 'devolvidos' ? '' : 'btn-secondary' ?>">
                Devolvidos
            </a>
        </div>

        <!-- Busca por texto -->
        <form method="GET" action="emprestimos.php" style="background: transparent; padding: 0;">
            <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro_status) ?>">
            <div class="form-group">
                <label for="busca">Buscar por cliente ou livro:</label>
                <div style="display: flex; gap: 10px;">
                    <input 
                        type="text" 
                        id="busca" 
                        name="busca" 
                        value="<?= htmlspecialchars($filtro_busca) ?>"
                        placeholder="Digite o nome do cliente ou título do livro..."
                        style="flex: 1;"
                    >
                    <button type="submit" class="btn">Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Informação sobre resultados -->
    <p style="color: #666; margin: 20px 0;">
        Exibindo <?= count($emprestimos) ?> de <?= $total_registros ?> empréstimo(s)
    </p>

    <!-- ========================================
         TABELA DE EMPRÉSTIMOS
         ======================================== -->
    <?php if (count($emprestimos) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Livro</th>
                    <th>Data Empréstimo</th>
                    <th>Devolução Prevista</th>
                    <th>Status</th>
                    <th>Multa</th>
                    <th style="width: 220px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprestimos as $emp): 
                    $dias_atraso = max(0, $emp['dias_atraso']);
                    $esta_atrasado = $emp['status'] == 'Ativo' && $dias_atraso > 0;
                ?>
                    <tr style="<?= $esta_atrasado ? 'background-color: #ffebee;' : '' ?>">
                        <!-- ID -->
                        <td><strong>#<?= $emp['id'] ?></strong></td>

                        <!-- Cliente -->
                        <td>
                            <strong><?= htmlspecialchars($emp['cliente_nome']) ?></strong>
                            <br>
                            <small style="color: #999;">
                                <?= formatarTelefone($emp['cliente_telefone']) ?>
                            </small>
                        </td>

                        <!-- Livro -->
                        <td>
                            <strong><?= htmlspecialchars($emp['livro_titulo']) ?></strong>
                            <br>
                            <small style="color: #999;">
                                <?= htmlspecialchars($emp['autor_nome']) ?>
                            </small>
                        </td>

                        <!-- Data Empréstimo -->
                        <td><?= formatarData($emp['data_emprestimo']) ?></td>

                        <!-- Devolução Prevista -->
                        <td>
                            <?= formatarData($emp['data_devolucao_prevista']) ?>
                            <?php if ($esta_atrasado): ?>
                                <br>
                                <span class="badge badge-danger">
                                    <?= $dias_atraso ?> dia(s) de atraso
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            $badge_class = 'badge-info';
                            if ($emp['status'] == 'Devolvido') $badge_class = 'badge-success';
                            if ($esta_atrasado) $badge_class = 'badge-danger';
                            ?>
                            <span class="badge <?= $badge_class ?>">
                                <?= $esta_atrasado ? 'ATRASADO' : $emp['status'] ?>
                            </span>
                        </td>

                        <!-- Multa -->
                        <td>
                            <?php if ($emp['multa_calculada'] > 0): ?>
                                <span style="color: #f44336; font-weight: bold;">
                                    <?= formatarMoeda($emp['multa_calculada']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #4CAF50;">Sem multa</span>
                            <?php endif; ?>
                        </td>

                        <!-- Ações -->
                        <td style="text-align: center;">
                            <?php if ($emp['status'] == 'Ativo'): ?>
                                <a href="emprestimo_devolver.php?id=<?= $emp['id'] ?>" 
                                   class="btn btn-success btn-small"
                                   title="Registrar devolução">
                                    ✅ Devolver
                                </a>
                                
                                <?php if (!$esta_atrasado): ?>
                                    <a href="emprestimo_renovar.php?id=<?= $emp['id'] ?>" 
                                       class="btn btn-info btn-small"
                                       title="Renovar empréstimo">
                                        🔄 Renovar
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #999; font-size: 12px;">
                                    Devolvido em<br>
                                    <?= formatarData($emp['data_devolucao_real']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ========================================
             PAGINAÇÃO
             ======================================== -->
        <?php if ($total_paginas > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin: 30px 0;">
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=<?= $pagina_atual - 1 ?>&filtro=<?= $filtro_status ?>&busca=<?= urlencode($filtro_busca) ?>" 
                       class="btn btn-secondary btn-small">
                        « Anterior
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($i == $pagina_atual): ?>
                        <span class="btn btn-small"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?pagina=<?= $i ?>&filtro=<?= $filtro_status ?>&busca=<?= urlencode($filtro_busca) ?>" 
                           class="btn btn-secondary btn-small">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?= $pagina_atual + 1 ?>&filtro=<?= $filtro_status ?>&busca=<?= urlencode($filtro_busca) ?>" 
                       class="btn btn-secondary btn-small">
                        Próxima »
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhum empréstimo encontrado.</strong><br>
            <?php if (!empty($filtro_busca) || $filtro_status != 'todos'): ?>
                Tente ajustar os filtros de busca.
            <?php else: ?>
                Comece <a href="emprestimo_novo.php" style="color: #0c5460; text-decoration: underline;">registrando um novo empréstimo</a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar empréstimos: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>
