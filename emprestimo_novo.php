<?php
/**
 * Formulário de Novo Empréstimo
 * 
 * Permite registrar um novo empréstimo com validações de:
 * - Disponibilidade do livro
 * - Limite de empréstimos por cliente
 * - Cliente sem atrasos
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

try {
    // ========================================
    // BUSCAR CLIENTES APTOS
    // ========================================
    $sql = "
        SELECT 
            c.id, 
            c.nome, 
            c.email,
            c.status,
            (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id AND status = 'Ativo') AS emprestimos_ativos,
            (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id AND status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados
        FROM clientes c
        WHERE c.status = 'Ativo'
        ORDER BY c.nome
    ";
    $stmt = $pdo->query($sql);
    $clientes = $stmt->fetchAll();

    // ========================================
    // BUSCAR LIVROS DISPONÍVEIS
    // ========================================
    $sql = "
        SELECT 
            l.id, 
            l.titulo, 
            a.nome AS autor, 
            l.quantidade_disponivel,
            l.localizacao
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        WHERE l.quantidade_disponivel > 0
        ORDER BY l.titulo
    ";
    $stmt = $pdo->query($sql);
    $livros = $stmt->fetchAll();

    // Livro pré-selecionado (se vier da URL)
    $livro_selecionado = isset($_GET['livro_id']) ? (int)$_GET['livro_id'] : 0;

?>

    <!-- Título da Página -->
    <h1>📝 Registrar Novo Empréstimo</h1>

    <?php if (count($livros) == 0): ?>
        <!-- Nenhum livro disponível -->
        <div class="alert alert-danger">
            <strong>⚠️ Nenhum livro disponível</strong><br>
            Não há livros disponíveis para empréstimo no momento.
            Todos os exemplares estão emprestados.
        </div>
        <a href="emprestimos.php" class="btn btn-secondary">Voltar</a>
        
    <?php elseif (count($clientes) == 0): ?>
        <!-- Nenhum cliente cadastrado -->
        <div class="alert alert-warning">
            <strong>⚠️ Nenhum cliente cadastrado</strong><br>
            Você precisa cadastrar clientes antes de registrar empréstimos.
        </div>
        <a href="cliente_novo.php" class="btn btn-success">Cadastrar Cliente</a>
        <a href="emprestimos.php" class="btn btn-secondary">Voltar</a>
        
    <?php else: ?>
        <!-- ========================================
             FORMULÁRIO DE EMPRÉSTIMO
             ======================================== -->
        <form method="POST" action="emprestimo_registrar.php" id="formEmprestimo">
            
            <!-- Informações do Prazo -->
            <div class="alert alert-info">
                <strong>ℹ️ Informações Importantes</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Prazo de devolução: <strong><?= PRAZO_EMPRESTIMO_DIAS ?> dias</strong></li>
                    <li>Multa por dia de atraso: <strong><?= formatarMoeda(VALOR_MULTA_DIA) ?></strong></li>
                    <li>Limite de empréstimos por cliente: <strong><?= LIMITE_EMPRESTIMOS_CLIENTE ?> livros</strong></li>
                </ul>
            </div>

            <!-- ========================================
                 SELEÇÃO DO CLIENTE
                 ======================================== -->
            <div class="card">
                <h3>👤 Selecione o Cliente</h3>
                
                <div class="form-group">
                    <label for="cliente_id">
                        Cliente <span style="color: red;">*</span>
                    </label>
                    <select id="cliente_id" name="cliente_id" required>
                        <option value="">-- Selecione um cliente --</option>
                        <?php foreach ($clientes as $cliente): 
                            $disabled = '';
                            $aviso = '';
                            $class_option = '';
                            
                            // Verificar se cliente está bloqueado para empréstimos
                            if ($cliente['emprestimos_atrasados'] > 0) {
                                $disabled = 'disabled';
                                $aviso = ' ❌ BLOQUEADO - Com atraso';
                                $class_option = 'style="color: #f44336; background: #ffebee;"';
                            } elseif ($cliente['emprestimos_ativos'] >= LIMITE_EMPRESTIMOS_CLIENTE) {
                                $disabled = 'disabled';
                                $aviso = ' ❌ Limite de empréstimos atingido';
                                $class_option = 'style="color: #ff9800; background: #fff3cd;"';
                            } elseif ($cliente['emprestimos_ativos'] > 0) {
                                $aviso = " (📚 {$cliente['emprestimos_ativos']} empréstimo(s) ativo(s))";
                            }
                        ?>
                            <option value="<?= $cliente['id'] ?>" <?= $disabled ?> <?= $class_option ?>>
                                <?= htmlspecialchars($cliente['nome']) ?><?= $aviso ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <small style="color: #999; display: block; margin-top: 8px;">
                        ⚠️ Clientes com empréstimos atrasados ou que atingiram o limite não podem realizar novos empréstimos.
                    </small>
                </div>

                <!-- Informações do cliente selecionado (preenchido via JavaScript) -->
                <div id="info-cliente" style="display: none; background: #e3f2fd; padding: 15px; border-radius: 4px; margin-top: 15px;">
                    <h4 style="margin: 0 0 10px 0;">📋 Informações do Cliente</h4>
                    <div id="dados-cliente"></div>
                </div>
            </div>

            <!-- ========================================
                 SELEÇÃO DO LIVRO
                 ======================================== -->
            <div class="card">
                <h3>📚 Selecione o Livro</h3>
                
                <div class="form-group">
                    <label for="livro_id">
                        Livro <span style="color: red;">*</span>
                    </label>
                    <select id="livro_id" name="livro_id" required>
                        <option value="">-- Selecione um livro --</option>
                        <?php foreach ($livros as $livro): ?>
                            <option 
                                value="<?= $livro['id'] ?>" 
                                <?= $livro['id'] == $livro_selecionado ? 'selected' : '' ?>
                                data-localizacao="<?= htmlspecialchars($livro['localizacao']) ?>"
                                data-disponivel="<?= $livro['quantidade_disponivel'] ?>">
                                <?= htmlspecialchars($livro['titulo']) ?> - <?= htmlspecialchars($livro['autor']) ?>
                                (<?= $livro['quantidade_disponivel'] ?> disponível)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Informações do livro selecionado -->
                <div id="info-livro" style="display: none; background: #f3e5f5; padding: 15px; border-radius: 4px; margin-top: 15px;">
                    <h4 style="margin: 0 0 10px 0;">📖 Informações do Livro</h4>
                    <div id="dados-livro"></div>
                </div>
            </div>

            <!-- ========================================
                 RESUMO DO EMPRÉSTIMO
                 ======================================== -->
            <div class="card" style="background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);">
                <h3 style="color: #333;">📅 Resumo do Empréstimo</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <strong>Data do Empréstimo:</strong><br>
                        <span style="font-size: 18px;"><?= date('d/m/Y') ?></span>
                    </div>
                    
                    <div>
                        <strong>Data de Devolução:</strong><br>
                        <span style="font-size: 18px;">
                            <?= date('d/m/Y', strtotime('+' . PRAZO_EMPRESTIMO_DIAS . ' days')) ?>
                        </span>
                    </div>
                    
                    <div>
                        <strong>Prazo:</strong><br>
                        <span style="font-size: 18px;"><?= PRAZO_EMPRESTIMO_DIAS ?> dias</span>
                    </div>
                </div>
            </div>

            <!-- ========================================
                 BOTÕES DE AÇÃO
                 ======================================== -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
                <button type="submit" class="btn btn-success">
                    ✅ Registrar Empréstimo
                </button>
                
                <a href="emprestimos.php" class="btn btn-warning">
                    ❌ Cancelar
                </a>
            </div>
        </form>

        <!-- ========================================
             JAVASCRIPT PARA INTERATIVIDADE
             ======================================== -->
        <script>
        // Dados dos clientes (para exibir informações)
        const clientesData = <?= json_encode($clientes) ?>;
        
        // Quando selecionar um cliente
        document.getElementById('cliente_id').addEventListener('change', function() {
            const clienteId = parseInt(this.value);
            const infoDiv = document.getElementById('info-cliente');
            const dadosDiv = document.getElementById('dados-cliente');
            
            if (clienteId) {
                const cliente = clientesData.find(c => c.id === clienteId);
                if (cliente) {
                    dadosDiv.innerHTML = `
                        <p><strong>Nome:</strong> ${cliente.nome}</p>
                        <p><strong>E-mail:</strong> ${cliente.email}</p>
                        <p><strong>Empréstimos ativos:</strong> ${cliente.emprestimos_ativos} de ${<?= LIMITE_EMPRESTIMOS_CLIENTE ?>}</p>
                    `;
                    infoDiv.style.display = 'block';
                }
            } else {
                infoDiv.style.display = 'none';
            }
        });

        // Quando selecionar um livro
        document.getElementById('livro_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const infoDiv = document.getElementById('info-livro');
            const dadosDiv = document.getElementById('dados-livro');
            
            if (this.value) {
                const localizacao = option.getAttribute('data-localizacao');
                const disponivel = option.getAttribute('data-disponivel');
                
                dadosDiv.innerHTML = `
                    <p><strong>Título:</strong> ${option.text.split(' (')[0]}</p>
                    <p><strong>Localização:</strong> ${localizacao || 'Não informada'}</p>
                    <p><strong>Exemplares disponíveis:</strong> ${disponivel}</p>
                `;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        });

        // Validação antes de enviar
        document.getElementById('formEmprestimo').addEventListener('submit', function(e) {
            const clienteId = document.getElementById('cliente_id').value;
            const livroId = document.getElementById('livro_id').value;
            
            if (!clienteId || !livroId) {
                e.preventDefault();
                alert('⚠️ Por favor, selecione o cliente e o livro.');
                return false;
            }
            
            // Confirmação final
            if (!confirm('Confirma o registro deste empréstimo?')) {
                e.preventDefault();
                return false;
            }
        });
        </script>

    <?php endif; ?>

<?php

} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>
