<?php
/**
 * Formulário de Cadastro de Novo Cliente
 * 
 * Permite cadastrar um novo cliente no sistema com validação
 * de campos e mensagens de erro/sucesso.
 * 
 * @author Módulo 5 - Banco de Dados II
 * @version 1.0
 */

// Inclui os arquivos necessários
require_once 'config/config.php';
require_once 'includes/funcoes.php';
require_once 'includes/header.php';
?>

<!-- Título da Página -->
<h1>➕ Cadastrar Novo Cliente</h1>

<p style="color: #666; margin-bottom: 25px;">
    Preencha os dados abaixo para cadastrar um novo cliente no sistema.
    Campos marcados com <span style="color: red;">*</span> são obrigatórios.
</p>

<!-- ========================================
     FORMULÁRIO DE CADASTRO
     ======================================== -->
<form method="POST" action="cliente_salvar.php" id="formCliente">
    
    <div class="card">
        <h3>📋 Dados Pessoais</h3>
        
        <!-- Nome Completo -->
        <div class="form-group">
            <label for="nome">
                Nome Completo <span style="color: red;">*</span>
            </label>
            <input 
                type="text" 
                id="nome" 
                name="nome" 
                required 
                maxlength="150"
                placeholder="Digite o nome completo do cliente"
                autofocus
            >
            <small style="color: #999;">Exemplo: João Silva Santos</small>
        </div>

        <!-- E-mail -->
        <div class="form-group">
            <label for="email">
                E-mail <span style="color: red;">*</span>
            </label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                maxlength="150"
                placeholder="exemplo@email.com"
            >
            <small style="color: #999;">
                O e-mail deve ser único no sistema. Será usado para identificação.
            </small>
        </div>

        <!-- Telefone -->
        <div class="form-group">
            <label for="telefone">
                Telefone <span style="color: red;">*</span>
            </label>
            <input 
                type="tel" 
                id="telefone" 
                name="telefone" 
                required 
                maxlength="20"
                placeholder="(00) 00000-0000"
            >
            <small style="color: #999;">
                Formato: (11) 98765-4321 ou (11) 3333-4444
            </small>
        </div>

        <!-- CPF (Opcional) -->
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input 
                type="text" 
                id="cpf" 
                name="cpf" 
                maxlength="14"
                placeholder="000.000.000-00"
            >
            <small style="color: #999;">
                Opcional. Formato: 123.456.789-00
            </small>
        </div>

        <!-- Data de Nascimento (Opcional) -->
        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento</label>
            <input 
                type="date" 
                id="data_nascimento" 
                name="data_nascimento"
            >
            <small style="color: #999;">Opcional</small>
        </div>
    </div>

    <!-- ========================================
         ENDEREÇO (Opcional)
         ======================================== -->
    <div class="card">
        <h3>📍 Endereço (Opcional)</h3>
        
        <div class="row">
            <!-- Endereço -->
            <div class="col" style="flex: 2;">
                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input 
                        type="text" 
                        id="endereco" 
                        name="endereco" 
                        maxlength="255"
                        placeholder="Rua, número, complemento"
                    >
                </div>
            </div>

            <!-- CEP -->
            <div class="col">
                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input 
                        type="text" 
                        id="cep" 
                        name="cep" 
                        maxlength="10"
                        placeholder="00000-000"
                    >
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Cidade -->
            <div class="col">
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input 
                        type="text" 
                        id="cidade" 
                        name="cidade" 
                        maxlength="100"
                        placeholder="Nome da cidade"
                    >
                </div>
            </div>

            <!-- Estado -->
            <div class="col">
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Selecione...</option>
                        <option value="AC">Acre</option>
                        <option value="AL">Alagoas</option>
                        <option value="AP">Amapá</option>
                        <option value="AM">Amazonas</option>
                        <option value="BA">Bahia</option>
                        <option value="CE">Ceará</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                        <option value="MA">Maranhão</option>
                        <option value="MT">Mato Grosso</option>
                        <option value="MS">Mato Grosso do Sul</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="PA">Pará</option>
                        <option value="PB">Paraíba</option>
                        <option value="PR">Paraná</option>
                        <option value="PE">Pernambuco</option>
                        <option value="PI">Piauí</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="RN">Rio Grande do Norte</option>
                        <option value="RS">Rio Grande do Sul</option>
                        <option value="RO">Rondônia</option>
                        <option value="RR">Roraima</option>
                        <option value="SC">Santa Catarina</option>
                        <option value="SP">São Paulo</option>
                        <option value="SE">Sergipe</option>
                        <option value="TO">Tocantins</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================
         OBSERVAÇÕES (Opcional)
         ======================================== -->
    <div class="card">
        <h3>📝 Observações</h3>
        
        <div class="form-group">
            <label for="observacoes">Observações sobre o cliente</label>
            <textarea 
                id="observacoes" 
                name="observacoes" 
                rows="4"
                placeholder="Informações adicionais sobre o cliente (opcional)"
            ></textarea>
            <small style="color: #999;">
                Máximo de 1000 caracteres
            </small>
        </div>
    </div>

    <!-- ========================================
         BOTÕES DE AÇÃO
         ======================================== -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
        <button type="submit" class="btn btn-success">
            ✅ Cadastrar Cliente
        </button>
        
        <button type="reset" class="btn btn-secondary">
            🔄 Limpar Formulário
        </button>
        
        <a href="clientes.php" class="btn btn-warning">
            ❌ Cancelar
        </a>
    </div>
</form>

<!-- ========================================
     JAVASCRIPT PARA VALIDAÇÕES
     ======================================== -->
<script>
/**
 * Validação do formulário antes do envio
 */
document.getElementById('formCliente').addEventListener('submit', function(e) {
    let erros = [];
    
    // Validar nome
    const nome = document.getElementById('nome').value.trim();
    if (nome.length < 3) {
        erros.push('O nome deve ter pelo menos 3 caracteres.');
    }
    
    // Validar e-mail
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        erros.push('Digite um e-mail válido.');
    }
    
    // Validar telefone
    const telefone = document.getElementById('telefone').value.trim();
    if (telefone.length < 10) {
        erros.push('Digite um telefone válido.');
    }
    
    // Validar CPF se preenchido
    const cpf = document.getElementById('cpf').value.trim();
    if (cpf && cpf.replace(/\D/g, '').length !== 11) {
        erros.push('CPF deve ter 11 dígitos.');
    }
    
    // Se houver erros, previne o envio e exibe
    if (erros.length > 0) {
        e.preventDefault();
        alert('Por favor, corrija os seguintes erros:\n\n' + erros.join('\n'));
        return false;
    }
});

/**
 * Máscara para CPF
 */
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    
    e.target.value = value;
});

/**
 * Máscara para CEP
 */
document.getElementById('cep').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length <= 8) {
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    
    e.target.value = value;
});

/**
 * Confirmação ao limpar o formulário
 */
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    if (!confirm('Tem certeza que deseja limpar todos os campos preenchidos?')) {
        e.preventDefault();
        return false;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
