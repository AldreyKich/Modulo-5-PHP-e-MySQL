<?php
/**
 * Página Inicial do Sistema de Biblioteca
 * 
 * Exibe:
 * - Dashboard com estatísticas gerais
 * - Alertas de empréstimos atrasados
 * - Últimos livros cadastrados
 * - Top 5 livros mais emprestados
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

// Obtém a conexão com o banco de dados
$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    // ========================================
    // BUSCAR ESTATÍSTICAS GERAIS
    // ========================================
    
    /**
     * Esta query usa subqueries (subconsultas) para buscar
     * várias estatísticas em uma única consulta
     */
    $sql = "
        SELECT
            (SELECT COUNT(*) FROM livros) AS total_livros,
            (SELECT SUM(quantidade_total) FROM livros) AS total_exemplares,
            (SELECT SUM(quantidade_disponivel) FROM livros) AS exemplares_disponiveis,
            (SELECT COUNT(*) FROM clientes WHERE status = 'Ativo') AS total_clientes,
            (SELECT COUNT(*) FROM autores) AS total_autores,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo') AS emprestimos_ativos,
            (SELECT COUNT(*) FROM emprestimos WHERE status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados
    ";
    
    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch();

    ?>
    
    <!-- Título da Página -->
    <h1>🏠 Bem-vindo ao Sistema de Biblioteca</h1>
    
    <p style="font-size: 16px; color: #666; margin-bottom: 30px;">
        Gerencie livros, clientes e empréstimos de forma eficiente e organizada.
    </p>

    <!-- ========================================
         ALERTA DE EMPRÉSTIMOS ATRASADOS
         ======================================== -->
    <?php if ($stats['emprestimos_atrasados'] > 0): ?>
        <div class="alert alert-danger">
            <strong>⚠️ ATENÇÃO!</strong> 
            Existem <strong><?= $stats['emprestimos_atrasados'] ?></strong> empréstimo(s) em atraso.
            <a href="emprestimos.php?filtro=atrasados" style="color: #721c24; text-decoration: underline; margin-left: 10px;">
                Ver detalhes »
            </a>
        </div>
    <?php endif; ?>

    <!-- ========================================
         CARDS DE ESTATÍSTICAS
         ======================================== -->
    <div style="
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 20px; 
        margin: 30px 0;
    ">
        <!-- Card: Total de Livros -->
        <div style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        " onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?= number_format($stats['total_livros']) ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9; margin-bottom: 15px;">
                Títulos de Livros
            </div>
            <a href="livros.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">
                Ver catálogo completo →
            </a>
        </div>

        <!-- Card: Exemplares Disponíveis -->
        <div style="
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        " onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?= number_format($stats['exemplares_disponiveis']) ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9; margin-bottom: 15px;">
                Exemplares Disponíveis
            </div>
            <div style="font-size: 13px; opacity: 0.8;">
                de <?= number_format($stats['total_exemplares']) ?> no total
            </div>
        </div>

        <!-- Card: Clientes Ativos -->
        <div style="
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        " onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?= number_format($stats['total_clientes']) ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9; margin-bottom: 15px;">
                Clientes Ativos
            </div>
            <a href="clientes.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">
                Gerenciar clientes →
            </a>
        </div>

        <!-- Card: Empréstimos Ativos -->
        <div style="
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        " onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?= number_format($stats['emprestimos_ativos']) ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9; margin-bottom: 15px;">
                Empréstimos Ativos
            </div>
            <a href="emprestimos.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">
                Ver empréstimos →
            </a>
        </div>
    </div>

    <!-- ========================================
         AÇÕES RÁPIDAS
         ======================================== -->
    <div style="
        background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
        padding: 25px;
        border-radius: 12px;
        margin: 30px 0;
    ">
        <h2 style="margin: 0 0 20px 0; color: #333;">⚡ Ações Rápidas</h2>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="emprestimo_novo.php" class="btn btn-success">
                📝 Novo Empréstimo
            </a>
            <a href="cliente_novo.php" class="btn btn-info">
                👤 Cadastrar Cliente
            </a>
            <a href="livro_novo.php" class="btn btn-warning">
                📚 Cadastrar Livro
            </a>
            <a href="autor_novo.php" class="btn btn-secondary">
                ✍️ Cadastrar Autor
            </a>
        </div>
    </div>

    <!-- ========================================
         ÚLTIMOS LIVROS CADASTRADOS
         ======================================== -->
    <h2 style="margin-top: 40px;">📚 Últimos Livros Cadastrados</h2>
    
    <?php
    $sql = "
        SELECT 
            l.id, 
            l.titulo, 
            a.nome AS autor, 
            l.ano_publicacao, 
            l.quantidade_disponivel,
            l.quantidade_total
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        ORDER BY l.id DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->query($sql);
    $ultimos_livros = $stmt->fetchAll();

    if (count($ultimos_livros) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Ano</th>
                    <th>Disponibilidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimos_livros as $livro): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($livro['titulo']) ?></strong></td>
                        <td><?= htmlspecialchars($livro['autor']) ?></td>
                        <td><?= $livro['ano_publicacao'] ?></td>
                        <td>
                            <?php if ($livro['quantidade_disponivel'] > 0): ?>
                                <span class="badge badge-success">
                                    <?= $livro['quantidade_disponivel'] ?> de <?= $livro['quantidade_total'] ?> disponíveis
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    Indisponível
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="livro_detalhes.php?id=<?= $livro['id'] ?>" class="btn btn-small">
                                Ver Detalhes
                            </a>
                            <?php if ($livro['quantidade_disponivel'] > 0): ?>
                                <a href="emprestimo_novo.php?livro_id=<?= $livro['id'] ?>" class="btn btn-success btn-small">
                                    Emprestar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum livro cadastrado ainda.</p>
    <?php endif; ?>

    <!-- ========================================
         TOP 5 LIVROS MAIS EMPRESTADOS
         ======================================== -->
    <h2 style="margin-top: 40px;">🏆 Top 5 Livros Mais Emprestados</h2>
    
    <?php
    $sql = "
        SELECT 
            l.id,
            l.titulo, 
            a.nome AS autor, 
            COUNT(e.id) AS total_emprestimos
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        LEFT JOIN emprestimos e ON l.id = e.livro_id
        GROUP BY l.id
        HAVING total_emprestimos > 0
        ORDER BY total_emprestimos DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->query($sql);
    $top_livros = $stmt->fetchAll();

    if (count($top_livros) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px; text-align: center;">Posição</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th style="width: 150px; text-align: center;">Empréstimos</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $posicao = 1;
                foreach ($top_livros as $livro): 
                    // Define cor da medalha baseado na posição
                    $cores_medalhas = [
                        1 => '#FFD700', // Ouro
                        2 => '#C0C0C0', // Prata
                        3 => '#CD7F32', // Bronze
                    ];
                    $cor = isset($cores_medalhas[$posicao]) ? $cores_medalhas[$posicao] : '#667eea';
                ?>
                    <tr>
                        <td style="text-align: center; font-weight: bold; color: <?= $cor ?>; font-size: 24px;">
                            #<?= $posicao ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($livro['titulo']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($livro['autor']) ?></td>
                        <td style="text-align: center;">
                            <span class="badge badge-info" style="font-size: 14px; padding: 6px 12px;">
                                <?= $livro['total_emprestimos'] ?> empréstimos
                            </span>
                        </td>
                    </tr>
                <?php 
                    $posicao++;
                endforeach; 
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum empréstimo registrado ainda.</p>
    <?php endif; ?>

<?php

} catch (PDOException $e) {
    // Em caso de erro, exibe mensagem amigável
    exibirMensagem('erro', 'Erro ao carregar os dados do sistema: ' . $e->getMessage());
}

// Inclui o rodapé
require_once 'includes/footer.php';
?>