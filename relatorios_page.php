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
 * - Livros disponíveis/indisponíveis
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
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo' AND data_devoluc