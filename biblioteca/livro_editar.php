<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// Obter o ID do livro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    exibirMensagem('erro', 'ID inválido.');
    require_once 'includes/footer.php';
    exit;
}

try {
    // Buscar dados do livro
    $sql = "SELECT * FROM livros WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $livro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livro) {
        exibirMensagem('erro', 'Livro não encontrado.');
        require_once 'includes/footer.php';
        exit;
    }

    // Buscar autores
    $sqlAutores = "SELECT id, nome, nacionalidade FROM autores ORDER BY nome";
    $autores = $pdo->query($sqlAutores)->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>✏️ Editar Livro</h1>

<form method="POST" action="livro_atualizar.php" id="formLivro">
    <input type="hidden" name="id" value="<?= $livro['id'] ?>">

    <div class="card">
        <h3>📖 Informações Básicas</h3>

        <div class="form-group">
            <label for="titulo">Título do Livro <span style="color:red">*</span></label>
            <input type="text" id="titulo" name="titulo" required maxlength="200"
                   value="<?= htmlspecialchars($livro['titulo']) ?>">
        </div>

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="autor_id">Autor <span style="color:red">*</span></label>
                    <select id="autor_id" name="autor_id" required>
                        <option value="">-- Selecione um autor --</option>
                        <?php foreach ($autores as $autor): ?>
                            <option value="<?= $autor['id'] ?>" 
                                <?= $autor['id'] == $livro['autor_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($autor['nome']) ?>
                                <?php if ($autor['nacionalidade']): ?>
                                    (<?= htmlspecialchars($autor['nacionalidade']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col">
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" maxlength="20"
                           value="<?= htmlspecialchars($livro['isbn']) ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="ano_publicacao">Ano de Publicação</label>
                <input type="number" id="ano_publicacao" name="ano_publicacao"
                       value="<?= htmlspecialchars($livro['ano_publicacao']) ?>">
            </div>
            <div class="col">
                <label for="editora">Editora</label>
                <input type="text" id="editora" name="editora"
                       value="<?= htmlspecialchars($livro['editora']) ?>">
            </div>
            <div class="col">
                <label for="numero_paginas">Número de Páginas</label>
                <input type="number" id="numero_paginas" name="numero_paginas"
                       value="<?= htmlspecialchars($livro['numero_paginas']) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3>🏷️ Classificação</h3>
        <div class="row">
            <div class="col">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria">
                    <?php
                    $categorias = ['Romance','Ficção','Fantasia','Terror','Mistério','Suspense','Biografia','História','Ciência','Autoajuda','Infantil','Técnico','Poesia','Drama','Aventura','Outros'];
                    echo "<option value=''>-- Selecione --</option>";
                    foreach ($categorias as $cat) {
                        $selected = ($livro['categoria'] == $cat) ? 'selected' : '';
                        echo "<option value='$cat' $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col">
                <label for="localizacao">Localização</label>
                <input type="text" id="localizacao" name="localizacao"
                       value="<?= htmlspecialchars($livro['localizacao']) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3>📊 Quantidades</h3>
        <div class="row">
            <div class="col">
                <label for="quantidade_total">Total</label>
                <input type="number" id="quantidade_total" name="quantidade_total" required
                       value="<?= htmlspecialchars($livro['quantidade_total']) ?>">
            </div>
            <div class="col">
                <label for="quantidade_disponivel">Disponível</label>
                <input type="number" id="quantidade_disponivel" name="quantidade_disponivel" required
                       value="<?= htmlspecialchars($livro['quantidade_disponivel']) ?>">
            </div>
        </div>
    </div>

    <div style="margin-top:30px;">
        <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
        <a href="livros.php" class="btn btn-warning">❌ Cancelar</a>
    </div>
</form>

<script>
document.getElementById('formLivro').addEventListener('submit', e => {
    if (!confirm('💾 Deseja realmente salvar as alterações deste livro?')) {
        e.preventDefault();
    }
});
</script>

<?php
} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>
