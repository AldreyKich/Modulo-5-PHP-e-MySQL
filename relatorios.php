<?php
/**
 * Página de Relatórios
 * 
 * Exibe diversos relatórios gerenciais do sistema:
 * - Estatísticas gerais
 * - Empréstimos por período
 * - Livros mais emprestados
 * - Clientes mais ativos
 * - Situação financeira (multas)
 * - Empréstimos por categoria
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// ========================================
// FILTROS DE PERÍODO
// ========================================
$periodo = isset($_GET['periodo']) ? limparInput($_GET['periodo']) : 'mes_atual';
$data_inicio = '';
$data_fim = '';

// Define as datas baseado no período selecionado
switch ($periodo) {
    case 'hoje':
        $data_inicio = date('Y-m-d');
        $data_fim = date('Y-m-d');
        break;
    case 'semana':
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
        $data_fim = date('Y-m-d');
        break;
    case 'mes_atual':
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-t');
        break;
    case 'mes_passado':
        $data_inicio = date('Y-m-01', strtotime('first day of last month'));
        $data_fim = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'ano_atual':
        $data_inicio = date('Y-01-01');
        $data_fim = date('Y-12-31');
        break;
    case 'tudo':
        $data_inicio = '2000-01-01';
        $data_fim = date('Y-m-d');
        break;
    case 'personalizado':
        $data_inicio = isset($_GET['data_inicio']) ? limparInput($_GET['data_inicio']) : date('Y-m-01');
        $data_fim = isset($_GET['data_fim']) ? limparInput($_GET['data_fim']) : date('Y-m-d');
        break;
}

try {
?>

    <!-- Título da Página -->
    <h1>📊 Relatórios Gerenciais</h1>

    <!-- ========================================
         SELETOR DE PERÍODO
         ======================================== -->
    <div class="card">
        <h3>📅 Selecione o Período</h3>
        <form method="GET" action="relatorios.php" style="background: transparent; padding: 0;">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="periodo">Período:</label>
                        <select id="periodo" name="periodo" onchange="toggleCustomDates()">
                            <option value="hoje" <?= $periodo == 'hoje' ? 'selected' : '' ?>>Hoje</option>
                            <option value="semana" <?= $periodo == 'semana' ? 'selected' : '' ?>>Última Semana</option>
                            <option value="mes_atual" <?= $periodo == 'mes_atual' ? 'selected' : '' ?>>Mês Atual</option>
                            <option value="mes_passado" <?= $periodo == 'mes_passado' ? 'selected' : '' ?>>Mês Passado</option>
                            <option value="ano_atual" <?= $periodo == 'ano_atual' ? 'selected' : '' ?>>Ano Atual</option>
                            <option value="tudo" <?= $periodo == 'tudo' ? 'selected' : '' ?>>Todo o Período</option>
                            <option value="personalizado" <?= $periodo == 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
                        </select>
                    </div>
                </div>
                
                <div class="col" id="customDates" style="display: <?= $periodo == 'personalizado' ? 'block' : 'none' ?>;">
                    <div class="form-group">
                        <label for="data_inicio">De:</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= $data_inicio ?>">
                    </div>
                </div>
                
                <div class="col" id="customDates2" style="display: <?= $periodo == 'personalizado' ? 'block' : 'none' ?>;">
                    <div class="form-group">
                        <label for="data_fim">Até:</label>
                        <input type="date" id="data_fim" name="data_fim" value="<?= $data_fim ?>">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-info">🔍 Gerar Relatórios</button>
            <button type="button" onclick="window.print()" class="btn btn-secondary">🖨️ Imprimir</button>
        </form>
        
        <p style="margin-top: 15px; color: #666;">
            <strong>Período selecionado:</strong> 
            <?= formatarData($data_inicio) ?> até <?= formatarData($data_fim) ?>
        </p>
    </div>

    <?php
    // ========================================
    // RELATÓRIO 1: ESTATÍSTICAS GERAIS
    // ========================================
    $sql = "
        SELECT
            (SELECT COUNT(*) FROM livros) AS total_livros,
            (SELECT SUM(quantidade_total) FROM livros) AS total_exemplares,
            (SELECT SUM(quantidade_disponivel) FROM livros) AS exemplares_disponiveis,
            (SELECT COUNT(*) FROM clientes WHERE status = 'Ativo') AS clientes_ativos,
            (SELECT COUNT(*) FROM autores) AS total_autores,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo') AS emprestimos_ativos,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados,
            (SELECT COUNT(*) FROM emprestimos WHERE data_emprestimo BETWEEN :data_inicio AND :data_fim) AS emprestimos_periodo,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Devolvido' AND data_devolucao_real BETWEEN :data_inicio2 AND :data_fim2) AS devolucoes_periodo,
            (SELECT SUM(multa) FROM emprestimos WHERE data_devolucao_real BETWEEN :data_inicio3 AND :data_fim3) AS total_multas
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim,
        'data_inicio2' => $data_inicio,
        'data_fim2' => $data_fim,
        'data_inicio3' => $data_inicio,
        'data_fim3' => $data_fim
    ]);
    $stats = $stmt->fetch();
    ?>

    <!-- ========================================
         ESTATÍSTICAS GERAIS
         ======================================== -->
    <h2>📈 Estatísticas Gerais do Sistema</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;">
            <div style="font-size: 32px; font-weight: bold; color: #2196F3;">
                <?= number_format($stats['total_livros']) ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Total de Livros</div>
        </div>
        
        <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; border-left: 4px solid #4CAF50;">
            <div style="font-size: 32px; font-weight: bold; color: #4CAF50;">
                <?= number_format($stats['exemplares_disponiveis']) ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Exemplares Disponíveis</div>
        </div>
        
        <div style="background: #fff3e0; padding: 20px; border-radius: 8px; border-left: 4px solid #ff9800;">
            <div style="font-size: 32px; font-weight: bold; color: #ff9800;">
                <?= number_format($stats['clientes_ativos']) ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Clientes Ativos</div>
        </div>
        
        <div style="background: #fce4ec; padding: 20px; border-radius: 8px; border-left: 4px solid #e91e63;">
            <div style="font-size: 32px; font-weight: bold; color: #e91e63;">
                <?= number_format($stats['emprestimos_ativos']) ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Empréstimos Ativos</div>
        </div>
    </div>

    <!-- ========================================
         ESTATÍSTICAS DO PERÍODO
         ======================================== -->
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 30px;">
        <h3 style="color: white; margin-bottom: 20px;">📊 Estatísticas do Período Selecionado</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            <div>
                <div style="font-size: 36px; font-weight: bold;"><?= number_format($stats['emprestimos_periodo']) ?></div>
                <div style="opacity: 0.9;">Empréstimos Realizados</div>
            </div>
            <div>
                <div style="font-size: 36px; font-weight: bold;"><?= number_format($stats['devolucoes_periodo']) ?></div>
                <div style="opacity: 0.9;">Devoluções</div>
            </div>
            <div>
                <div style="font-size: 36px; font-weight: bold;"><?= number_format($stats['emprestimos_atrasados']) ?></div>
                <div style="opacity: 0.9;">Empréstimos Atrasados</div>
            </div>
            <div>
                <div style="font-size: 36px; font-weight: bold;"><?= formatarMoeda($stats['total_multas'] ?? 0) ?></div>
                <div style="opacity: 0.9;">Total em Multas</div>
            </div>
        </div>
    </div>

    <?php
    // ========================================
    // RELATÓRIO 2: TOP 10 LIVROS MAIS EMPRESTADOS
    // ========================================
    $sql = "
        SELECT 
            l.titulo,
            a.nome AS autor,
            l.categoria,
            COUNT(e.id) AS total_emprestimos,
            SUM(CASE WHEN e.status = 'Ativo' THEN 1 ELSE 0 END) AS emprestimos_ativos
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        LEFT JOIN emprestimos e ON l.id = e.livro_id
            AND e.data_emprestimo BETWEEN :data_inicio AND :data_fim
        GROUP BY l.id
        HAVING total_emprestimos > 0
        ORDER BY total_emprestimos DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['data_inicio' => $data_inicio, 'data_fim' => $data_fim]);
    $top_livros = $stmt->fetchAll();
    ?>

    <h2>🏆 Top 10 Livros Mais Emprestados</h2>
    <?php if (count($top_livros) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 100px;">Posição</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Categoria</th>
                    <th style="width: 150px; text-align: center;">Total de Empréstimos</th>
                    <th style="width: 150px; text-align: center;">Atualmente Emprestados</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $posicao = 1;
                foreach ($top_livros as $livro): 
                    $medalha = '';
                    $cor_fundo = '';
                    if ($posicao == 1) {
                        $medalha = '🥇';
                        $cor_fundo = 'background: #fff8e1;';
                    } elseif ($posicao == 2) {
                        $medalha = '🥈';
                        $cor_fundo = 'background: #f5f5f5;';
                    } elseif ($posicao == 3) {
                        $medalha = '🥉';
                        $cor_fundo = 'background: #fff3e0;';
                    }
                ?>
                    <tr style="<?= $cor_fundo ?>">
                        <td style="text-align: center; font-size: 24px; font-weight: bold;">
                            <?= $medalha ?> #<?= $posicao ?>
                        </td>
                        <td><strong><?= htmlspecialchars($livro['titulo']) ?></strong></td>
                        <td><?= htmlspecialchars($livro['autor']) ?></td>
                        <td>
                            <?php if ($livro['categoria']): ?>
                                <span class="badge badge-info"><?= htmlspecialchars($livro['categoria']) ?></span>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-success" style="font-size: 16px; padding: 8px 12px;">
                                <?= $livro['total_emprestimos'] ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <?= $livro['emprestimos_ativos'] ?>
                        </td>
                    </tr>
                <?php 
                    $posicao++;
                endforeach; 
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhum empréstimo registrado</strong><br>
            Não há empréstimos no período selecionado.
        </div>
    <?php endif; ?>

    <?php
    // ========================================
    // RELATÓRIO 3: TOP 10 CLIENTES MAIS ATIVOS
    // ========================================
    $sql = "
        SELECT 
            c.nome,
            c.email,
            COUNT(e.id) AS total_emprestimos,
            SUM(CASE WHEN e.status = 'Ativo' THEN 1 ELSE 0 END) AS emprestimos_ativos,
            SUM(e.multa) AS total_multas
        FROM clientes c
        LEFT JOIN emprestimos e ON c.id = e.cliente_id
            AND e.data_emprestimo BETWEEN :data_inicio AND :data_fim
        GROUP BY c.id
        HAVING total_emprestimos > 0
        ORDER BY total_emprestimos DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['data_inicio' => $data_inicio, 'data_fim' => $data_fim]);
    $top_clientes = $stmt->fetchAll();
    ?>

    <h2 style="margin-top: 40px;">👥 Top 10 Clientes Mais Ativos</h2>
    <?php if (count($top_clientes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Posição</th>
                    <th>Cliente</th>
                    <th>E-mail</th>
                    <th style="width: 120px; text-align: center;">Total de Empréstimos</th>
                    <th style="width: 120px; text-align: center;">Empréstimos Ativos</th>
                    <th style="width: 120px; text-align: center;">Total em Multas</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $posicao = 1;
                foreach ($top_clientes as $cliente): 
                ?>
                    <tr>
                        <td style="text-align: center; font-weight: bold; font-size: 18px;">
                            #<?= $posicao ?>
                        </td>
                        <td><strong><?= htmlspecialchars($cliente['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($cliente['email']) ?></td>
                        <td style="text-align: center;">
                            <span class="badge badge-info" style="font-size: 14px;">
                                <?= $cliente['total_emprestimos'] ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <?= $cliente['emprestimos_ativos'] ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($cliente['total_multas'] > 0): ?>
                                <span style="color: #f44336; font-weight: bold;">
                                    <?= formatarMoeda($cliente['total_multas']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #4CAF50;">R$ 0,00</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    $posicao++;
                endforeach; 
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhum empréstimo registrado</strong><br>
            Não há empréstimos no período selecionado.
        </div>
    <?php endif; ?>

    <?php
    // ========================================
    // RELATÓRIO 4: EMPRÉSTIMOS POR CATEGORIA
    // ========================================
    $sql = "
        SELECT 
            l.categoria,
            COUNT(e.id) AS total_emprestimos,
            SUM(CASE WHEN e.status = 'Ativo' THEN 1 ELSE 0 END) AS emprestimos_ativos
        FROM emprestimos e
        INNER JOIN livros l ON e.livro_id = l.id
        WHERE e.data_emprestimo BETWEEN :data_inicio AND :data_fim
            AND l.categoria IS NOT NULL
        GROUP BY l.categoria
        ORDER BY total_emprestimos DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['data_inicio' => $data_inicio, 'data_fim' => $data_fim]);
    $categorias = $stmt->fetchAll();
    ?>

    <h2 style="margin-top: 40px;">📚 Empréstimos por Categoria</h2>
    <?php if (count($categorias) > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <?php foreach ($categorias as $cat): ?>
                <div class="card" style="text-align: center;">
                    <h3 style="margin: 0 0 15px 0; color: #667eea;">
                        <?= htmlspecialchars($cat['categoria']) ?>
                    </h3>
                    <div style="font-size: 36px; font-weight: bold; color: #764ba2; margin: 10px 0;">
                        <?= $cat['total_emprestimos'] ?>
                    </div>
                    <p style="margin: 5px 0; color: #666;">Total de empréstimos</p>
                    <p style="margin: 5px 0;">
                        <span class="badge badge-info">
                            <?= $cat['emprestimos_ativos'] ?> atualmente emprestados
                        </span>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhuma categoria</strong><br>
            Não há empréstimos com categoria definida no período.
        </div>
    <?php endif; ?>

    <?php
    // ========================================
    // RELATÓRIO 5: SITUAÇÃO FINANCEIRA (MULTAS)
    // ========================================
    $sql = "
        SELECT 
            DATE_FORMAT(e.data_devolucao_real, '%Y-%m') AS mes,
            COUNT(*) AS total_devolucoes,
            SUM(e.multa) AS total_multas,
            SUM(CASE WHEN e.multa > 0 THEN 1 ELSE 0 END) AS devolucoes_com_multa
        FROM emprestimos e
        WHERE e.status = 'Devolvido'
            AND e.data_devolucao_real BETWEEN :data_inicio AND :data_fim
        GROUP BY mes
        ORDER BY mes DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['data_inicio' => $data_inicio, 'data_fim' => $data_fim]);
    $multas_mes = $stmt->fetchAll();
    ?>

    <h2 style="margin-top: 40px;">💰 Situação Financeira - Multas</h2>
    <?php if (count($multas_mes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Mês/Ano</th>
                    <th style="width: 150px; text-align: center;">Total de Devoluções</th>
                    <th style="width: 150px; text-align: center;">Devoluções com Multa</th>
                    <th style="width: 150px; text-align: center;">Total Arrecadado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($multas_mes as $multa): ?>
                    <tr>
                        <td><strong><?= date('m/Y', strtotime($multa['mes'] . '-01')) ?></strong></td>
                        <td style="text-align: center;"><?= $multa['total_devolucoes'] ?></td>
                        <td style="text-align: center;">
                            <span class="badge badge-warning">
                                <?= $multa['devolucoes_com_multa'] ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <strong style="color: <?= $multa['total_multas'] > 0 ? '#f44336' : '#4CAF50' ?>;">
                                <?= formatarMoeda($multa['total_multas'] ?? 0) ?>
                            </strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f5f5f5; font-weight: bold;">
                    <td>TOTAL GERAL:</td>
                    <td style="text-align: center;">
                        <?= array_sum(array_column($multas_mes, 'total_devolucoes')) ?>
                    </td>
                    <td style="text-align: center;">
                        <?= array_sum(array_column($multas_mes, 'devolucoes_com_multa')) ?>
                    </td>
                    <td style="text-align: center; color: #f44336; font-size: 18px;">
                        <?php
                        $total_geral = array_sum(array_column($multas_mes, 'total_multas'));
                        echo formatarMoeda($total_geral);
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Nenhuma multa</strong><br>
            Não há devoluções com multa no período selecionado.
        </div>
    <?php endif; ?>

    <!-- ========================================
         RESUMO FINAL
         ======================================== -->
    <div class="card" style="background: #f5f5f5; margin-top: 40px;">
        <h3>📋 Resumo do Relatório</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <p style="margin: 5px 0;"><strong>Data de geração:</strong></p>
                <p style="color: #666;"><?= date('d/m/Y H:i:s') ?></p>
            </div>
            <div>
                <p style="margin: 5px 0;"><strong>Período analisado:</strong></p>
                <p style="color: #666;"><?= formatarData($data_inicio) ?> até <?= formatarData($data_fim) ?></p>
            </div>
            <div>
                <p style="margin: 5px 0;"><strong>Sistema:</strong></p>
                <p style="color: #666;"><?= NOME_BIBLIOTECA ?></p>
            </div>
            <div>
                <p style="margin: 5px 0;"><strong>Versão:</strong></p>
                <p style="color: #666;"><?= VERSAO_SISTEMA ?></p>
            </div>
        </div>
    </div>

    <!-- ========================================
         JAVASCRIPT
         ======================================== -->
    <script>
    /**
     * Mostra/esconde campos de data personalizada
     */
    function toggleCustomDates() {
        const periodo = document.getElementById('periodo').value;
        const customDates = document.getElementById('customDates');
        const customDates2 = document.getElementById('customDates2');
        
        if (periodo === 'personalizado') {
            customDates.style.display = 'block';
            customDates2.style.display = 'block';
        } else {
            customDates.style.display = 'none';
            customDates2.style.display = 'none';
        }
    }

    /**
     * Configurações para impressão
     * Oculta elementos desnecessários antes de imprimir
     */
    window.addEventListener('beforeprint', function() {
        document.querySelector('nav').style.display = 'none';
        document.querySelector('footer').style.display = 'none';
        document.querySelectorAll('button').forEach(btn => btn.style.display = 'none');
        document.querySelectorAll('.btn').forEach(btn => btn.style.display = 'none');
    });

    /**
     * Restaura elementos após impressão
     */
    window.addEventListener('afterprint', function() {
        document.querySelector('nav').style.display = 'block';
        document.querySelector('footer').style.display = 'block';
        document.querySelectorAll('button').forEach(btn => btn.style.display = 'inline-block');
        document.querySelectorAll('.btn').forEach(btn => btn.style.display = 'inline-block');
    });
    </script>

<?php

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao gerar relatórios: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>