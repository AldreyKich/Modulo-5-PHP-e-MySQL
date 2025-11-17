<?php
/**
 * Formulário de Cadastro de Novo Livro
 * * Permite cadastrar um novo livro no acervo da biblioteca
 * com todas as informações necessárias.
 * * @author Módulo 5 - Banco de Dados II
 * @version 1.1 (Com Upload de Imagem)
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    // Buscar autores para o select
    $sql = "SELECT id, nome, nacionalidade FROM autores ORDER BY nome";
    $stmt = $pdo->query($sql);
    $autores = $stmt->fetchAll();
?>

<h1>➕ Cadastrar Novo Livro</h1>

<p style="color: #666; margin-bottom: 25px;">
    Preencha os dados abaixo para cadastrar um novo livro no acervo da biblioteca.
    Campos marcados com <span style="color: red;">*</span> são obrigatórios.
</p>

<?php if (count($autores) == 0): ?>
    <div class="alert alert-warning">
        <strong>⚠️ Nenhum autor cadastrado!</strong><br>
        Você precisa cadastrar pelo menos um autor antes de cadastrar livros.
    </div>
    <a href="autor_novo.php" class="btn btn-success">✍️ Cadastrar Autor</a>
    <a href="livros.php" class="btn btn-secondary">❌ Voltar para Livros</a>

<?php else: ?>

<form method="POST" action="livro_salvar.php" id="formLivro" enctype="multipart/form-data">
    
    <div class="card">
        <h3>📖 Informações Básicas</h3>
        
        <div class="form-group">
            <label for="capa_imagem">
                Capa do Livro (Imagem)
            </label>
            <input 
                type="file" 
                id="capa_imagem" 
                name="capa_imagem" 
                accept="image/jpeg, image/png, image/webp"
            >
            <small style="color: #999;">
                Envie uma imagem JPG, PNG ou WebP. Tamanho máximo recomendado: 2MB.
            </small>
        </div>
        
        <div class="form-group">
            <label for="titulo">
                Título do Livro <span style="color: red;">*</span>
            </label>
            <input 
                type="text" 
                id="titulo" 
                name="titulo" 
                required 
                maxlength="200"
                placeholder="Digite o título completo do livro"
                autofocus
            >
            <small style="color: #999;">
                Exemplo: Dom Casmurro, 1984, Harry Potter e a Pedra Filosofal
            </small>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="autor_id">
                        Autor <span style="color: red;">*</span>
                    </label>
                    <select id="autor_id" name="autor_id" required>
                        <option value="">-- Selecione um autor --</option>
                        <?php foreach ($autores as $autor): ?>
                            <option value="<?= $autor['id'] ?>">
                                <?= htmlspecialchars($autor['nome']) ?>
                                <?php if ($autor['nacionalidade']): ?>
                                    (<?= htmlspecialchars($autor['nacionalidade']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #999;">
                        Não encontrou o autor? 
                        <a href="autor_novo.php" target="_blank" style="color: #667eea;">
                            Cadastre aqui em nova aba
                        </a>
                    </small>
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input 
                        type="text" 
                        id="isbn" 
                        name="isbn" 
                        maxlength="20"
                        placeholder="978-8535911664"
                    >
                    <small style="color: #999;">
                        Código único internacional do livro (opcional)
                    </small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="ano_publicacao">Ano de Publicação</label>
                    <input 
                        type="number" 
                        id="ano_publicacao" 
                        name="ano_publicacao" 
                        min="1000" 
                        max="<?= date('Y') ?>"
                        placeholder="<?= date('Y') ?>"
                    >
                    <small style="color: #999;">
                        Entre 1000 e <?= date('Y') ?>
                    </small>
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label for="editora">Editora</label>
                    <input 
                        type="text" 
                        id="editora" 
                        name="editora" 
                        maxlength="100"
                        placeholder="Nome da editora"
                    >
                    <small style="color: #999;">
                        Exemplo: Companhia das Letras, Rocco
                    </small>
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label for="numero_paginas">Número de Páginas</label>
                    <input 
                        type="number" 
                        id="numero_paginas" 
                        name="numero_paginas" 
                        min="1"
                        placeholder="Ex: 350"
                    >
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h3>🏷️ Classificação</h3>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="categoria">Categoria/Gênero</label>
                    <select id="categoria" name="categoria">
                        <option value="">-- Selecione uma categoria --</option>
                        <option value="Romance">Romance</option>
                        <option value="Ficção">Ficção</option>
                        <option value="Fantasia">Fantasia</option>
                        <option value="Terror">Terror</option>
                        <option value="Mistério">Mistério</option>
                        <option value="Suspense">Suspense</option>
                        <option value="Biografia">Biografia</option>
                        <option value="História">História</option>
                        <option value="Ciência">Ciência</option>
                        <option value="Autoajuda">Autoajuda</option>
                        <option value="Infantil">Infantil</option>
                        <option value="Técnico">Técnico</option>
                        <option value="Poesia">Poesia</option>
                        <option value="Drama">Drama</option>
                        <option value="Aventura">Aventura</option>
                        <option value="Outros">Outros</option>
                    </select>
                    <small style="color: #999;">
                        Ajuda na organização e busca de livros
                    </small>
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label for="localizacao">Localização na Biblioteca</label>
                    <input 
                        type="text" 
                        id="localizacao" 
                        name="localizacao" 
                        maxlength="50"
                        placeholder="Ex: Estante A1, Prateleira 3"
                    >
                    <small style="color: #999;">
                        Onde o livro está fisicamente localizado
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h3>📊 Quantidade de Exemplares</h3>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="quantidade_total">
                        Quantidade Total de Exemplares <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="quantidade_total" 
                        name="quantidade_total" 
                        required
                        min="1"
                        value="1"
                        placeholder="Quantos exemplares a biblioteca possui?"
                    >
                    <small style="color: #999;">
                        Número total de exemplares deste livro no acervo
                    </small>
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label for="quantidade_disponivel">
                        Quantidade Disponível <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="quantidade_disponivel" 
                        name="quantidade_disponivel" 
                        required
                        min="0"
                        value="1"
                        placeholder="Quantos estão disponíveis agora?"
                    >
                    <small style="color: #999;">
                        Quantidade que está disponível para empréstimo
                    </small>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info" style="margin-top: 15px;">
            <strong>ℹ️ Atenção:</strong> 
            <ul style="margin: 10px 0 0 20px;">
                <li>A quantidade disponível não pode ser maior que a quantidade total</li>
                <li>Ao alterar a quantidade total, a disponível será ajustada automaticamente</li>
                <li>Se todos os exemplares estiverem disponíveis, use o mesmo valor em ambos os campos</li>
            </ul>
        </div>
    </div>
    
    <div class="card" style="background: #f9f9f9; border: 2px dashed #ccc;">
        <h3 style="color: #667eea;">💡 Exemplos de Livros Famosos</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <div>
                <strong>📚 Brasileiros:</strong>
                <ul style="margin: 10px 0 0 20px; color: #666;">
                    <li>Dom Casmurro - Machado de Assis (1899)</li>
                    <li>Grande Sertão: Veredas - Guimarães Rosa</li>
                    <li>Capitães da Areia - Jorge Amado</li>
                    <li>A Hora da Estrela - Clarice Lispector</li>
                </ul>
            </div>
            <div>
                <strong>🌍 Internacionais:</strong>
                <ul style="margin: 10px 0 0 20px; color: #666;">
                    <li>1984 - George Orwell</li>
                    <li>Cem Anos de Solidão - Gabriel García Márquez</li>
                    <li>O Pequeno Príncipe - Antoine de Saint-Exupéry</li>
                    <li>Harry Potter - J.K. Rowling</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
        <button type="submit" class="btn btn-success">
            ✅ Cadastrar Livro
        </button>
        
        <button type="reset" class="btn btn-secondary">
            🔄 Limpar Formulário
        </button>
        
        <a href="livros.php" class="btn btn-warning">
            ❌ Cancelar
        </a>
    </div>
</form>

<script>
/**
 * Validação do formulário antes do envio
 */
document.getElementById('formLivro').addEventListener('submit', function(e) {
    let erros = [];
    
    // Validar título
    const titulo = document.getElementById('titulo').value.trim();
    if (titulo.length < 2) {
        erros.push('O título deve ter pelo menos 2 caracteres.');
    }
    
    // Validar autor
    const autorId = document.getElementById('autor_id').value;
    if (!autorId || autorId === '') {
        erros.push('Selecione um autor.');
    }
    
    // Validar quantidades
    const qtdTotal = parseInt(document.getElementById('quantidade_total').value) || 0;
    const qtdDisponivel = parseInt(document.getElementById('quantidade_disponivel').value) || 0;
    
    if (qtdTotal < 1) {
        erros.push('A quantidade total deve ser pelo menos 1.');
    }
    
    if (qtdDisponivel < 0) {
        erros.push('A quantidade disponível não pode ser negativa.');
    }
    
    if (qtdDisponivel > qtdTotal) {
        erros.push('A quantidade disponível não pode ser maior que a quantidade total!');
    }
    
    // Validar ano de publicação se preenchido
    const ano = parseInt(document.getElementById('ano_publicacao').value);
    if (ano) {
        const anoAtual = new Date().getFullYear();
        if (ano < 1000 || ano > anoAtual) {
            erros.push('Ano de publicação inválido. Deve estar entre 1000 e ' + anoAtual);
        }
    }
    
    // NOVO: Validação da Capa de Imagem (Tamanho e Tipo)
    const capaInput = document.getElementById('capa_imagem');
    if (capaInput && capaInput.files.length > 0) {
        const file = capaInput.files[0];
        const maxSizeBytes = 2 * 1024 * 1024; // 2 MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (file.size > maxSizeBytes) {
            erros.push('A imagem da capa é muito grande. O tamanho máximo permitido é 2MB.');
        }

        if (!allowedTypes.includes(file.type)) {
            erros.push('Tipo de arquivo inválido para a capa. Use apenas JPG, PNG ou WebP.');
        }
    }
    
    // Se houver erros, previne o envio e exibe
    if (erros.length > 0) {
        e.preventDefault();
        alert('❌ Por favor, corrija os seguintes erros:\n\n' + erros.join('\n'));
        return false;
    }
    
    // Confirmação final
    if (!confirm('✅ Confirma o cadastro deste livro?')) {
        e.preventDefault();
        return false;
    }
});

/**
 * Atualizar quantidade disponível automaticamente
 * quando alterar a quantidade total
 */
document.getElementById('quantidade_total').addEventListener('change', function() {
    const disponivelInput = document.getElementById('quantidade_disponivel');
    const totalValue = parseInt(this.value) || 0;
    const disponivelValue = parseInt(disponivelInput.value) || 0;
    
    // Se disponível estiver vazio ou for maior que total, iguala ao total
    if (!disponivelInput.value || disponivelValue > totalValue) {
        disponivelInput.value = totalValue;
    }
});

/**
 * Validação em tempo real da quantidade disponível
 */
document.getElementById('quantidade_disponivel').addEventListener('input', function() {
    const totalValue = parseInt(document.getElementById('quantidade_total').value) || 0;
    const disponivelValue = parseInt(this.value) || 0;
    
    if (disponivelValue > totalValue) {
        this.style.borderColor = '#f44336';
        this.style.backgroundColor = '#ffebee';
    } else {
        this.style.borderColor = '#ddd';
        this.style.backgroundColor = 'white';
    }
});

/**
 * Confirmação ao limpar o formulário
 */
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    if (!confirm('🔄 Tem certeza que deseja limpar todos os campos preenchidos?')) {
        e.preventDefault();
        return false;
    }
});

/**
 * Formatar ISBN enquanto digita (apenas números e hífens)
 */
document.getElementById('isbn').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^\d\-]/g, '');
    e.target.value = value;
});

/**
 * Destaque visual nos campos obrigatórios vazios
 */
document.querySelectorAll('input[required], select[required]').forEach(field => {
    field.addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.style.borderColor = '#ff9800';
        } else {
            this.style.borderColor = '#ddd';
        }
    });
});
</script>

<?php endif; ?>

<?php
} catch (PDOException $e) {
    exibirMensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
}

require_once 'includes/footer.php';
?>