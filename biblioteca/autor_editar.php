<?php
/**
 * Formulário de Edição de Autor Existente
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

// -------------------------------------------------------------------------
// LÓGICA DE CARREGAMENTO DE DADOS
// -------------------------------------------------------------------------

// 1. Conexão com o banco de dados
try {
    $conn = Database::getInstance()->getConnection(); 
} catch (Exception $e) {
    die("Falha ao obter conexão com o banco de dados: " . $e->getMessage());
}

// 2. Obtém o ID do autor da URL
$autor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 3. Verifica se o ID é válido
if ($autor_id <= 0) {
    header("Location: /biblioteca/erro.php?msg=ID de autor inválido para edição.");
    exit();
}

// 4. Consulta os dados do autor pelo ID
$sql = "SELECT id, nome, nacionalidade, data_nascimento, biografia FROM autores WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $autor_id, PDO::PARAM_INT);
$stmt->execute();
$autor = $stmt->fetch(PDO::FETCH_ASSOC);

// 5. Verifica se o autor foi encontrado
if (!$autor) {
    header("Location: /biblioteca/erro.php?msg=Autor não encontrado no banco de dados.");
    exit();
}

// Extrai os dados para variáveis mais fáceis de usar no formulário
$nome = htmlspecialchars($autor['nome']);
$nacionalidade = htmlspecialchars($autor['nacionalidade']);
$data_nascimento = $autor['data_nascimento']; // Já deve estar no formato YYYY-MM-DD
$biografia = htmlspecialchars($autor['biografia']);

?>

<h1>✏️ Editar Autor: <?php echo $nome; ?></h1>

<p style="color: #666; margin-bottom: 25px;">
    Altere os dados abaixo para atualizar o registro do autor. 
    Campos marcados com <span style="color: red;">*</span> são obrigatórios.
</p>

<!-- O formulário continuará enviando para 'autor_salvar.php', mas agora enviará o ID para indicar uma edição (UPDATE) -->
<form method="POST" action="autor_salvar.php" id="formAutor">
    
    <!-- CAMPO OCULTO (HIDDEN) ESSENCIAL PARA SABER QUAL AUTOR ESTÁ SENDO EDITADO -->
    <input type="hidden" name="id" value="<?php echo $autor_id; ?>">
    
    <div class="card">
        <h3>📝 Dados do Autor</h3>
        
        <div class="form-group">
            <label for="nome">
                Nome Completo do Autor <span style="color: red;">*</span>
            </label>
            <input 
                type="text" 
                id="nome" 
                name="nome" 
                required 
                maxlength="150"
                placeholder="Digite o nome completo do autor"
                autofocus
                value="<?php echo $nome; ?>"
            >
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input 
                        type="text" 
                        id="nacionalidade" 
                        name="nacionalidade" 
                        maxlength="50"
                        placeholder="Ex: Brasileira, Portuguesa, etc."
                        value="<?php echo $nacionalidade; ?>"
                    >
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input 
                        type="date" 
                        id="data_nascimento" 
                        name="data_nascimento"
                        max="<?= date('Y-m-d') ?>"
                        value="<?php echo $data_nascimento; ?>"
                    >
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="biografia">Biografia (opcional)</label>
            <textarea 
                id="biografia" 
                name="biografia" 
                rows="6"
                placeholder="Breve biografia do autor, suas obras principais, prêmios recebidos, etc."
            ><?php echo $biografia; ?></textarea>
            <small style="color: #999;">
                Informações adicionais sobre o autor que podem ser úteis
            </small>
        </div>
    </div>
    
    <!-- Exemplos de autores famosos -->
    <div class="alert alert-info">
        <strong>💡 Dica:</strong> Certifique-se de que os dados estejam corretos antes de salvar.
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
        <button type="submit" class="btn btn-primary">
            💾 Salvar Alterações
        </button>
        
        <!-- O botão de reset foi removido em edição, pois limpar pode ser frustrante -->
        
        <a href="autores.php" class="btn btn-warning">
            ❌ Cancelar e Voltar
        </a>
    </div>
</form>

<script>
// Validação simples
document.getElementById('formAutor').addEventListener('submit', function(e) {
    const nome = document.getElementById('nome').value.trim();
    
    if (nome.length < 3) {
        // Usando um modal/div customizado seria melhor, mas mantemos o alert simples
        // para compatibilidade com o código original.
        e.preventDefault();
        alert('❌ O nome do autor deve ter pelo menos 3 caracteres.');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>