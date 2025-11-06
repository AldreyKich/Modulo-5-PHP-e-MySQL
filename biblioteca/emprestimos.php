<?php
// emprestimo_novo.php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/funcoes.php';
require_once 'config/config.php';


$db = Database::getInstance();
$pdo = $db->getConnection();

// Buscar clientes
$sql = "SELECT c.id, c.nome, c.email,
        (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id AND status = 'Ativo') AS emprestimos_ativos,
        (SELECT COUNT(*) FROM emprestimos WHERE cliente_id = c.id AND status = 'Ativo' AND data_devolucao_prevista < CURDATE()) AS emprestimos_atrasados
        FROM clientes c
        ORDER BY c.nome";
$stmt = $pdo->query($sql);
$clientes = $stmt->fetchAll();

// Buscar livros disponíveis
$sql = "SELECT l.id, l.titulo, a.nome AS autor, l.quantidade_disponivel
        FROM livros l
        INNER JOIN autores a ON l.autor_id = a.id
        WHERE l.quantidade_disponivel > 0
        ORDER BY l.titulo";
$stmt = $pdo->query($sql);
$livros = $stmt->fetchAll();

// Pre-selecionar livro se vier da URL
$livro_selecionado = isset($_GET['livro_id']) ? (int)$_GET['livro_id'] : 0;

echo "<h1>Registrar Novo Empréstimo</h1>";

// Mensagem de erro
if (isset($_GET['erro'])) {
    echo "<div style='background:#ffebee;color:#c62828;padding:15px;margin:10px 0;border-radius:4px;'>";
    echo htmlspecialchars($_GET['erro']);
    echo "</div>";
}

// Nenhum livro disponível
if (count($livros) == 0) {
    echo "<div style='background:#ffebee;color:#c62828;padding:15px;margin:10px 0;border-radius:4px;'>";
    echo "Nenhum livro disponível para empréstimo no momento.";
    echo "</div>";
    echo "<a href='emprestimos.php' class='btn'>Voltar</a>";
    require_once 'includes/footer.php';
    exit;
}
?>

<form method="POST" action="emprestimo_registrar.php" id="formEmprestimo">
    <div style="margin-bottom:15px;">
        <label for="cliente_id">Selecione o Cliente:</label><br>
        <select id="cliente_id" name="cliente_id" required style="width:100%;padding:8px;">
            <option value="">-- Selecione um cliente --</option>
            <?php foreach ($clientes as $cliente): 
                $disabled = '';
                $aviso = '';
                if ($cliente['emprestimos_atrasados'] > 0) {
                    $disabled = 'disabled';
                    $aviso = ' (EM ATRASO - Bloqueado)';
                } elseif ($cliente['emprestimos_ativos'] >= 3) {
                    $disabled = 'disabled';
                    $aviso = ' (Limite de 3 empréstimos atingido)';
                }
            ?>
            <option value="<?= $cliente['id'] ?>" <?= $disabled ?>>
                <?= htmlspecialchars($cliente['nome']) ?> - <?= htmlspecialchars($cliente['email']) ?><?= $aviso ?>
            </option>
            <?php endforeach; ?>
        </select>
        <small>Clientes com empréstimos atrasados ou limite atingido não podem realizar novos empréstimos.</small>
    </div>

    <div style="margin-bottom:15px;">
        <label for="livro_id">Selecione o Livro:</label><br>
        <select id="livro_id" name="livro_id" required style="width:100%;padding:8px;">
            <option value="">-- Selecione um livro --</option>
            <?php foreach ($livros as $livro): ?>
            <option value="<?= $livro['id'] ?>" <?= $livro['id'] == $livro_selecionado ? 'selected' : '' ?>>
                <?= htmlspecialchars($livro['titulo']) ?> - <?= htmlspecialchars($livro['autor']) ?>
                (<?= $livro['quantidade_disponivel'] ?> disponível)
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom:15px;background:#e3f2fd;padding:15px;border-radius:4px;">
        <h3 style="margin-top:0;">Informações do Empréstimo</h3>
        <p><strong>Data do Empréstimo:</strong> <?= date('d/m/Y') ?></p>
        <p><strong>Data de Devolução Prevista:</strong> <?= date('d/m/Y', strtotime('+' . PRAZO_EMPRESTIMO_DIAS . ' days')) ?></p>
        <p><strong>Prazo:</strong> <?= PRAZO_EMPRESTIMO_DIAS ?> dias</p>
        <p><strong>Multa por dia de atraso:</strong> <?= formatarMoeda(VALOR_MULTA_DIA) ?></p>
    </div>

    <button type="submit" class="btn">Registrar Empréstimo</button>
    <a href="emprestimos.php" class="btn btn-warning">Cancelar</a>
</form>

<script>
// Validação adicional no cliente
document.getElementById('formEmprestimo').addEventListener('submit', function(e) {
    var clienteId = document.getElementById('cliente_id').value;
    var livroId = document.getElementById('livro_id').value;

    if (!clienteId || !livroId) {
        e.preventDefault();
        alert('Por favor, selecione cliente e livro.');
        return false;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
