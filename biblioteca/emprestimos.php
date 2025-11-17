<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php'; // header já incluído uma única vez

// ========================================
// EXIBIÇÃO DE MENSAGENS (apenas UM local)
// ========================================
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $tipo = $_GET['msg_tipo'] ?? 'info';

    // Mapear tipo para classes Bootstrap
    $classe_alerta = match ($tipo) {
        'sucesso' => 'alert alert-success alert-dismissible fade show',
        'erro' => 'alert alert-danger alert-dismissible fade show',
        'aviso' => 'alert alert-warning alert-dismissible fade show',
        default => 'alert alert-info alert-dismissible fade show'
    };

    // Exibe o HTML da mensagem (interpretando tags como <br>, <strong> etc.)
    echo "
    <div class='$classe_alerta' role='alert'
         style='white-space: normal; line-height: 1.6; font-size: 15px; border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;'>
        " . html_entity_decode($msg) . "
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>
    </div>";
}

// ========================================
// CONEXÃO COM O BANCO
// ========================================
$db = Database::getInstance();
$pdo = $db->getConnection();

// ========================================
// FILTROS (opcional - mantive o seu padrão anterior)
// ========================================
$filtro_status = isset($_GET['filtro']) ? limparInput($_GET['filtro']) : 'todos';
$filtro_busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// ========================================
// CONSULTAR EMPRÉSTIMOS (exemplo simples)
// - Ajuste conforme sua query/paginação original
// ========================================
try {
    $where_clauses = [];
    $params = [];

    if ($filtro_status == 'ativos') {
        $where_clauses[] = "e.status = 'Ativo'";
    } elseif ($filtro_status == 'atrasados') {
        $where_clauses[] = "e.status = 'Ativo' AND e.data_devolucao_prevista < CURDATE()";
    } elseif ($filtro_status == 'devolvidos') {
        $where_clauses[] = "e.status = 'Devolvido'";
    }

    if (!empty($filtro_busca)) {
        $where_clauses[] = "(c.nome LIKE :busca OR l.titulo LIKE :busca)";
        $params['busca'] = "%$filtro_busca%";
    }

    $where_sql = count($where_clauses) ? ' WHERE ' . implode(' AND ', $where_clauses) : '';

    $sql = "
        SELECT 
            e.id,
            e.data_emprestimo,
            e.data_devolucao_prevista,
            e.data_devolucao_real,
            e.status,
            e.multa,
            c.nome AS cliente_nome,
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
    ";

    $stmt = $pdo->prepare($sql);
    // bind dos parâmetros dinâmicos
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':valor_multa', defined('VALOR_MULTA_DIA') ? VALOR_MULTA_DIA : 2, PDO::PARAM_INT);
    $stmt->execute();
    $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar empréstimos: ' . $e->getMessage());
    $emprestimos = [];
}
?>

<div class="container mt-4">
    <h2 class="mb-4 text-center">📚 Gerenciamento de Empréstimos</h2>

    <div class="mb-3 text-end">
        <a href="emprestimo_novo.php" class="btn btn-success">➕ Novo Empréstimo</a>
    </div>

    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Livro</th>
                <th>Data Empréstimo</th>
                <th>Devolução Prevista</th>
                <th>Status</th>
                <th>Multa</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($emprestimos)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">Nenhum empréstimo registrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($emprestimos as $emp): 
                    $dias_atraso = max(0, (int)$emp['dias_atraso']);
                    $esta_atrasado = $emp['status'] === 'Ativo' && $dias_atraso > 0;
                ?>
                    <tr <?= $esta_atrasado ? 'style="background:#fff7f7;"' : '' ?>>
                        <td><?= htmlspecialchars($emp['id']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($emp['cliente_nome']) ?></strong><br>
                            <small class="text-muted"><?= formatarTelefone($emp['cliente_telefone']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($emp['livro_titulo']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($emp['autor_nome']) ?></small>
                        </td>
                        <td><?= formatarData($emp['data_emprestimo']) ?></td>
                        <td>
                            <?= formatarData($emp['data_devolucao_prevista']) ?>
                            <?php if ($esta_atrasado): ?>
                                <br><span class="badge bg-danger"><?= $dias_atraso ?> dia(s) de atraso</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['status'] === 'Devolvido'): ?>
                                <span class="badge bg-success">Devolvido</span>
                            <?php elseif ($esta_atrasado): ?>
                                <span class="badge bg-danger">Atrasado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Ativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= ($emp['multa_calculada'] > 0) ? formatarMoeda($emp['multa_calculada']) : '<span class="text-success">Sem multa</span>' ?>
                        </td>
                        <td>
                            <?php if ($emp['status'] === 'Ativo'): ?>
                                <a href="emprestimo_devolver.php?id=<?= $emp['id'] ?>" class="btn btn-success btn-sm"
                                   onclick="return confirm('Confirmar devolução do empréstimo #<?= $emp['id'] ?>?')">✅ Devolver</a>
                                <a href="emprestimo_renovar.php?id=<?= $emp['id'] ?>" class="btn btn-info btn-sm">🔄 Renovar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                            <a href="emprestimo_excluir.php?id=<?= $emp['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Excluir empréstimo #<?= $emp['id'] ?>?')">🗑️ Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
