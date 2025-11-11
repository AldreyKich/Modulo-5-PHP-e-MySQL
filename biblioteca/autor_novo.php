<?php
/**
 * Formulário de Cadastro de Novo Autor
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';
?>

<h1>✍️ Cadastrar Novo Autor</h1>

<p style="color: #666; margin-bottom: 25px;">
    Preencha os dados abaixo para cadastrar um novo autor no sistema.
    Campos marcados com <span style="color: red;">*</span> são obrigatórios.
</p>

<form method="POST" action="autor_salvar.php" id="formAutor">
    
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
            ></textarea>
            <small style="color: #999;">
                Informações adicionais sobre o autor que podem ser úteis
            </small>
        </div>
    </div>
    
    <!-- Exemplos de autores famosos -->
    <div class="alert alert-info">
        <strong>💡 Exemplos de Autores Brasileiros:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li>Machado de Assis (Brasileira, 1839-1908)</li>
            <li>Clarice Lispector (Brasileira, 1920-1977)</li>
            <li>Jorge Amado (Brasileira, 1912-2001)</li>
            <li>Paulo Coelho (Brasileira, 1947-)</li>
            <li>Monteiro Lobato (Brasileira, 1882-1948)</li>
        </ul>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
        <button type="submit" class="btn btn-success">
            ✅ Cadastrar Autor
        </button>
        
        <button type="reset" class="btn btn-secondary">
            🔄 Limpar Formulário
        </button>
        
        <a href="autores.php" class="btn btn-warning">
            ❌ Cancelar
        </a>
    </div>
</form>

<script>
// Validação simples
document.getElementById('formAutor').addEventListener('submit', function(e) {
    const nome = document.getElementById('nome').value.trim();
    
    if (nome.length < 3) {
        e.preventDefault();
        alert('❌ O nome do autor deve ter pelo menos 3 caracteres.');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
